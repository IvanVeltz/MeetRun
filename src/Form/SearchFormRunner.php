<?php

namespace App\Form;

use App\Data\SearchDataRunner;
use App\Repository\UserRepository;
use App\Repository\LevelRunRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class SearchFormRunner extends AbstractType{
    
    private UserRepository $userRepository;
    private LevelRunRepository $levelRunRepository;

    public function __construct(UserRepository $userRepository, LevelRunRepository $levelRunRepository)
    {
        $this->userRepository = $userRepository;
        $this->levelRunRepository = $levelRunRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
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

        $levels = $this->levelRunRepository->findAllLevels();
        $levelValues = array_map(fn($item) => $item['level'], $levels);


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
            ->add('levels', ChoiceType::class, [
                'label' => 'Niveau',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => array_combine($levelValues, $levelValues)
            ])
            ->add('ageMin', IntegerType::class, [
                'required' => false,
                'label' => 'Âge minimum',
                'attr' => ['min' => 12, 'max' => 100],
            ])
            ->add('ageMax', IntegerType::class, [
                'required' => false,
                'label' => 'Âge maximum',
                'attr' => ['min' => 12, 'max' => 100],
            ])
            
            ->add('sexe', ChoiceType::class, [
                'required' => false,
                'label' => 'Sexe',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'Homme' => 'homme',
                    'Femme' => 'femme'
                ]
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults([
            'data_class' => SearchDataRunner::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {

        return '';
    }
}