<?php

namespace App\Form;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class AffiliateType extends AbstractType
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
            ->add('firstName',TextType::class,array('label' => false, "required"=>true))
            ->add('lastName',TextType::class,array('label' => false,  "required"=>true))
            ->add('email',EmailType::class,array('label' => false ,"required"=>true))
            ->add('phone',TelType::class,array('label' => false, 'mapped' => true, "required"=>true))
            ->add('status', ChoiceType::class, ['label' => false,
                'choices'  => [
                    $this->intl->trans("Actif")      => '1',
                    $this->intl->trans("En attente") => '0',
                    $this->intl->trans("Désactivé")  => '2',
                    $this->intl->trans("Suspendu")   => '3',
                    $this->intl->trans("Suprimé")    => '4',
                ]
            ])
            ->add('gender', ChoiceType::class, ['label' => false,
            'choices'  => [
                $this->intl->trans("Homme") => 'M',
                $this->intl->trans("Femme") => 'F',
            ]
        ])
            ->add('uid',HiddenType::class,  array('mapped' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'allow_extra_fields' => true
        ]);
    }
}
