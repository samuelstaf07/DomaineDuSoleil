<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\UX\Dropzone\Form\DropzoneType;

class ImageUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', DropzoneType::class, [
                'mapped' => false,
                'required' => false,
                'multiple' => false,
                'attr' => [
                    'accept' => 'image/*',
                    'placeholder' => 'Déposer votre nouvelle image de profil',
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
