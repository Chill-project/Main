<?php

namespace Chill\MainBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Agent
 */
class User implements UserInterface {
    
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    private $username;
    
    private $password;
    
    private $salt;
    
    



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
     * Set username
     *
     * @param string $name
     * @return Agent
     */
    public function setUsername($name)
    {
        $this->username = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->username;
    }
    
    public function __toString() {
        return $this->getUsername();
    }

    public function eraseCredentials()
    {
        
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return 'ROLE_USER';
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }
    
    function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }



}
