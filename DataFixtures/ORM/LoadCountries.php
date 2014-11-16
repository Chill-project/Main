<?php

namespace Chill\MainBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Chill\MainBundle\Command\LoadCountriesCommand;

/**
 * Load countries into database
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class LoadCountries extends AbstractFixture implements ContainerAwareInterface {
    
    /**
     * 
     * @var ContainerInterface
     */
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function getOrder() {
        return 1001;
    }
    
    public function load(ObjectManager $manager) {
        
        echo "loading countries... \n";
        
        $languages = $this->container->getParameter('chill_main.available_languages');
        
        foreach (LoadCountriesCommand::prepareCountryList($languages) as $country){
            $manager->persist($country);
        }
        
        $manager->flush();
    }
    

}
