<?php

namespace App\Form;

use App\Entity\Images;
use App\Entity\Posts;
use App\Entity\Rentals;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('src')
            ->add('alt')
            ->add('posts', EntityType::class, [
                'class' => Posts::class,
                'choice_label' => 'id',
            ])
            ->add('rentals', EntityType::class, [
                'class' => Rentals::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Images::class,
        ]);
    }
}
