<?php

namespace App\Form;

use App\Entity\ReservationsEvents;
use App\Entity\Users;
use App\Entity\Events;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationsEventsFullType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $step = $options['step'] ?? 1;

        if($step == 1){
            $builder
                ->add('user', EntityType::class, [
                    'class' => Users::class,
                    'choice_label' => 'email',
                    'label' => 'Utilisateur',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->where('u.is_active = true')
                            ->orderBy('u.email', 'ASC');
                    },
                ])
                ->add('event', EntityType::class, [
                    'class' => Events::class,
                    'choice_label' => function ($event) {
                        return sprintf('(%s): %s', $event->getDate()->format('d/m/Y'), $event->getTitle());
                    },
                    'label' => 'Événement',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('e')
                            ->where('e.is_active = true')
                            ->orderBy('e.date', 'DESC');
                    },
                ]);
        }

        if ($step == 2) {
            $builder
                ->add('nb_places', IntegerType::class, [
                    'label' => 'Nombre de places',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationsEvents::class,
            'step' => 1,
        ]);
    }
}