<?php

namespace App\Form;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Router;
use App\Entity\Status;
use App\Service\Services;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PriceType extends AbstractType
{

    private $security;
    private $trans;

    public function __construct(Security $security, TranslatorInterface $trans, Services $services)
    {
        $this->security = $security;
        $this->intl     = $trans;
        $this->services = $services;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('price',TextType::class,array('label' => false,'mapped' => false, "required"=>true));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'allow_extra_fields' => true
        ]);
    }
}
