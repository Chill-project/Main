<?php


/*
 * Chill is a suite of a modules, Chill is a software for social workers
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

namespace Chill\MainBundle\Templating;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * 
 * This helper helps to find the string in current locale from translatable_strings
 * 
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 *
 */
class TranslatableStringHelper
{
    /**
     * 
     * @var RequestStack
     */
    private $requestStack;
    
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    
    /**
     * return the string in current locale if it exists.
     * 
     * If it does not exists; return the name in the first language available.
     * 
     * Return a blank string if any strings are available.
     * Return NULL if $translatableString is NULL
     * 
     * @param array $translatableStrings
     * @return string
     */
    public function localize(array $translatableStrings)
    {
        if (NULL === $translatableStrings) {
            return NULL;
        }
        
        $language = $this->requestStack->getCurrentRequest()->getLocale();
        

        if (isset($translatableStrings[$language])) {
            return $translatableStrings[$language];
        } else {
            foreach ($translatableStrings as $string) {
                if (!empty($string)) {
                    return $string;
                }
            }
        }
    
        return '';

    }

}