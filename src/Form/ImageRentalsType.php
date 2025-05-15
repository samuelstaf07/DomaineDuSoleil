<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\UX\Dropzone\Form\DropzoneType;

class ImageRentalsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', DropzoneType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/*',
                    'placeholder' => 'Déposer la nouvelle image du logement',
                    'class' => 'my-dropzone',
                    'data-controller' => 'symfony--ux-dropzone--dropzone',
                ],
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Seules les images sont autorisées.',
                    ]),
                ],
            ])
        ;
    }
}
