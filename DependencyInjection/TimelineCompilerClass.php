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

namespace Chill\MainBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add services taggued with  `name: chill.timeline` to 
 * timeline_builder service definition
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class TimelineCompilerClass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('chill.main.timeline_builder')) {
            throw new \LogicException('service chill.main.timeline_builder '
                    . 'is not defined.');
        }
        
        $definition = $container->getDefinition(
            'chill.main.timeline_builder'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'chill.timeline'
        );
        
        foreach ($taggedServices as $id => $tagAttributes) {
            
            foreach ($tagAttributes as $attributes) {
                
                if (!isset($attributes["context"])) {
                    throw new \LogicException("the 'context' attribute is missing in your ".
                        "service '$id' definition");
                }

                $definition->addMethodCall(
                    'addProvider',
                    array($attributes["context"], $id)
                );
            }
        }
    }

}
