<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class ChangePasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldPlainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Votre ancien mot de passe',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('newPlainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passes doivent correpsondre',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => [
                    'label' => 'Votre nouveau mot de passe',
                    "attr" => [
                    'class' => 'form-control',
                    ]
                ],
                'second_options' => [
                    'label' => 'Répéter votre nouveau mot de passe',
                    "attr" => [
                    'class' => 'form-control',
                    ]
                ],
                "attr" => [
                    'class' => 'form-control',
                ]
            ])
            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
                'action_name' => 'signeup'
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
