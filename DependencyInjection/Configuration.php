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
        $tb = new TreeBuilder();
        $root = $tb->root('sherlockode_user_confirmation');
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
                    ->isRequired()
                    ->children()
                        ->scalarNode('confirmation_form')
                            ->defaultValue('SherlockodeUserConfirmationBundle:Form:confirmation.html.twig')
                        ->end()
                        ->scalarNode('confirmation_email')
                            ->defaultValue('SherlockodeUserConfirmationBundle:Email:confirmation.html.twig')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $tb;
    }
}
