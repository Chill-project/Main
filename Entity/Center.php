<?php

/*
 * 
 * Copyright (C) 2014, Champs Libres Cooperative SCRLFS, <http://www.champs-libres.coop>
 * 
 * This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chill\MainBundle\Entity;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class Center
{
    /**
     *
     * @var string
     */
    private $name;
    
    /**
     *
     * @var int 
     */
    private $id;
    
    /**
     *
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupCenters;
    
    public function __construct()
    {
        $this->groupCenters = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function getGroupCenters()
    {
        return $this->groupCenters;
    }

    public function addGroupCenter(GroupCenter $groupCenter)
    {
        $this->groupCenters->add($groupCenter);
        return $this;
    }


}
