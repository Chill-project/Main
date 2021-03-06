<?php

namespace Chill\MainBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * User
 */
class User implements AdvancedUserInterface {
    
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    private $username;
    
    /**
     *
     * @var string 
     */
    private $password;
    
    /**
     *
     * @var string
     * @internal must be set to null if we use bcrypt 
     */
    private $salt = null;
    
    /**
     *
     * @var boolean
     */
    private $locked = true;
    
    /**
     *
     * @var boolean
     */
    private $enabled = true;
    
    /**
     *
     * @var Collection 
     */
    private $groupCenters;
    
    public function __construct()
    {
        $this->groupCenters = new ArrayCollection();
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
    
    public function __toString() {
        return $this->getUsername();
    }

    public function eraseCredentials()
    {
        
    }

    /**
     * 
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
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
        $this->password =  $password;
        return $this;
    }

    function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * {@inheritdoc}
     * 
     * @return boolean
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * 
     */
    public function isAccountNonLocked()
    {
        return $this->locked;
    }

    /**
     * {@inheritdoc}
     * 
     * @return boolean
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    
    /**
     * 
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
    
    /**
     * 
     * @return GroupCenter[]
     */
    public function getGroupCenters()
    {
        return $this->groupCenters;
    }
    
    /**
     * 
     * @param \Chill\MainBundle\Entity\GroupCenter $groupCenter
     * @return \Chill\MainBundle\Entity\User
     */
    public function addGroupCenter(GroupCenter $groupCenter)
    {
        $this->groupCenters->add($groupCenter);
        return $this;
    }
    
    /**
     * 
     * @param \Chill\MainBundle\Entity\GroupCenter $groupCenter
     * @throws \RuntimeException if the groupCenter is not in the collection
     */
    public function removeGroupCenter(GroupCenter $groupCenter)
    {
        if ($this->groupCenters->removeElement($groupCenter) === FALSE) {
            throw new \RuntimeException(sprintf("The groupCenter could not be removed, "
                    . "it seems not to be associated with the user. Aborting."));
        }
    }
    
    /**
     * This function check that groupCenter are present only once. The validator 
     * use this function to avoid a user to be associated to the same groupCenter
     * more than once.
     */
    public function isGroupCenterPresentOnce(ExecutionContextInterface $context)
    {
        $groupCentersIds = array();
        foreach ($this->getGroupCenters() as $groupCenter) {
            if (in_array($groupCenter->getId(), $groupCentersIds)) {
                $context->buildViolation("The user has already those permissions")
                        ->addViolation();
                
            } else {
                $groupCentersIds[] = $groupCenter->getId();
            }
        }
    }

}
