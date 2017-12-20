<?php

namespace Sherlockode\UserConfirmationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ConfirmPasswordType
 */
class ConfirmPasswordType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'password',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'confirmation.form.passwords_must_match',
                    'required' => true,
                    'first_options' => [
                        'attr' => ['placeholder' => 'confirmation.form.password']
                    ],
                    'second_options' => [
                        'attr' => ['placeholder' => 'confirmation.form.password_confirm']
                    ]
                ]
            )
            ->add('_confirmationToken', HiddenType::class)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('translation_domain', 'SherlockodeUserConfirmationBundle');
    }
}
