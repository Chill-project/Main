<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2015 Champs-Libres Coopérative <info@champs-libres.coop>
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

/**
 * Description of ConfigConsistencyCompilerPass
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ConfigConsistencyCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $availableLanguages = $container
              ->getParameter('chill_main.available_languages');
        $methodCallsTranslator = $container
              ->findDefinition('translator.default')
              ->getMethodCalls();
        
        $fallbackLocales = array();
        foreach($methodCallsTranslator as $call) {
            if ($call[0] === 'setFallbackLocales') {
                $fallbackLocales = array_merge($fallbackLocales,
                      $call[1][0]);
            }    
        }
        
        if (count($fallbackLocales) === 0) {
            throw new \LogicException('the fallback locale are not defined. '
                  . 'The framework config should not allow this.');
        }
        
        $diff = array_diff($fallbackLocales, $availableLanguages);
        if (count($diff) > 0) {
            throw new \RuntimeException(sprintf('The chill_main.available_languages'
                  . ' parameter does not contains fallback locales. The languages %s'
                  . ' are missing.', implode(', ', $diff)));
        }
    }
}
