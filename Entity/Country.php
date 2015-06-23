<?php

namespace Chill\MainBundle\Entity;

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
    private $name;
    
    /**
     * 
     * @var string
     */
    private $countryCode;


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
     * @return Country
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
        return $this->getName();
    }

    /**
     *
     * @return the string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     *
     * @param string $countryCode            
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
        return $this;
    }
 
}
