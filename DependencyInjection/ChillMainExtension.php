<?php

namespace Chill\MainBundle\DependencyInjection;

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
class ChillMainExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $container->setParameter('chill_main.installation_name',
            $config['installation_name']);

        $container->setParameter('chill_main.available_languages',
            $config['available_languages']);
        
        $container->setParameter('chill_main.routing.resources', 
            $config['routing']['resources']); 

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('services/logger.yml');
    }

    public function prepend(ContainerBuilder $container) 
    {
        $bundles = $container->getParameter('kernel.bundles');
        //add ChillMain to assetic-enabled bundles
        if (!isset($bundles['AsseticBundle'])) {
            throw new MissingBundleException('AsseticBundle');
        }

        $asseticConfig = $container->getExtensionConfig('assetic');
        $asseticConfig['bundles'][] = 'ChillMainBundle';
        $container->prependExtensionConfig('assetic', 
                array('bundles' => array('ChillMainBundle')));
        
        //add installation_name and date_format to globals
        $chillMainConfig = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $chillMainConfig);
        $twigConfig = array(
            'globals' => array(
                'installation' => array(
                    'name' => $config['installation_name']),
                'available_languages' => $config['available_languages']
            ),
            'form_themes' => array('ChillMainBundle:Form:fields.html.twig')
        );
        $container->prependExtensionConfig('twig', $twigConfig);
        
        //add DQL function to ORM (default entity_manager)
        $container->prependExtensionConfig('doctrine', array(
           'orm' => array(
              'dql' => array(
                 'string_functions' => array(
                    'unaccent' => 'Chill\MainBundle\Doctrine\DQL\Unaccent'
                 )
              )
           )
        ));
        
        //add current route to chill main
        $container->prependExtensionConfig('chill_main', array(
           'routing' => array(
              'resources' => array(
                 '@ChillMainBundle/Resources/config/routing.yml'
              )
              
           )
        ));
        
        //add a channel to log app events
        $container->prependExtensionConfig('monolog', array(
            'channels' => array('chill')
        ));
    }
}
