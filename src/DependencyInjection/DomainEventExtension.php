<?php

namespace GabrielDosAnjosBr\DomainEvents\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DomainEventExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config')
        );

        $loader->load('services.yaml');

        if (interface_exists('\Doctrine\ORM\EntityManagerInterface')) {
            $loader->load('orm.yaml');
        }

        if (class_exists('\Doctrine\ODM\MongoDB\DocumentManager')) {
            $loader->load('odm.yaml');
        }
    }
}