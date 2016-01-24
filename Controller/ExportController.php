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
use Chill\MainBundle\Form\Type\Export\FormatterType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Chill\MainBundle\Form\Type\Export\PickCenterType;


/**
 * ExportController is the controller use for exporting data.
 * 
 * 
 */
class ExportController extends Controller
{
    /**
     * Render the list of available exports
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $exportManager = $this->get('chill.main.export_manager');
        
        $exports = $exportManager->getExports(true);
        
        return $this->render('ChillMainBundle:Export:layout.html.twig', array(
            'exports' => $exports
        ));
    }
    
    /**
     * handle the step to build a query for an export
     * 
     * This action has three steps :
     * 
     * 1.'export', the export form. When the form is posted, the data is stored
     * in the session (if valid), and then a redirection is done to next step.
     * 2. 'formatter', the formatter form. When the form is posted, the data is
     * stored in the session (if valid), and then a redirection is done to next step.
     * 3. 'generate': gather data from session from the previous steps, and 
     * make a redirection to the "generate" action with data in query (HTTP GET)
     * 
     * @param string $request
     * @param Request $alias
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request, $alias)
    {
        // first check for ACL
        $exportManager = $this->get('chill.main.export_manager');
        $export = $exportManager->getExport($alias);
        $centers = $this->get('chill.main.security.authorization.helper')
                ->getReachableCenters($this->getUser(), $export->requiredRole());
        if ($exportManager->isGrantedForElement($export, $centers) === FALSE) {
            throw $this->createAccessDeniedException('The user does not have access to this export');
        }
        
        $step = $request->query->getAlpha('step', 'centers');
        
        switch ($step) {
            case 'centers':
                return $this->selectCentersStep($request, $alias);
            case 'export':
                return $this->exportFormStep($request, $alias);
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
    
    public function selectCentersStep(Request $request, $alias)
    {
        /* @var $exportManager \Chill\MainBundle\Export\ExportManager */
        $exportManager = $this->get('chill.main.export_manager');
        
        $form = $this->createCreateFormExport($alias, 'centers');
        
        $export = $exportManager->getExport($alias);
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->get('logger')->debug('form centers is valid', array(
                      'location' => __METHOD__));
                
                $data = $form->getData();
                
                // check ACL
                if ($exportManager->isGrantedForElement($export, 
                        $exportManager->getPickedCenters($data['centers'])) === FALSE) {
                    throw $this->createAccessDeniedException('you do not have '
                            . 'access to this export for those centers');
                }
                
                $this->get('session')->set('centers_step_raw', 
                      $request->request->all());
                $this->get('session')->set('centers_step', $data);
                
                return $this->redirectToRoute('chill_main_export_new', array(
                   'step' => $this->getNextStep('centers'),
                   'alias' => $alias
                ));
                
            }
        }
        
        return $this->render('ChillMainBundle:Export:new_centers_step.html.twig',
                array(
                    'form' => $form->createView(),
                    'export' => $export
                ));
    }
    
    /**
     * Render the export form
     * 
     * When the method is POST, the form is stored if valid, and a redirection
     * is done to next step.
     * 
     * @param string $alias
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function exportFormStep(Request $request, $alias)
    {
        $exportManager = $this->get('chill.main.export_manager');
        
        // check we have data from the previous step (export step)
        $data = $this->get('session')->get('centers_step', null);
        
        if ($data === null) {
            
            return $this->redirectToRoute('chill_main_export_new', array(
               'step' => $this->getNextStep('export', true),
               'alias' => $alias
               ));
        }
        
        $export = $exportManager->getExport($alias);
                
        $form = $this->createCreateFormExport($alias, 'export', $data);
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                
                $this->get('logger')->debug('form export is valid', array(
                      'location' => __METHOD__));
                
                // store data for reusing in next steps
                $data = $form->getData();
                $this->get('session')->set('export_step_raw', 
                      $request->request->all());
                $this->get('session')->set('export_step', $data);
                
                //redirect to next step
                return $this->redirect(
                      $this->generateUrl('chill_main_export_new', array(
                         'step' => $this->getNextStep('export'),
                         'alias' => $alias
                      )));
            } else {
                $this->get('logger')->debug('form export is invalid', array(
                      'location' => __METHOD__));
            }
        }
        
        return $this->render('ChillMainBundle:Export:new.html.twig', array(
            'form' => $form->createView(),
            'export_alias' => $alias,
            'export' => $export
        ));
    }
    
    /**
     * create a form to show on different steps. 
     * 
     * @param string $alias
     * @param string $step, can either be 'export', 'formatter', 'generate_export' or 'generate_formatter' (last two are used by generate action)
     * @param array $data the data from previous step. Required for steps 'formatter' and 'generate_formatter'
     * @return \Symfony\Component\Form\Form
     */
    protected function createCreateFormExport($alias, $step, $data = array())
    {
        /* @var $exportManager \Chill\MainBundle\Export\ExportManager */
        $exportManager = $this->get('chill.main.export_manager');
        $isGenerate = strpos($step, 'generate_') === 0;
        
        $builder = $this->get('form.factory')
              ->createNamedBuilder(null, FormType::class, array(), array(
                    'method' => $isGenerate ? 'GET' : 'POST',
                    'csrf_protection' => $isGenerate ? false : true,              
              ));
        
        if ($step === 'centers') {
            $builder->add('centers', PickCenterType::class, array(
               'export_alias' => $alias 
            ));
        }
        
        if ($step === 'export' or $step === 'generate_export') {
            $builder->add('export', ExportType::class, array(
               'export_alias' => $alias,
               'picked_centers' => $exportManager->getPickedCenters($data['centers'])
            ));
        }
        
        if ($step === 'formatter' or $step === 'generate_formatter') {
            $builder->add('formatter', FormatterType::class, array(
                'formatter_alias' => $exportManager
                     ->getFormatterAlias($data['export']),
                'export_alias' => $alias,
                'aggregator_aliases' => $exportManager
                    ->getUsedAggregatorsAliases($data['export'])
            ));
        }
        
        //if (strpos($step, 'generate_') !== false) {
            $builder->add('submit', 'submit', array(
                'label' => 'Generate'
            ));
        //}
        
        return $builder->getForm();
    }
    
    /**
     * get the next step. If $reverse === true, the previous step is returned.
     * 
     * This method provides a centralized way of handling next/previous step.
     * 
     * @param string $step the current step
     * @param boolean $reverse set to true to get the previous step
     * @return string the next/current step
     * @throws \LogicException if there is no step before or after the given step
     */
    private function getNextStep($step, $reverse = false)
    {
        switch($step) {
            case 'centers': 
                if ($reverse !== false) {
                    throw new \LogicException("there is no step before 'export'");
                }
                return 'export';
            case 'export':
                return $reverse ? 'centers' : 'formatter';
            case 'formatter' : 
                return $reverse ? 'export' : 'generate';
            case 'generate' : 
                if ($reverse === false) {
                    throw new \LogicException("there is no step after 'generate'");
                }
                return 'formatter';
                
            default:
                throw new \LogicException("the step $step is not defined.");
        }
    }
    
    /**
     * Render the form for formatter. 
     * 
     * If the form is posted and valid, store the data in session and 
     * redirect to the next step.
     * 
     * @param Request $request
     * @param string $alias
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function formatterFormStep(Request $request, $alias)
    {
        $export = $this->get('chill.main.export_manager')->getExport($alias);
        
        // check we have data from the previous step (export step)
        $data = $this->get('session')->get('export_step', null);
        
        if ($data === null) {
            
            return $this->redirectToRoute('chill_main_export_new', array(
               'step' => $this->getNextStep('formatter', true),
               'alias' => $alias
               ));
        }

        $form = $this->createCreateFormExport($alias, 'formatter', $data);
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $dataFormatter = $form->getData();
                $this->get('session')->set('formatter_step', $dataFormatter);
                $this->get('session')->set('formatter_step_raw', 
                      $request->request->all());

                //redirect to next step
                return $this->redirect($this->generateUrl('chill_main_export_new', 
                      array(
                         'alias' => $alias, 
                         'step' => $this->getNextStep('formatter')
                )));
            }
        }
        
        return $this->render('ChillMainBundle:Export:new_formatter_step.html.twig',
                array(
                    'form' => $form->createView(),
                    'export' => $export
                ));
    }
    
    /**
     * Gather data stored in session from previous steps, and redirect
     * to the `generate` action, compiling data from previous step in the URL
     * (to obtain a GET HTTP query).
     * 
     * The data from previous steps is removed from session.
     * 
     * @param Request $request
     * @param string $alias
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function forwardToGenerate(Request $request, $alias)
    {
        $dataCenters = $this->get('session')->get('centers_step_raw', null);
        $dataFormatter = $this->get('session')->get('formatter_step_raw', null);
        $dataExport = $this->get('session')->get('export_step_raw', null);
        
        if ($dataFormatter === NULL) {
            return $this->redirectToRoute('chill_main_export_new', array(
               'alias' => $alias, 'step' => $this->getNextStep('generate', true)
            ));
        }
        
        // remove data from session
        $this->get('session')->remove('export_step_raw');
        $this->get('session')->remove('export_step');
        $this->get('session')->remove('formatter_step_raw');
        $this->get('session')->remove('formatter_step');
        
        $redirectParameters = array_merge(
              $dataFormatter,
              $dataExport,
              $dataCenters,
              array('alias' => $alias)
              );
        unset($redirectParameters['_token']);
        
        return $this->redirectToRoute('chill_main_export_generate', 
              $redirectParameters);
    }
    
    /**
     * Generate a report.
     * 
     * This action must work with GET queries.
     * 
     * @param Request $request
     * @param string $alias
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function generateAction(Request $request, $alias)
    {
        $exportManager = $this->get('chill.main.export_manager');
        
        $formExport = $this->createCreateFormExport($alias, 'generate_export');
        $formExport->handleRequest($request);
        $dataExport = $formExport->getData();
        
        $formFormatter = $this->createCreateFormExport($alias, 'generate_formatter', 
              $dataExport);
        $formFormatter->handleRequest($request);
        $dataFormatter = $formFormatter->getData();
        
        return $exportManager->generate($alias, $dataExport['export'], $dataFormatter['formatter']);
    }
}
