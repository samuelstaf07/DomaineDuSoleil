<?php

namespace App\Form;

use App\Entity\images;
use App\Entity\PostImages;
use App\Entity\posts;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostImagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('post_id', EntityType::class, [
                'class' => posts::class,
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
            'data_class' => PostImages::class,
        ]);
    }
}
