<?php

namespace App\Form;

use App\Data\SearchData;
use App\Repository\UserRepository;
use App\Repository\LevelRunRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class SearchForm extends AbstractType{
    
    private UserRepository $userRepository;
    private LevelRunRepository $levelRunRepository;

    public function __construct(UserRepository $userRepository, LevelRunRepository $levelRunRepository)
    {
        $this->userRepository = $userRepository;
        $this->levelRunRepository = $levelRunRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $departements = $this->userRepository->findDistinctDepartements();
        $levels = $this->levelRunRepository->findAllLevels();
        $levelValues = array_map(fn($item) => $item['level'], $levels);


        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher'
                ]
                ])
            ->add('departements', ChoiceType::class, [
                'label' => 'Départements',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => array_combine($departements, $departements)
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
            'data_class' => SearchData::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {

        return '';
    }
}