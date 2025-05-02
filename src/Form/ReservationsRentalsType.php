<?php

namespace App\Form;

use App\Entity\ReservationsRentals;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationsRentalsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_start', DateType::class, [
                'required' => true,
            ])
            ->add('date_end', DateType::class, [
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter votre réservation au panier',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationsRentals::class,
        ]);
    }
}
