<?php

/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2014, Champs Libres Cooperative SCRLFS, <http://www.champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Chill\MainBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class SearchableServicesCompilerPass implements CompilerPassInterface
{

    /*
     * (non-PHPdoc)
     * @see \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface::process()
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('chill.main.search_provider')) {
            throw new \LogicException('service chill.main.search_provider '
                    . 'is not defined.');
        }
        
        $definition = $container->getDefinition(
            'chill.main.search_provider'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'chill.search'
        );
        
        $knownAliases = array();
        
        foreach ($taggedServices as $id => $tagAttributes) {
            
            foreach ($tagAttributes as $attributes) {
                
                if (!isset($attributes["alias"])) {
                    throw new \LogicException("the 'name' attribute is missing in your ".
                        "service '$id' definition");
                }
                
                if (array_search($attributes["alias"], $knownAliases)) {
                    throw new \LogicException("There is already a chill.search service with alias "
                        .$attributes["alias"].". Choose another alias.");
                }
                $knownAliases[] = $attributes["alias"];
                
                $definition->addMethodCall(
                    'addSearchService',
                    array(new Reference($id), $attributes["alias"])
                );
            }
        }
    }
}