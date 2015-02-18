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

namespace Chill\MainBundle\Templating;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class TranslatableStringTwig extends \Twig_Extension
{
    use ContainerAwareTrait;

    /*
     * (non-PHPdoc)
     * @see Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('localize_translatable_string', array($this, 'localize')),
            new \Twig_SimpleFilter(
                'csv_cell',
                array($this, 'csvCellFilter'),
                array('is_safe' => array('html')))
        );
    }

    /*
     *
     */
    public function csvCellFilter($content)
    {
        return str_replace('"', '""', $content);
    }
    
    public function localize(array $translatableStrings)
    {
        return $this->container->get('chill.main.helper.translatable_string')
            ->localize($translatableStrings);
    }
    
    public function getName()
    {
        return 'chill_main_localize';
    }

}