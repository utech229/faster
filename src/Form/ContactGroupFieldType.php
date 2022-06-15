<?php

namespace App\Form;

use App\Entity\ContactGroupField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactGroupFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uid')
            ->add('field1')
            ->add('field2')
            ->add('field3')
            ->add('field4')
            ->add('field5')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('contactGroup')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactGroupField::class,
        ]);
    }
}
