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

class ObjectToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var string
     */
    private $class;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, $class)
    {
        $this->om = $om;
        $this->class = $class;
    }

    /**
     * Transforms an object to a string (id)
     *
     * @param  Object|null $Object
     * @return string
     */
    public function transform($object)
    {
        if (!$object) {
            return "";
        }

        return $object->getId();
    }

    /**
     * Transforms a string (id) to an object
     *
     * @param  string $id
     * @return Object|null
     * @throws TransformationFailedException if object is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $object = $this->om
            ->getRepository($this->class)
            ->find($id)
        ;

        if (! $object) {
            throw new TransformationFailedException();
        }

        return $object;
    }
}