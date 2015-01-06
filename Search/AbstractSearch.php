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

use Chill\MainBundle\Search\SearchInterface;
use Chill\MainBundle\Search\ParsingException;

/**
 * This class implements abstract search with most common responses.
 * 
 * you should use this abstract class instead of SearchInterface : if the signature of
 * search interface change, the generic method will be implemented here.
 * 
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 *
 */
abstract class AbstractSearch implements SearchInterface
{
    /**
     * parse string expected to be a date and transform to a DateTime object
     * 
     * @param type $string
     * @return \DateTime
     * @throws ParsingException if the  date is not parseable
     */
    public function parseDate($string)
    {
        try {
            return new \DateTime($string);
        } catch (ParsingException $ex) {
            $exception = new ParsingException('The date is '
                      . 'not parsable', 0, $ex);
            throw $exception;
        }
        
    }
    
    /**
     * recompose a pattern, retaining only supported terms
     * 
     * the outputted string should be used to show users their search
     * 
     * @param array $terms
     * @param array $supportedTerms
     * @param string $domain if your domain is NULL, you should set NULL. You should set used domain instead
     * @return string
     */
    protected function recomposePattern(array $terms, array $supportedTerms, $domain = NULL)
    {
        $recomposed = '';
        
        if ($domain !== NULL)
        {
            $recomposed .= '@'.$domain.' ';
        }
        
        foreach ($supportedTerms as $term) {
            if (array_key_exists($term, $terms) && $term !== '_default') {
                $recomposed .= ' '.$term.':';
                $recomposed .= (mb_stristr(' ', $terms[$term]) === FALSE) ?  $terms[$term] : '('.$terms[$term].')';
            }
        }
        
        if ($terms['_default'] !== '') {
            $recomposed .= ' '.$terms['_default'];
        }
        
        //strip first character if empty
        if (mb_strcut($recomposed, 0, 1) === ' '){
            $recomposed = mb_strcut($recomposed, 1);
        }
        
        return $recomposed;
    }
}