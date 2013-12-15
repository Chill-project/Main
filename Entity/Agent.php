<?php

namespace CL\Chill\MainBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * Agent
 */
class Agent extends BaseUser {
    
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    private $name;
    
    public function __construct() {
        parent::__construct();
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Agent
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function __toString() {
        return parent::__toString();
    }
}
