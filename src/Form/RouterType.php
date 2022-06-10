<?php

namespace App\Form;

use App\Entity\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RouterType extends AbstractType
{
    private $security;
    private $trans;

    public function __construct(Security $security, TranslatorInterface $trans)
    {
        $this->security = $security;
        $this->intl     = $trans;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name',TextType::class,array('label' => false, "required"=>true));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Router::class,
            'allow_extra_fields' => true
        ]);
    }
}
