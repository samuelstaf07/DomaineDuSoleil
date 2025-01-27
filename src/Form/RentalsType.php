<?php

namespace App\Form;

use App\Entity\Rentals;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RentalsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nb_double_bed')
            ->add('nb_simple_bed')
            ->add('has_shower')
            ->add('has_toilet')
            ->add('has_kitchen')
            ->add('has_fridge')
            ->add('has_heating')
            ->add('pets_accepted')
            ->add('price_per_day')
            ->add('is_on_promotion')
            ->add('is_active')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rentals::class,
        ]);
    }
}
