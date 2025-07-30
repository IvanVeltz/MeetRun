<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class ChangePasswordForm extends AbstractType
{

    public function getBlockPrefix(): string
    {
        return 'registration_form'; // Tous les champs auront ce préfixe
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldPlainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Votre ancien mot de passe',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'required' => true,
                'first_options'  => [
                    'label' => 'Votre nouveau mot de passe',
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez saisir un mot de passe.',
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[A-Z])(?=.*\d)(?=.*\W).{12,}$/',
                            'message' => 'Le mot de passe doit contenir au moins 12 caractères, une majuscule, un chiffre et un caractère spécial.',
                        ])                        
                    ]
                ],
                'second_options' => [
                    'label' => 'Répéter votre nouveau mot de passe',
                    'attr' => [
                        'class' => 'form-control',
                    ]
                ]
            ])

            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
                'action_name' => 'signup'
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
