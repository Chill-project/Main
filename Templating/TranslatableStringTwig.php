<?php
namespace Chill\MainBundle\Templating;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class TranslatableStringTwig extends \Twig_Extension
{
    use ContainerAwareTrait;

    /*
     * (non-PHPdoc)
     * @see Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('localize_translatable_string', array($this, 'localize'))
        );
    }
    
    public function localize(array $translatableStrings)
    {
        return $this->container->get('chill.main.helper.translatable_string')
            ->localize($translatableStrings);
    }
    
    public function getName()
    {
        return 'chill_main_localize';
    }

}