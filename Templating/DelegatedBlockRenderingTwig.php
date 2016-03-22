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

namespace Chill\MainBundle\Templating;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Add the function `chill_delegated_block`.
 * 
 * In a template, you can now allow rendering of a block from other bundle.
 *  
 *  The layout template must explicitly call the rendering of other block,
 *  with the twig function
 *  
 *  ```
 *  chill_delegated_block('block_name', { 'array' : 'with context' } )
 *  ```
 *  
 *  This will launch an event
 *  `Chill\MainBundle\Templating\Events\DelegatedBlockRenderingEvent` with
 *  the event's name 'chill_block.block_name'.
 *  
 *  You may add content to the page using the function
 *  `DelegatedBlockRenderingEvent::addContent`.
 * 
 * See also the documentation of 
 * `Chill\MainBundle\Templating\Events\DelegatedBlockRenderingEvent`
 * for usage of this event class
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class DelegatedBlockRenderingTwig extends \Twig_Extension
{
    /**
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
    
    
    public function getName()
    {
        return 'chill_main_delegated_block';
    }
    
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('chill_delegated_block', 
                    array($this, 'renderingDelegatedBlock'),
                    array('is_safe' => array('html')))
        );
    }
    
    public function renderingDelegatedBlock($block, array $context)
    {
        $event = new Events\DelegatedBlockRenderingEvent($context);
        
        $this->eventDispatcher->dispatch('chill_block.'.$block, $event);
        
        return $event->getContent();
    }

}
