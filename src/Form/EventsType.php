<?php

namespace App\Form;

use App\Entity\Events;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EventsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextareaType::class, [
                'label' => 'Titre',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le titre ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'minMessage' => 'Le titre doit faire au moins {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La description ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'La description doit faire au moins {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('price', MoneyType::class, [
                'currency' => 'EUR',
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Le prix est requis.',
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Le prix ne peut pas être négatif.',
                    ]),
                ],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'La date est requise.',
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date ne peut pas être dans le passé.',
                    ]),
                ],
            ])
            ->add('nb_places', IntegerType::class, [
                'label' => 'Nombre de places',
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Le nombre de places est requis.',
                    ]),
                    new Assert\Positive([
                        'message' => 'Le nombre de places doit être supérieur à zéro.',
                    ]),
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le lieu est requis.',
                    ]),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Le lieu doit contenir au moins {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('is_active', CheckboxType::class, [
                'label'    => 'Actif',
                'required' => false,
            ])
            ->add('age_requirement', IntegerType::class, [
                'label' => 'Âge requis',
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'L’âge requis est obligatoire.',
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'L’âge requis doit être positif.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Events::class,
        ]);
    }
}
