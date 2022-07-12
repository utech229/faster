<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\Router;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\sMailer;
use App\Service\Services;
use App\Service\AddEntity;
use App\Service\BrickPhone;
use App\Form\PasswordFormType;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier, UserRepository $userRepository, StatusRepository $statusRepository, 
    uBrand $brand, TranslatorInterface $intl, BaseUrl $baseUrl, Services $services, BrickPhone $brickPhone, AddEntity $addEntity,
    sMailer $sMailer, UrlGeneratorInterface $urlGenerator )
    {
        $this->emailVerifier     = $emailVerifier;
        $this->statusRepository  = $statusRepository;
        $this->userRepository    =  $userRepository;
        $this->comptes = [
			['Owner' =>'','Operator'=>'','Phone'=>'','TransactionId'=>'','Country'=>'', 'Status'=>''],
			['Banque'=>'','Country'=>'','NAccount'=>'','Swift'=>'','DocID'=>'','DocRIB'=>''],
			['Owner' =>'','NBIN'=>'','CVV2'=>'','NAccount'=>'']
		];
        $this->brand = $brand;
        $this->intl  = $intl;
        $this->baseUrl = $baseUrl->init();
        $this->services = $services;
        $this->sMailer = $sMailer;
        $this->addEntity = $addEntity;
        $this->urlGenerator = $urlGenerator;
        $this->brickPhone = $brickPhone;
    }

    #[Route('/{_locale}/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        $brand = $this->brand->get();
        
        if ($form->isSubmitted() && $form->isValid()) {
            //emil verify 
            $email = $form->get('email')->getData();
            $isExistedUser = $this->userRepository->findOneBy(['email' => $email, 'brand' => $this->brand->get()['brand']]);
            if ($isExistedUser) {
                return $this->services->msg_error(
                    $this->intl->trans("Ajout d'un nouvel utilisateur"),
                    $this->intl->trans("Cette adresse email appartient à un compte existant, veuillez le changer"),
                );
            }

            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            $countryCode   = strtoupper($request->request->get('country'));
            $phone         = $request->request->get('full_number');
            $countryDatas  = $this->brickPhone->getInfosCountryFromCode($countryCode);
            if ($countryDatas) {
                $countryDatas  = [
                    'dial_code' => $countryDatas['dial_code'],
                    'code'      => $countryCode,
                    'name'      => $countryDatas['name']
                ];

                $priceDatas = [
                    'dial_code' => $countryDatas['dial_code'],
                    'code'      => $countryCode,
                    'name'      => $countryDatas['name'],
                    'price'     => $countryCode == 'BJ' ? 12 : 25
                ];
            }else
            return $this->services->msg_error(
                $this->intl->trans("Insertion du tableau de données pays"),
                $this->intl->trans("La recherche du nom du pays à échoué : BrickPhone"),
            );

        
            // A commenter revoir lorsque l'envoi des mail est activé
            $user->setCreatedAt(new \DatetimeImmutable());
            $user->setBalance(0);
            $user->setPaymentAccount($this->comptes);
            $user->setApikey(bin2hex(random_bytes(32)));
            $user->setUid(time().uniqid());
            $user->setPostPay(0);
            $user->setIsDlr(0);
            $user->setPhone($phone);
            $user->setCountry($countryDatas);
            $user->setPrice([
                $countryDatas['code'] => $priceDatas,
            ]);
            $user->setRole($entityManager->getRepository(Role::Class)->findOneById(2));
            $user->setProfilePhoto("default_avatar_1.png");
            $user->setRoles(['ROLE_USER']);
            $user->setRouter($entityManager->getRepository(Router::Class)->findOneById(1));
            $user->setBrand($this->brand->get()['brand']);
            $user->setStatus($this->services->status(2));
            $this->userRepository->add($user);

            $settingData = [
                'ccode' => $request->request->get('currency'),
                'cname' => $request->request->get('currency_name'),
                'ufirstname' => null,
                'ulastname'  => null,
            ];
            $setDefaultSetting = $this->addEntity->defaultUsetting($user, $settingData);

            //code
            $code = $this->services->idgenerate(10);
            $user->setActiveCode($code);
            // Lien d'activation'
            $url = $this->baseUrl.$this->urlGenerator->generate('app_account_activation', ["uid" => $user->getUid(), 'code' => $code]);
            //email
            $message = $this->render('email/invitation.html.twig', [
                'title'           => $this->intl->trans('Activation de compte').' - '. $brand['name'],
                'brand'           => $brand,
                'data'            => [
                    'url'      => $url,
                    'user'     => $user,
                    'password' => $form->get('plainPassword')->getData(),
                    'base_url' => $this->baseUrl
                ]
            ]);
            $this->sMailer->nativeSend( $this->brand->get()['emails']['support'], 
                $email ,  $this->intl->trans('Activation de compte'),  $message);

            return $this->services->msg_success(
                $this->intl->trans("Création d'un nouvel utilisateur"),
                $this->intl->trans("Votre compte à été crée avec succès, veuillez consulter votre boîte email pour valider votre compte. Merci")
            );
        }   
        return $this->render('registration/'.$this->brand->get()['regisform'], [
            'title'           => $this->intl->trans('Inscription').' - '. $brand['name'],
            'menu_text'       => $this->intl->trans('Inscription'),
            'brand'           => $brand,
            'baseUrl'         => $this->baseUrl,
            'registrationForm' => $form->createView(),
        ]);
    }

  
    #[Route('/{_locale}/reset/password', name: 'app_init_reset')]
    public function app_password_reset(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        $user = new User();
        $form = $this->createForm(PasswordFormType::class, $user);
        $form->handleRequest($request);

        $brand = $this->brand->get();

        if ($request->request->count() > 0)
        {
            if ($form->isSubmitted() && $form->isValid()) {
                //emil verify 
                $email = $form->get('email')->getData();
                $user = $this->userRepository->findOneBy(['email' => $email, 'brand' => $this->brand->get()['brand']]);
                if ($user) {
                    $code = $this->services->idgenerate(10);
                    $user->setActiveCode($code);
                    $user->setUpdatedAt(new \DatetimeImmutable());
                    $this->userRepository->add($user);
                    // Lien de réinitialisation
                    $base = $this->baseUrl;
                    $url = $base.$this->urlGenerator->generate('app_password_resetting', ["uid" => $user->getUid(), 'code' => $code]);
                    //email
                    $message = $this->render('email/password-reset.html.twig', [
                        'title'           => $this->intl->trans('Récupération de compte').' - '. $brand['name'],
                        'brand'           => $brand,
                        'data'            => [
                            'url'      => $url,
                            'user'     => $user,
                            'base_url' => $this->baseUrl
                        ]
                    ]);
                    $this->sMailer->nativeSend( $this->brand->get()['emails']['support'], 
                        $email ,  $this->intl->trans('Réinialisation de mot de passe'),  $message);

                    $message = $this->intl->trans("Veuillez vérifier votre boite de reception email pour réinitialiser votre nouveau mot de passe");
                    $this->addFlash('info', $message);
                    return $this->services->msg_success(
                        $this->intl->trans("Récupération de mot de passe"),
                        $message,
                        $url,
                    );
                }else {
                    return $this->services->msg_warning(
                        $this->intl->trans("Récupération de mot de passe"),
                        $this->intl->trans("Cette adresse email n'appartient à aucun compte existant"),
                    );
                }

                return $this->services->msg_success(
                    $this->intl->trans("Création d'un nouvel utilisateur"),
                    $this->intl->trans("Votre compte à été crée avec succès, veuillez consulter votre boîte email pour valider votre compte. Merci")
                );

            }
        }
        

        return $this->render('registration/'.$this->brand->get()['regisform'], [
            'title'           => $this->intl->trans('Récupération de compte').' - '. $brand['name'],
            'menu_text'       => $this->intl->trans('Récupération de compte'),
            'brand'           => $brand,
            'baseUrl'         => $this->baseUrl,
            'form' => $form->createView(),
        ]);
    }
}
