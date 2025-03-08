<?php

namespace App\Form;

use App\Entity\Comments;
use App\Entity\rentals;
use App\Entity\users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content')
            ->add('created_at', null, [
                'widget' => 'single_text',
            ])
            ->add('rating')
            ->add('is_active')
            ->add('rentals', EntityType::class, [
                'class' => Rentals::class,
                'choice_label' => 'id',
                'placeholder' => 'Sélectionnez une location',
            ])
            ->add('user', EntityType::class, [
                'class' => Users::class,
                'choice_label' => 'email',
                'placeholder' => 'Sélectionnez un utilisateur',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comments::class,
        ]);
    }
}
