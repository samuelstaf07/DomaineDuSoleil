<?php

namespace App\Form;

use App\Entity\bills;
use App\Entity\events;
use App\Entity\ReservationsEvents;
use App\Entity\users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationsEventsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_reservation', null, [
                'widget' => 'single_text',
            ])
            ->add('date_start', null, [
                'widget' => 'single_text',
            ])
            ->add('is_active')
            ->add('bill_id', EntityType::class, [
                'class' => bills::class,
                'choice_label' => 'id',
            ])
            ->add('user_id', EntityType::class, [
                'class' => users::class,
                'choice_label' => 'id',
            ])
            ->add('event_id', EntityType::class, [
                'class' => events::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationsEvents::class,
        ]);
    }
}
