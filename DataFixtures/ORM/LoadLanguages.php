<?php

namespace Chill\MainBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Intl\Intl;
use Chill\MainBundle\Entity\Language;

/**
 * Load languages into database
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class LoadLanguages extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    // The regional version of language are language with _ in the code
    // This array contains regional code to not exclude
    private $regionalVersionToInclude = ["ro_MD"];

    // Array of ancien languages (to exclude)
    private $ancientToExclude = ["ang", "egy", "fro", "goh", "grc", "la", "non", "peo", "pro", "sga",
        "dum", "enm", "frm", "gmh", "mga", "akk", "phn", "zxx", "got", "und"];
    
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
        return 10;
    }
    
    public function load(ObjectManager $manager) {
        
        echo "loading languages... \n";
        
        foreach (Intl::getLanguageBundle()->getLanguageNames() as $code => $language) {
            if (
                    !in_array($code, $this->regionalVersionToInclude)
                    &&
                    !in_array($code, $this->ancientToExclude)
                ) {

                $lang = (new Language())
                        ->setId($code)
                        ->setName($this->prepareName($code))
                        ;
                $manager->persist($lang);
            }
        }
        
        $manager->flush();
    }
    
    /**
     * prepare names for languages
     * 
     * @param string $languageCode
     * @return string[] languages name indexed by available language code
     */
    private function prepareName($languageCode) {
        foreach ($this->container->getParameter('chill_main.available_languages') as $lang) {
            $names[$lang] = Intl::getLanguageBundle()->getLanguageName($languageCode);
        }
        
        return $names;
    }
    

}
