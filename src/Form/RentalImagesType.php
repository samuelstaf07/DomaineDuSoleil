<?php

namespace App\Form;

use App\Entity\images;
use App\Entity\RentalImages;
use App\Entity\rentals;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RentalImagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rental_id', EntityType::class, [
                'class' => rentals::class,
                'choice_label' => 'id',
            ])
            ->add('image_id', EntityType::class, [
                'class' => images::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RentalImages::class,
        ]);
    }
}
