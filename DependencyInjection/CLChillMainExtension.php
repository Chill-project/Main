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
        
        $container->setParameter('cl_chill_main.installation_name', 
                $config['installation_name']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container) 
    {
        $bundles = $container->getParameter('kernel.bundles');
        //add CLChillMain to assetic-enabled bundles
        if (!isset($bundles['AsseticBundle'])) {
            throw new MissingBundleException('AsseticBundle');
        }

        $asseticConfig = $container->getExtensionConfig('assetic');
        $asseticConfig['bundles'][] = 'CLChillMainBundle';
        $container->prependExtensionConfig('assetic', 
                array('bundles' => array('CLChillMainBundle')));
        
        //add installation_name to globals
        $chillMainConfig = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $chillMainConfig);
        $twigConfig = array(
            'globals' => array(
                'installation' => array(
                    'name' => $config['installation_name']
                )
            )
        );
        $container->prependExtensionConfig('twig', $twigConfig);
                
    }

}
