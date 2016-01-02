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
    
    public function newAction($alias)
    {
        $exportManager = $this->get('chill.main.export_manager');
        
        $export = $exportManager->getExport($alias);
        
        $form = $this->createCreateForm($alias);
        
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
    protected function createCreateForm($alias)
    {
        $form = $this->createForm(ExportType::class, array(), array(
            'export_alias' => $alias,
            'method' => 'GET',
            'action' => $this->generateUrl('chill_main_export_generate', array(
                'alias' => $alias
            ))
        ));
        
        $form->add('submit', 'submit', array(
            'label' => 'Generate'
        ));
        
        return $form;
    }
    
    public function generateAction(Request $request, $alias)
    {
        $exportManager = $this->get('chill.main.export_manager');
        
        $form = $this->createCreateForm($alias);
        $form->handleRequest($request);
        
        return $exportManager->generate($alias, $form->getData());
    }
}
