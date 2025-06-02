<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProfilForm extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security; // Stocke l'instance de Security
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $bioText = $this->security->getUser()->getBio() ? $this->security->getUser()->getBio() : "Parlez un peu de vous...";

        $builder
            ->add('dateOfBirth',DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('pictureProfilUrl', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false, // Ne lie pas directement le champ à l'entité (gestion manuelle)
                'required' => false, // Rend le champ facultatif
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG).',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code Postal',
                "attr" => [
                    'class' => 'form-control',
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                "attr" => [
                    'class' => 'form-control',
                ]
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Presentation',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5, // Définit la hauteur du champ
                    'placeholder' => $bioText,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
