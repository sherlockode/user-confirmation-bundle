<?php

namespace Sherlockode\UserConfirmationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * SherlockodeUserConfirmationExtension.
 */
class SherlockodeUserConfirmationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sherlockode_user_confirmation.from_email', $config['from_email']);
        $container->setParameter(
            'sherlockode_user_confirmation.redirect_after_confirmation',
            $config['redirect_after_confirmation']
        );
        $container->setParameter(
            'sherlockode_user_confirmation.templates.confirmation_form',
            $config['templates']['confirmation_form']
        );
        $container->setParameter(
            'sherlockode_user_confirmation.templates.confirmation_email',
            $config['templates']['confirmation_email']
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
