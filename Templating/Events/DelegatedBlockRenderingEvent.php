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

namespace Chill\MainBundle\Templating\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * This event is transmitted on event chill_block.*
 * 
 * You may access to the context as an array : 
 * 
 * ```
 * $var = $event['context_key']
 * ```
 * 
 * The documentation for the bundle where the event is launched should give 
 * you the context keys.
 * 
 * The keys are read-only: if you try to update the context using array access
 * (example, using `$event['context_key'] = $bar;`, an error will be thrown.
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class DelegatedBlockRenderingEvent extends Event implements \ArrayAccess
{
    /**
     *
     * @var mixed[]
     */
    protected $context;
    
    /**
     * The returned content of the event
     *
     * @var string
     */
    protected $content = '';
    
    public function __construct(array $context)
    {
        $this->context = $context;
    }
    
    /**
     * add content to the event. This content will be printed in the
     * layout which launched the event
     * 
     * @param string $text
     */
    public function addContent($text)
    {
        $this->content .= $text;
    }
    
    /**
     * the content of the event
     * 
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
    
    public function offsetExists($offset)
    {
        return isset($this->context[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->context[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException("The event context is read-only, you are not "
                . "allowed to update it.");
    }

    public function offsetUnset($offset)
    {
        throw new \RuntimeException("The event context is read-only, you are not "
                . "allowed to update it.");
    }

}
