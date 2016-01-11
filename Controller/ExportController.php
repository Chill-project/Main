<?php

/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2014-2015, Champs Libres Cooperative SCRLFS, 
 * <http://www.champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chill\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Chill\MainBundle\Form\Type\Export\ExportType;
use Chill\MainBundle\Form\Type\Export\PickFormatterType;
use Chill\MainBundle\Form\Type\Export\FormatterType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


/**
 * ExportController is the controller use for exporting data.
 * 
 */
class ExportController extends Controller
{
    public function indexAction(Request $request)
    {
        $exportManager = $this->get('chill.main.export_manager');
        
        $exports = $exportManager->getExports();
        
        return $this->render('ChillMainBundle:Export:layout.html.twig', array(
            'exports' => $exports
        ));
    }
    
    /**
     * Render the form required to generate data for the export.
     * 
     * This action has two steps :
     * 
     * - 'export', for the export form
     * - 'formatter', for the formatter form
     * 
     * @param string $alias
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request, $alias)
    {
        $step = $request->query->getAlpha('step', 'export');
        
        switch ($step) {
            case 'export':
                return $this->renderExportForm($alias);
                break;
            case 'formatter':
                return $this->renderFormatterStep($request, $alias);
                break;
            default:
                throw $this->createNotFoundException("The given step '$step' is invalid");
        }
    }
    
    /**
     * Render the export form
     * 
     * @param string $alias
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderExportForm($alias)
    {
        $exportManager = $this->get('chill.main.export_manager');
        
        $export = $exportManager->getExport($alias);
                
        $form = $this->createCreateFormExport($alias);
        
        return $this->render('ChillMainBundle:Export:new.html.twig', array(
            'form' => $form->createView(),
            'export_alias' => $alias,
            'export' => $export
        ));
    }
    
    /**
     * 
     * @param string $alias
     * @return \Symfony\Component\Form\Form
     */
    protected function createCreateFormExport($alias)
    {
        $builder = $this->get('form.factory')
              ->createNamedBuilder(null, FormType::class, array(), array(
                    'method' => 'GET',
                    'csrf_protection' => false,
                    'action' => $this->generateUrl('chill_main_export_new', array(
                        'alias' => $alias
                    ))               
              ));
        
        $builder->add('export', ExportType::class, array(
            'export_alias' => $alias,

        ));
        
        $builder->add('submit', 'submit', array(
            'label' => 'Generate'
        ));
        $builder->add('step', 'hidden', array(
            'data' => 'formatter'
        ));
        
        return $builder->getForm();
    }
    
    protected function renderFormatterStep(Request $request, $alias)
    {
        $export = $this->get('chill.main.export_manager')->getExport($alias);
        
        $exportForm = $this->createCreateFormExport($alias);
        $exportForm->handleRequest($request);
        $data = $exportForm->getData();
        
        $form = $this->createCreateFormFormatter($request, 
                 $alias, array(), $data['export']['formatter']['alias']);
        
        return $this->render('ChillMainBundle:Export:new_formatter_step.html.twig',
                array(
                    'form' => $form->createView(),
                    'export' => $export
                ));
    }
    
    /**
     * 
     * @param Request $request
     * @param type $formatterAlias
     * @return \Symfony\Component\Form\Form
     */
    protected function createCreateFormFormatter(Request $request, 
            $exportAlias, $aggregatorAliases, $formatterAlias = null)
    {
        var_dump($request->query->all());
        $builder = $this->get('form.factory')
                ->createNamedBuilder(null, FormType::class, array(), array(
                    'method' => 'GET',
                    'csrf_protection' => false,
                    'action' => $this->generateUrl('chill_main_export_generate', array(
                        'alias' => $exportAlias
                    ))
                ));
        
        $builder->add('formatter', FormatterType::class, array(
            'formatter_alias' => $formatterAlias,
            'export_alias' => $exportAlias,
            'aggregator_aliases' => $aggregatorAliases
        ));
        
        // re-add the export type under hidden fields
        $builderExport = $builder->create('export', FormType::class, array());
        $data = $request->query->all();
        foreach($data['export'] as $key => $value) {
            $this->recursiveAddHiddenFieldsToForm($builderExport, $key, $value);
        }
        $builder->add($builderExport);
        
        //add the formatter alias
        $builder->add('formatter', HiddenType::class, array(
            'data' => $formatterAlias
        ));
        
        $builder->add('submit', 'submit', array(
            'label' => 'Generate'
        ));
        
        return $builder->getForm();
        
    }
    
    public function generateAction(Request $request, $alias)
    {
        $exportManager = $this->get('chill.main.export_manager');
        
        $form = $this->createCreateFormGenerate($request, $alias);
        $form->handleRequest($request);
        $data = $form->getData();
        
        return $exportManager->generate($alias, $data['export']);
    }
    
    /**
     * 
     * @param Request $request
     * @param string $alias
     * @return \Symfony\Component\Form\Form
     */
    public function createCreateFormGenerate(Request $request, $alias, 
            $aggregatorAliases, $formatterAlias)
    {
        $builder = $this->get('form.factory')
            ->createNamedBuilder(null, FormType::class, array(), array(
                'method' => 'GET',
                'csrf_protection' => false,
                'action' => $this->generateUrl('chill_main_export_generate', array(
                    'alias' => $alias
                ))
            ));
        
        $builder->add('formatter', FormatterType::class, array(
            'formatter_alias' => $formatterAlias,
            'export_alias' => $exportAlias,
            'aggregator_aliases' => $aggregatorAliases
        ));
        
        $builder->add('export', ExportType::class, array(
            'export_alias' => $alias,
        ));
        
        return $builder->getForm();
    }
    
    protected function recursiveAddHiddenFieldsToForm(FormBuilderInterface $builder, $key, $data)
    {
        if (is_array($data)) {
            foreach($data as $subKey => $value) {
                $subBuilder = $builder->create($subKey, FormType::class);
                $this->recursiveAddHiddenFieldsToForm($subBuilder, $subKey, $value);
                $builder->add($subBuilder);
            }
        } else {
            $builder->add($key, 'hidden', array(
                'data' => $data
            ));
        }
    }
}
