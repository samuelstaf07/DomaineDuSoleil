<?php

namespace App\Form;

use App\Entity\Rentals;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RentalsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le titre ne peut pas être vide.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 100,
                        'minMessage' => 'Le titre doit faire au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le contenu ne peut pas être vide.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 5000,
                        'minMessage' => 'Le contenu doit faire au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le contenu ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('price_per_day', MoneyType::class, [
                'label' => 'Prix par jour',
                'currency' => 'EUR',
                'constraints' => [
                    new Assert\NotNull(['message' => 'Le prix par jour est requis.']),
                    new Assert\Positive(['message' => 'Le prix doit être supérieur à 0.']),
                ],
            ])
            ->add('nb_double_bed', IntegerType::class, [
                'label' => 'Nombre lit double',
                'constraints' => [
                    new Assert\NotNull(['message' => 'Le nombre de lits doubles est requis.']),
                    new Assert\PositiveOrZero(['message' => 'Le nombre de lits doubles ne peut pas être négatif.']),
                ],
            ])
            ->add('nb_simple_bed', IntegerType::class, [
                'label' => 'Nombre lit simple',
                'constraints' => [
                    new Assert\NotNull(['message' => 'Le nombre de lits simples est requis.']),
                    new Assert\PositiveOrZero(['message' => 'Le nombre de lits simples ne peut pas être négatif.']),
                ],
            ])
            ->add('has_shower', CheckboxType::class, [
                'label' => 'Possède une douche',
                'required' => false,
            ])
            ->add('has_toilet', CheckboxType::class, [
                'label' => 'Possède des toilettes',
                'required' => false,
            ])
            ->add('has_kitchen', CheckboxType::class, [
                'label' => 'Possède une cuisine',
                'required' => false,
            ])
            ->add('has_fridge', CheckboxType::class, [
                'label' => 'Possède un frigo',
                'required' => false,
            ])
            ->add('has_heating', CheckboxType::class, [
                'label' => 'Possède un chauffage',
                'required' => false,
            ])
            ->add('pets_accepted', CheckboxType::class, [
                'label' => 'Animaux acceptés',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rentals::class,
        ]);
    }
}
