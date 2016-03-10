<?php

namespace Chill\MainBundle\Entity;

/**
 * Address
 */
class Address
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $streetAddress1 = '';

    /**
     * @var string
     */
    private $streetAddress2 = '';

    /**
     * @var \Chill\MainBundle\Entity\PostalCode
     */
    private $postcode;
    
    /**
     *
     * @var \DateTime
     */
    private $validFrom;
    
    public function __construct()
    {
        $this->validFrom = new \DateTime();
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
     * Set streetAddress1
     *
     * @param string $streetAddress1
     *
     * @return Address
     */
    public function setStreetAddress1($streetAddress1)
    {
        $this->streetAddress1 = $streetAddress1 === NULL ? '' : $streetAddress1;

        return $this;
    }

    /**
     * Get streetAddress1
     *
     * @return string
     */
    public function getStreetAddress1()
    {
        return $this->streetAddress1;
    }

    /**
     * Set streetAddress2
     *
     * @param string $streetAddress2
     *
     * @return Address
     */
    public function setStreetAddress2($streetAddress2)
    {
        $this->streetAddress2 = $streetAddress2 === NULL ? '' : $streetAddress2;

        return $this;
    }

    /**
     * Get streetAddress2
     *
     * @return string
     */
    public function getStreetAddress2()
    {
        return $this->streetAddress2;
    }

    /**
     * Set postcode
     *
     * @param \Chill\MainBundle\Entity\PostalCode $postcode
     *
     * @return Address
     */
    public function setPostcode(\Chill\MainBundle\Entity\PostalCode $postcode = null)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get postcode
     *
     * @return \Chill\MainBundle\Entity\PostalCode
     */
    public function getPostcode()
    {
        return $this->postcode;
    }
    
    /**
     * 
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * 
     * @param \DateTime $validFrom
     * @return \Chill\MainBundle\Entity\Address
     */
    public function setValidFrom(\DateTime $validFrom)
    {
        $this->validFrom = $validFrom;
        return $this;
    }


}

