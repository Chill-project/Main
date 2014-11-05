<?php                                                                                                                                                                                                              

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {   
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Chill\MainBundle\ChillMainBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
        );
    }   

    public function registerContainerConfiguration(LoaderInterface $loader)
    {   
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }   

    /** 
     * @return string
     */
    public function getCacheDir()
    {   
        return sys_get_temp_dir().'/AcmeHelloBundle/cache';
    }   

    /** 
     * @return string
     */
    public function getLogDir()
    {   
        return sys_get_temp_dir().'/AcmeHelloBundle/logs';
    }   
}
