<?php

/*
 * Copyright (C) 2016 Champs-Libres <info@champs-libres.coop>
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
use Chill\MainBundle\Export\ExportManager;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transform a formatter alias to an FormatterInterface class
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class AliasToFormatterTransformer implements DataTransformerInterface
{
    /**
     *
     * @var ExportManager
     */
    protected $exportManager;
    
    public function __construct(ExportManager $exportManager)
    {
        $this->exportManager = $exportManager;
    }

    public function reverseTransform($value)
    {
        if ($value === NULL) {
            return NULL;
        }
        
        if (!value instanceof \Chill\MainBundle\Export\FormatterInterface) {
            throw new TransformationFailedException("The given value is not a "
                    . "Chill\MainBundle\Export\FormatterInterface");
        }
        
        // we do not have the alias, which is only known by the container.
        // we try to check the formatter by the php internal object id.
        $formatters = $this->exportManager
                ->getFormattersByTypes(array($value->getType()));
        foreach($formatters as $alias => $formatter) {
            if (spl_object_hash($formatter) === spl_object_hash($value)) {
                return $alias;
            }
        }
        
        throw new TransformationFailedException("The formatter could not be found "
                . "by his object_hash. Maybe you created a formatter manually ? "
                . "Use the export manager to get your formatter.");
    }

    /**
     * 
     * @param type $value
     * @return \Chill\MainBundle\Export\FormatterInterface
     * @throws TransformationFailedException
     */
    public function transform($value)
    {
        if (empty($value)) {
            throw new TransformationFailedException("The formatter with empty "
                    . "alias is not allowed. Given value is ".$value);
        }
        
        return $this->exportManager->getFormatter($value);
    }

}
