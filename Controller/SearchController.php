<?php


/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2014, Champs Libres Cooperative SCRLFS, <http://www.champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
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
use Chill\MainBundle\Search\UnknowSearchDomainException;
use Chill\MainBundle\Search\UnknowSearchNameException;
use Chill\MainBundle\Search\ParsingException;

/**
 *
 *
 * @author julien.fastre@champs-libres.coop
 * @author marc@champs-libres.coop
 */
class SearchController extends Controller
{
    
    public function searchAction(Request $request)
    {
        $pattern = $request->query->get('q', '');
        
        if ($pattern === ''){
            return $this->render('ChillMainBundle:Search:error.html.twig',
                  array(
                     'message' => $this->get('translator')->trans("Your search is empty. "
                           . "Please provide search terms."),
                     'pattern' => $pattern
                  ));
            
        }
        
        $name = $request->query->get('name', NULL);
        
        try {
            if ($name === NULL) {
                $results = $this->get('chill.main.search_provider')
                  ->getSearchResults($request->query->get('q'));
            } else {
                $results = [$this->get('chill.main.search_provider')
                      ->getResultByName($pattern, $name)];
            }
        } catch (UnknowSearchDomainException $ex) {
            return $this->render('ChillMainBundle:Search:error.html.twig', 
                  array(
                     "message" => $this->get('translator')->trans("The domain %domain% "
                           . "is unknow. Please check your search.", array('%domain%' => $ex->getDomain())),
                     'pattern' => $pattern
                  ));
        } catch (UnknowSearchNameException $ex) {
            throw $this->createNotFoundException("The name ".$ex->getName()." is not found");
        } catch (ParsingException $ex) {
            return $this->render('ChillMainBundle:Search:error.html.twig', 
                  array(
                     "message" => $this->get('translator')->trans('Invalid terms').
                     ": ".$this->get('translator')->trans($ex->getMessage()),
                     'pattern' => $pattern
                  ));
        }
        
    
        return $this->render('ChillMainBundle:Search:list.html.twig', 
              array('results' => $results, 'pattern' => $pattern) 
              );
    }
    
}