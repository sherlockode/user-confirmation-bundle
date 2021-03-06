<?php

namespace Sherlockode\UserConfirmationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder('sherlockode_user_confirmation');
        // BC layer for symfony/config < 4.2
        $root = \method_exists($tb, 'getRootNode') ? $tb->getRootNode() : $tb->root('sherlockode_user_confirmation');
        $root
            ->children()
                ->scalarNode('from_email')
                    ->isRequired()
                ->end()
                ->scalarNode('redirect_after_confirmation')
                    ->isRequired()
                ->end()
                ->scalarNode('email_subject')
                    ->defaultValue('confirmation.email.subject')
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('confirmation_form')
                            ->defaultValue('@SherlockodeUserConfirmation/Form/confirmation.html.twig')
                        ->end()
                        ->scalarNode('confirmation_email')
                            ->defaultValue('@SherlockodeUserConfirmation/Email/confirmation.html.twig')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $tb;
    }
}
