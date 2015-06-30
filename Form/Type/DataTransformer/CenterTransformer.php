<?php

/*
 * Copyright (C) 2015 Julien Fastré <julien.fastre@champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chill\MainBundle\Form\Type\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transform a center object to his id, and vice-versa
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class CenterTransformer implements DataTransformerInterface
{
    /**
     *
     * @var ObjectManager
     */
    private $om;
    
    public function __construct(ObjectManager $om) 
    {
        $this->om = $om;
    }

    public function reverseTransform($id)
    {
        if ($id === NULL) {
            return NULL;
        }
        
        $center = $this->om->getRepository('ChillMainBundle:Center')
                ->find($id);
        
        if ($center === NULL) {
            throw new TransformationFailedException(sprintf(
                    'No center found with id %d', $id));
        }
        
        return $center;
    }

    public function transform($center)
    {
        if ($center === NULL) {
            return '';
        }
        
        return $center->getId();
    }

}
