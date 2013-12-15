<?php

namespace CL\Chill\MainBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CLChillMainExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container) {
        $bundles = $container->getParameter('kernel.bundles');
        
        //Configure FOSUSerBundle
        if (!isset($bundles['FOSUserBundle'])) {
            throw new MissingBundleException('FOSUserBundle');
        }
                
        $db_driver = array('db_driver' => 'orm'); 
        $container->prependExtensionConfig('fos_user', $db_driver);
        
        $user_class = array('user_class' => 'CL\Chill\MainBundle\Entity\Agent');
        $container->prependExtensionConfig('fos_user', $user_class);
        
        $registration_form = array('registration' => array( 
            'form' => array('type' => 'chill_user_registration')));
        $container->prependExtensionConfig('fos_user', $registration_form);
        
        
    }

}
