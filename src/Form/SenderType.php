<?php

namespace App\Form;

use App\Entity\Sender;
use App\Entity\Status;
use App\Entity\User;

use App\Service\Services;

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
    public function __construct(UserRepository $userRepo, Services $src)
    {
        $this->userRepo = $userRepo;
        $this->userType = $src->checkThisUser($src->checkPermission("SEND2"))[0];
        $this->min = '2'; $this->max = '4';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //dd($options);
        $builder
            ->add('manager', EntityType::class, [
                'class'=>User::class,
                'choices'=>$this->userRepo->getUsersByPermission("SEND1", null, null, 1),
                'choice_label'=>'email',
                'choice_value'=>'uid',
                'required'=>true,
            ])
            ->add('uid', HiddenType::class, [
                'required'=>false,
            ])
            ->add('name', TextType::class, [
                'required'=>true,
            ])
            ->add('observation', TextareaType::class, [
                'required'=>false,
            ])
            ->add('status', EntityType::class, [
                'class'=>Status::class,
                'query_builder'=>function(EntityRepository $er){
                    switch ($this->userType) {
                        case 0: $this->max = '5'; break;
                        case 1: $this->max = '5'; break;
                        case 2: $this->max = '5'; break;
                        default: break;
                    }
                    return $er->createQueryBuilder('s')
                        ->where('s.code >= '.$this->min)
                        ->andwhere('s.code <= '.$this->max)
                        ->orderBy('s.code', 'ASC');
                },
                'choice_label'=>'name',
                'choice_value'=>'uid',
                'required'=>true,
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
