<?php

namespace App\Form;

use App\Entity\Sender;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SenderType extends AbstractType
{
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //dd($options);
        $builder
            ->add('manager', EntityType::class, [
                'class'=>User::class,
                'choices'=>$this->userRepo->getUsersByPermission("SEND1", null),
                'choice_label'=>'email',
                'choice_value'=>'uid',
            ])
            ->add('uid', HiddenType::class)
            ->add('name', TextType::class, [
                'attr'=>[
                    'required' => true
                ]
            ])
            ->add('observation', TextareaType::class)
            ->add('status', EntityType::class, [
                'class'=>Status::class,
                'query_builder'=>function(EntityRepository $er){
                        return $er->createQueryBuilder('s')
                            ->where('s.code >= 2')
                            ->andwhere('s.code <= 6')
                            ->orderBy('s.code', 'ASC');
                },
                'choice_label'=>'name',
                'choice_value'=>'uid',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sender::class,
        ]);
    }
}
