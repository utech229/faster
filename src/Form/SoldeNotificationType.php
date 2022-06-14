<?php

namespace App\Form;

use App\Entity\SoldeNotification;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SoldeNotificationType extends AbstractType
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
        $builder->add('minSolde',TextType::class,array('label' => false, "required"=>true))
                ->add('email1',TextType::class,array('label' => false))
                ->add('email2',TextType::class,array('label' => false))
                ->add('email3',EmailType::class,array('label' => false));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SoldeNotification::class,
            'allow_extra_fields' => true
        ]);
    }
}
