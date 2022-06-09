<?php

namespace App\Form;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CompanyType extends AbstractType
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
        $builder->add('name',TextType::class,array('label' => false, "required"=>true))
                ->add('ifu',TextType::class,array('label' => false,  "required"=>true))
                ->add('rccm',TextType::class,array('label' => false,  "required"=>true))
                ->add('email',EmailType::class,array('label' => false ,"required"=>true))
                ->add('phone',TelType::class,array('label' => false, 'mapped' => true, "required"=>true));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
            'allow_extra_fields' => true
        ]);
    }
}
