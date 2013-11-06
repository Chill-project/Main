<?php

namespace CL\Chill\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country
 */
class Country
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $label;


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
     * Set label
     *
     * @param string $label
     * @return Country
     */
    public function setLabel($label)
    {
        $this->label = $label;
    
        return $this;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }
    
    public function __toString() {
        return $this->getLabel();
    }
}
