<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2014 Julien Fastré <julien.fastre@champs-libres.coop>
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
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
 
class MultipleObjectsToIdTransformer implements DataTransformerInterface
{
    /**
    * @var ObjectManager
    */
    private $em;
 
    /**
    * @var string
    */
    private $class;
 
    /**
    * @param ObjectManager $em
    */
    public function __construct(ObjectManager $em, $class)
    {
        $this->em = $em;
        $this->class = $class;
    }
 
    /**
    * Transforms an object (use) to a string (id).
    *
    * @param array $array
    * @return ArrayCollection
    */
    public function transform($array)
    {
        $ret = array();

        foreach ($array as $el) {
            $ret[] = ($el->getId());
        }

        return $ret;
    }
 
    /**
    * Transforms a string (id) to an object (item).
    *
    * @param string $id
    * @return ArrayCollection
    */
    public function reverseTransform($array)
    {
        $ret = new ArrayCollection();

        foreach ($array as $el) {
            $ret->add(
                $this->em
                    ->getRepository($this->class)
                    ->find($el)
            );
        }
        return $ret;
    }
}