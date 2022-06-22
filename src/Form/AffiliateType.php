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

class AffiliateType extends AbstractType
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
        $builder
            ->add('firstname',TextType::class,array('label' => false,'mapped' => false, "required"=>true))
            ->add('lastname',TextType::class,array('label' => false, 'mapped' => false, "required"=>true))
            ->add('email',EmailType::class,array('label' => false ,"required"=>true))
            ->add('phone',TelType::class,array('label' => false, 'mapped' => true, "required"=>true))
            ->add('uid',HiddenType::class,  array('mapped' => false))
            
            ->add('status', EntityType::class, [
                'label' => false,
                'class'=>Status::class,
                'query_builder'=>function(EntityRepository $er){
                        return $er->createQueryBuilder('s')
                            ->where('s.code >= 2')
                            ->andwhere('s.code <= 5')
                            ->orderBy('s.name', 'ASC');
                },
                'choice_label'=>'name',
                'choice_value'=>'uid',
            ])
            ->add('role', EntityType::class, [
                'label' => false,
                'class'=>Role::class,
                'query_builder'=>function(EntityRepository $er){
                        return $er->createQueryBuilder('r')
                            ->where('r.code == :text1')
                            ->andWhere('r.code == :text2')
                            ->andWhere('r.level < :level')
                            ->setParameter('text1', 'AFF0')
                            ->setParameter('text2', 'AFF1')
                            ->setParameter('level', $this->services->connectedUser()->getRole()->getLevel())
                            ->orderBy('r.level', 'ASC');
                },
                'choice_label'=>'name',
                'choice_value'=>'code',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'allow_extra_fields' => true
        ]);
    }
}
