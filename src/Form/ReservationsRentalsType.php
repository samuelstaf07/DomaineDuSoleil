<?php

namespace App\Form;

use App\Entity\bills;
use App\Entity\rentals;
use App\Entity\ReservationsRentals;
use App\Entity\users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationsRentalsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('has_cleaning_deposit')
            ->add('total_deposit_returned')
            ->add('status_base_deposit')
            ->add('date_reservation', null, [
                'widget' => 'single_text',
            ])
            ->add('date_start', null, [
                'widget' => 'single_text',
            ])
            ->add('date_end', null, [
                'widget' => 'single_text',
            ])
            ->add('status_reservation')
            ->add('bill_id', EntityType::class, [
                'class' => bills::class,
                'choice_label' => 'id',
            ])
            ->add('user_id', EntityType::class, [
                'class' => users::class,
                'choice_label' => 'id',
            ])
            ->add('rental_id', EntityType::class, [
                'class' => rentals::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationsRentals::class,
        ]);
    }
}
