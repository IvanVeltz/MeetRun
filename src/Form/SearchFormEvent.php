<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Event;
use App\Data\SearchDataEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class SearchFormEvent extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $departements = [
            '01', '02', '03', '04', '05', '06', '07', '08', '09',
            '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
            '21', '22', '23', '24', '25', '26', '27', '28', '29',
            '30', '31', '32', '33', '34', '35', '36', '37', '38', '39',
            '40', '41', '42', '43', '44', '45', '46', '47', '48', '49',
            '50', '51', '52', '53', '54', '55', '56', '57', '58', '59',
            '60', '61', '62', '63', '64', '65', '66', '67', '68', '69',
            '70', '71', '72', '73', '74', '75', '76', '77', '78', '79',
            '80', '81', '82', '83', '84', '85', '86', '87', '88', '89',
            '90', '91', '92', '93', '94', '95', '2A', '2B', '99'
        ];

        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher',
                    'id' => 'js-search-input'
                ]
                ])
            ->add('departements', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => array_combine($departements, $departements), // ['75' => '75', '13' => '13', ...]
                'attr' => ['style' => 'display:none'] // pour tout masquer
            ])
            ->add('distanceMin', IntegerType::class, [
                'required' => false,
                'label' => 'Distance minimum',
            ])
            ->add('distanceMax', IntegerType::class, [
                'required' => false,
                'label' => 'Distance maximum',
            ])
            ->add('reset', ResetType::class, [
                'label' => 'Réinitialiser',
                'attr' => ['class' => 'reset-btn', 'id' => 'reset']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchDataEvent::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {

        return '';
    }
}
