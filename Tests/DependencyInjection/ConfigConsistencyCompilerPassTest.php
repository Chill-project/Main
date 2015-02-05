<?php

/*
 * Chill is a software for social workers
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

namespace Chill\MainBundle\Tests\DependencyInjection;

use Chill\MainBundle\DependencyInjection\ConfigConsistencyCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilderInterface;

/**
 * Description of ConfigConsistencyCompilerPassTest
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ConfigConsistencyCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * 
     *
     * @var \Chill\MainBundle\DependencyInjection\ConfigConsistencyCompilerPass 
     */
    private $configConsistencyCompilerPass;
    
    public function setUp()
    {
        $this->configConsistencyCompilerPass = new ConfigConsistencyCompilerPass();
    }
    
    /**
     * Test that everything is fine is configuration is correct
     * 
     */
    public function testLanguagesArePresent()
    {
        try {
            $this ->configConsistencyCompilerPass
                  ->process(
                        $this->mockContainer(
                              $this->mockTranslatorDefinition(array('fr')),
                              array('fr', 'nl')
                        )
                    );
            $this->assertTrue(TRUE, 'the config consistency can process');
        } catch (\Exception $ex) {
            $this->assertTrue(FALSE, 
                  'the config consistency can process');
        }
    }
    
    /**
     * Test that everything is fine is configuration is correct
     * if multiple fallback languages are present
     * 
     */
    public function testMultiplesLanguagesArePresent()
    {
        try {
            $this ->configConsistencyCompilerPass
                  ->process(
                        $this->mockContainer(
                              $this->mockTranslatorDefinition(array('fr', 'nl')),
                              array('fr', 'nl', 'en')
                        )
                    );
            $this->assertTrue(TRUE, 'the config consistency can process');
        } catch (\Exception $ex) {
            $this->assertTrue(FALSE, 
                  'the config consistency can process');
        }
    }
    
    
    
    /**
     * Test that a runtime exception is throw if the available language does 
     * not contains the fallback locale
     * 
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /The chill_main.available_languages parameter does not contains fallback locales./
     */
    public function testLanguageNotPresent()
    {
        $container = $this->mockContainer(
              $this->mockTranslatorDefinition(array('en')), array('fr')
              );
        
        $this->configConsistencyCompilerPass->process($container);
    }
    
    /**
     * Test that a logic exception is thrown if the setFallbackLocale
     * method is not defined in translator definition
     * 
     * @expectedException \LogicException
     */
    public function testSetFallbackNotDefined()
    {
        $container = $this->mockContainer(
              $this->mockTranslatorDefinition(NULL), array('fr')
              );
        $this->configConsistencyCompilerPass->process($container);
    }
    
    /**
     * @return ContainerBuilder
     */
    private function mockContainer($definition, $availableLanguages)
    {
        $container = $this
              ->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
              ->getMock();
        
        $container->method('getParameter')
              ->will($this->returnCallback(
                        function($parameter) use ($availableLanguages) {
                            if ($parameter === 'chill_main.available_languages') {
                                return $availableLanguages;
                            } else {
                                throw new \LogicException("the parameter '$parameter' "
                                      . "is not defined in stub test");
                            }
                        }
                    ));
        
        $container->method('findDefinition')
              ->will($this->returnCallback(
                    function($id) use ($definition) { 
                        if (in_array($id, array('translator', 'translator.default'))) {
                            return $definition;
                        } else {
                            throw new \LogicException("the id $id is not defined in test");
                        }
                  }));
                  
        
        return $container;
    }
    
    /**
     * 
     * @param type $languages
     * @return 'Symfony\Component\DependencyInjection\Definition'
     */
    private function mockTranslatorDefinition(array $languages = NULL)
    {
        $definition = $this
              ->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
              ->getMock();
        
        if (NULL !== $languages) {
            $definition->method('getMethodCalls')
                  ->willReturn(array(
                    ['setFallbackLocales', array($languages)]
                     ));
        } else {
            $definition->method('getMethodCalls')
                  ->willReturn(array(['nothing', array()]));
        }
        
        return $definition;
    }
}
