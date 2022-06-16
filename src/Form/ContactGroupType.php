<?php

namespace App\Form;

use App\Entity\ContactGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uid')
            ->add('name')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('manager')
            ->add('contactGroupField')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactGroup::class,
        ]);
    }
}
