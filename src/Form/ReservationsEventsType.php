<?php

namespace App\Form;

use App\Entity\bills;
use App\Entity\events;
use App\Entity\ReservationsEvents;
use App\Entity\users;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ReservationsEventsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nb_places', IntegerType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer le nombre de places.',
                    ]),
                    new Positive([
                        'message' => 'Le nombre de places doit être positif.',
                    ]),
                    new LessThanOrEqual([
                        'value' => 20,
                        'message' => 'Le nombre de places ne peut pas dépasser 20.',
                    ]),
                ],
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
