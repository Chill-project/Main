<?php


/*
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

namespace Chill\MainBundle\Search;

/**
 * This interface must be implemented on services which provide search results.
 * 
 * @todo : write doc and add a link to documentation
 * 
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 *
 */
interface SearchInterface #-> good name ?
{
   /** 
    * return the result in a html string. The string will be inclued (as raw)
    * into a global view.
    * 
    * The global view may be :
    * {% for result as resultsFromDifferentSearchInterface %}
    *    {{ result|raw }}
    * {% endfor %}
    *
    * @param array  $terms   the string to search
    * @param int    $start   the first result (for pagination)
    * @param int    $limit   the number of result (for pagination)
    * @param array  $option  the options, specific for each search
    * @return string, an HTML string
    */
   public function renderResult(array $terms, $start=0, $limit=50, array $options = array());

   /** 
    * we may desactive the search interface by default. in this case,
    * the search will be launch and rendered only with "advanced search" 
    * 
    * this may be activated/desactived from bundle definition in config.yml
    *
    * @return boolean
    */
   public function isActiveByDefault();

   /**
    * the order in which the results will appears in the global view
    * 
    * (this may be eventually defined in config.yml)
    * 
    * @return int 
    */
   public function getOrder();
   
   /**
    * indicate if the implementation supports the given domain
    * 
    * @return boolean
    */
   public function supports($domain);
   
}
