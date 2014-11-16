<?php
namespace Chill\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop
 *        
 */
class LoadCountriesCommand extends ContainerAwareCommand
{
    
    /*
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('chill:main:countries:populate')
            ->setDescription('Load or update countries in db. This command does not delete existing countries, '.
                'but will update names according to available languages');
    }
    
    /*
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $countries = static::prepareCountryList($this->getContainer()->getParameter('chill_main.available_languages'));
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        foreach($countries as $country) {
            $countryStored = $em->getRepository('ChillMainBundle:Country')
                ->findOneBy(array('countryCode' => $country->getCountryCode()));
            
            if (NULL === $countryStored) {
                $em->persist($country);
            } else {
                $countryStored->setName($country->getName());
            }
        }
        
        $em->flush();
    }

    public static function prepareCountryList($languages)
    {
        $regionBundle = Intl::getRegionBundle();
        
        foreach ($languages as $language) {
            $countries[$language] = $regionBundle->getCountryNames($language);
        }
        
        $countryEntities = array();
        
        foreach ($countries[$languages[0]] as $countryCode => $name) {
            $names = array();
            
            foreach ($languages as $language) {
                $names[$language] = $countries[$language][$countryCode];
            }
            
            $country = new \Chill\MainBundle\Entity\Country();
            $country->setName($names)->setCountryCode($countryCode);
            $countryEntities[] = $country;
        }
        
        return $countryEntities;
    }
}
