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
                return $this->exportFormStep($alias);
                break;
            case 'formatter':
                return $this->formatterFormStep($request, $alias);
                break;
            case 'generate':
                return $this->forwardToGenerate($request, $alias);
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
    protected function exportFormStep($alias)
    {
        $exportManager = $this->get('chill.main.export_manager');
        
        $export = $exportManager->getExport($alias);
                
        $form = $this->createCreateFormExport($alias, 'export');
        
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
    protected function createCreateFormExport($alias, $step, $data = array())
    {
        $builder = $this->get('form.factory')
              ->createNamedBuilder(null, FormType::class, array(), array(
                    'method' => 'GET',
                    'csrf_protection' => false,
                    'action' => $this->generateUrl($this->getNextRoute($step), array(
                        'alias' => $alias
                    ))               
              ));
        
        if ($step === 'export') {
            $builder->add('export', ExportType::class, array(
                'export_alias' => $alias,
            ));
        }
        
        if ($step === 'formatter') {
            $builder->add('formatter', FormatterType::class, array(
                'formatter_alias' => $data['export']['pick_formatter']['alias'],
                'export_alias' => $alias,
                'aggregator_aliases' => array() //TODO
            ));
        }
        
        $builder->add('submit', 'submit', array(
            'label' => 'Generate'
        ));
        
        $builder->add('step', 'hidden', array(
            'data' => $this->getNextStep($step)
        ));
        
        return $builder->getForm();
    }
    
    private function getNextStep($step)
    {
        switch($step) {
            case 'export': return 'formatter';
            case 'formatter' : return 'generate';
            default:
                throw new \LogicException("the step $step is not defined.");
        }
    }
    
    private function getNextRoute($step)
    {
        switch($step) {
            case 'generate': 
                return 'chill_main_export_generate';
            default:
                return 'chill_main_export_new';
        }
    }
    
    protected function formatterFormStep(Request $request, $alias)
    {
        $export = $this->get('chill.main.export_manager')->getExport($alias);
        
        $exportForm = $this->createCreateFormExport($alias, 'export');
        $exportForm->handleRequest($request);
        
        if ($exportForm->isValid()) {
            $data = $exportForm->getData();
            $this->get('session')->set('export_step', $data);
            $this->get('session')->set('export_step_raw', $request->query->all());

        $form = $this->createCreateFormExport($alias, 
                 'formatter', $data);
        
        return $this->render('ChillMainBundle:Export:new_formatter_step.html.twig',
                array(
                    'form' => $form->createView(),
                    'export' => $export
                ));
        
        } else {
            throw new \LogicException("The form contains invalid data. Currently"
                  . " we do not handle invalid data in forms");
        }
    }
    
    protected function forwardToGenerate(Request $request, $alias)
    {
        $data = $this->get('session')->get('export_step');
        
        $form = $this->createCreateFormExport($alias, 
                 'formatter', $data);
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $dataFormatter = $form->getData();
            $this->get('session')->set('formatter_step', $dataFormatter);
        }
        
        $redirectParameters = array_merge(
              $this->get('session')->get('export_step_raw'),
              $request->query->all(),
              array('alias' => $alias)
              );
        
        return $this->redirect($this->generateUrl(
                'chill_main_export_generate', $redirectParameters));
        
    }
    
    public function generateAction(Request $request, $alias)
    {
        $exportManager = $this->get('chill.main.export_manager');
        
        $formExport = $this->createCreateFormExport($alias, 'export');
        $formExport->handleRequest($request);
        $dataExport = $formExport->getData();
        
        $formFormatter = $this->createCreateFormExport($alias, 'formatter', 
              $dataExport);
        $formFormatter->handleRequest($request);
        $dataFormatter = $formFormatter->getData();
        
        return $exportManager->generate($alias, $dataExport['export']);
    }
}
