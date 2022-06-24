<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationFormType extends AbstractType
{
    private $security;
    private $trans;

    public function __construct(Security $security, TranslatorInterface $trans)
    {
        $this->security = $security;
        $this->intl = $trans;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('phone', TelType::class, [
                "attr"=>["class"=>"form-control input-radius tel", "placeholder"=>"Téléphone", "required"=>true], "label"=>false
            ])
            ->add('email', EmailType::class,[
                "attr"=>["class"=>"form-control input-radius", "placeholder"=>"Email", "required"=>true
            ], "label"=>false
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'label' => false,
                'mapped' => false,
                'attr' => ["class"=>"form-control input-radius", "placeholder"=>"Confirmer Mot de passe",'autocomplete' => 'new-password', "required"=>true],
                'constraints' => [
                    new NotBlank([
                        'message' => $this->intl->trans('Veuillez saisir un mot de passe'),
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => $this->intl->trans('Your password should be at least {{ limit }} characters'),
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('confirm_password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'label' => false,
                'mapped' => false,
                'attr' => ["class"=>"form-control input-radius", "placeholder"=>"Confirmer Mot de passe",'autocomplete' => 'new-password', "required"=>true],
                'constraints' => [
                    new NotBlank([
                        'message' => $this->intl->trans('Veuillez saisir un mot de passe'),
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => $this->intl->trans('Your password should be at least {{ limit }} characters'),
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
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
