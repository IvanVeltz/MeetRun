<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class NewEventForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label'=>'Nom de la course'
            ])
            ->add('dateEvent', DateType::class, [
                'label'=> 'Date de la course',
                'widget' => 'single_text'
            ])
            ->add('description', TextareaType::class, [
                'label'=>'Descritption de la course'
            ])
            ->add('distance', IntegerType::class, [
                'label'=>'Distance de la course (en km)'
            ])
            
            ->add('capacity', IntegerType::class, [
                'label'=>'Nombre de particpant maximum'
            ])
            ->add('adress', TextType::class, [
                'label' => 'Lieu de départ'
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code Postal'
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville'
            ])
            ->add('photos', FileType::class, [
                'label' => 'Photos de la course',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'accept' => 'image/jpeg,image/png'
                ]
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
