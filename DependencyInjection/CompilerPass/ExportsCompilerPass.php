<?php

/*
 * Copyright (C) 2015 Champs-Libres <info@champs-libres.coop>
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

namespace Chill\MainBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Compiles the services tagged with : 
 * 
 * - chill.export
 * - chill.export_formatter
 * - chill.export_aggregator
 * - chill.export_filter
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ExportsCompilerPass implements CompilerPassInterface
{
    
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('chill.main.export_manager')) {
            throw new \LogicException('service chill.main.export_manager '
                    . 'is not defined. It is required by ExportsCompilerPass');
        }
        
        $chillManagerDefinition = $container->getDefinition(
            'chill.main.export_manager'
        );

        $this->compileExports($chillManagerDefinition, $container);
        $this->compileFilters($chillManagerDefinition, $container);
        $this->compileAggregators($chillManagerDefinition, $container);
        $this->compileFormatters($chillManagerDefinition, $container);
    }
    
    private function compileExports(Definition $chillManagerDefinition, 
            ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds(
            'chill.export'
        );
        
        $knownAliases = array();
        
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (!isset($attributes["alias"])) {
                    throw new \LogicException("the 'alias' attribute is missing in your ".
                        "service '$id' definition");
                }
                
                if (array_search($attributes["alias"], $knownAliases)) {
                    throw new \LogicException("There is already a chill.export service with alias "
                        .$attributes["alias"].". Choose another alias.");
                }
                $knownAliases[] = $attributes["alias"];
                
                $chillManagerDefinition->addMethodCall(
                    'addExport',
                    array(new Reference($id), $attributes["alias"])
                );
            }
        }
    }
    
    private function compileFilters(Definition $chillManagerDefinition,
            ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds(
            'chill.export_filter'
        );
        
        $knownAliases = array();
        
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (!isset($attributes["alias"])) {
                    throw new \LogicException("the 'alias' attribute is missing in your ".
                        "service '$id' definition");
                }
                
                if (array_search($attributes["alias"], $knownAliases)) {
                    throw new \LogicException("There is already a chill.export_filter service with alias "
                        .$attributes["alias"].". Choose another alias.");
                }
                $knownAliases[] = $attributes["alias"];
                
                $chillManagerDefinition->addMethodCall(
                    'addFilter',
                    array(new Reference($id), $attributes["alias"])
                );
            }
        }
    }
    
    private function compileAggregators(Definition $chillManagerDefinition,
            ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds(
            'chill.export_aggregator'
        );
        
        $knownAliases = array();
        
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (!isset($attributes["alias"])) {
                    throw new \LogicException("the 'alias' attribute is missing in your ".
                        "service '$id' definition");
                }
                
                if (array_search($attributes["alias"], $knownAliases)) {
                    throw new \LogicException("There is already a chill.export_aggregator service with alias "
                        .$attributes["alias"].". Choose another alias.");
                }
                $knownAliases[] = $attributes["alias"];
                
                $chillManagerDefinition->addMethodCall(
                    'addAggregator',
                    array(new Reference($id), $attributes["alias"])
                );
            }
        }
    }
    
    private function compileFormatters(Definition $chillManagerDefinition,
            ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds(
            'chill.export_formatter'
        );
        
        $knownAliases = array();
        
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (!isset($attributes["alias"])) {
                    throw new \LogicException("the 'alias' attribute is missing in your ".
                        "service '$id' definition");
                }
                
                if (array_search($attributes["alias"], $knownAliases)) {
                    throw new \LogicException("There is already a chill.export_formatter service with alias "
                        .$attributes["alias"].". Choose another alias.");
                }
                $knownAliases[] = $attributes["alias"];
                
                $chillManagerDefinition->addMethodCall(
                    'addFormatter',
                    array(new Reference($id), $attributes["alias"])
                );
            }
        }
    }

}
