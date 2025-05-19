<?php

namespace App\Form;

use App\Entity\ReservationsRentals;
use App\Entity\Users;
use App\Entity\Rentals;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationsRentalsFullType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $step = $options['step'] ?? 1;

        if ($step == 1) {
            $builder
                ->add('user', EntityType::class, [
                    'class' => Users::class,
                    'choice_label' => 'email',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->where('u.is_active = true')
                            ->orderBy('u.email', 'ASC');
                    },
                ])
                ->add('rentals', EntityType::class, [
                    'class' => Rentals::class,
                    'choice_label' => 'title',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->where('r.is_active = true')
                            ->orderBy('r.title', 'ASC');
                    },
                ]);
        }

        if ($step == 2) {
            $builder
                ->add('date_start', DateType::class, [
                    'widget' => 'single_text',
                ])
                ->add('date_end', DateType::class, [
                    'widget' => 'single_text',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationsRentals::class,
            'step' => 1,
        ]);
    }
}

