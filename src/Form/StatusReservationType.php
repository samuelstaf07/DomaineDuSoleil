<?php

namespace App\Form;

use App\Entity\ReservationsRentals;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatusReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status_base_deposit', ChoiceType::class, [
                'label' => 'Statut de la caution',
                'choices' => [
                    'En cours' => 0,
                    'Partiellement remboursé' => 1,
                    'Totalement remboursé' => 2,
                    'Refusé' => 3,
                    'Annulé' => 4,
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationsRentals::class,
        ]);
    }
}