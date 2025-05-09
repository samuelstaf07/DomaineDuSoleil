<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ChangeMailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('newEmail', EmailType::class, [
                'label' => 'Nouvelle adresse e-mail',
                'attr' =>[
                    'placeholder' => 'Entrez votre nouvelle adresse mail',
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'attr' =>[
                    'placeholder' => 'Entrez votre mot de passe actuel',
                ]
            ]);
    }
}