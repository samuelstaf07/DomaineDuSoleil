<?php

namespace App\Form;

use App\Entity\Bills;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;

class IsAdultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isAdult', CheckboxType::class, [
                'label' => 'Vous confirmez être majeur pour pouvoir passer commande.',
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez confirmer que vous êtes majeur pour passer commande.',
                    ]),
                ],
            ]);
        ;
    }
}
