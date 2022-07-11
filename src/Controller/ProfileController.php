<?php

namespace App\Controller;

use App\Form\UserType;
use App\Entity\Company;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\sMailer;
use App\Form\CompanyType;
use App\Service\Services;
use App\Service\AddEntity;
use App\Service\BrickPhone;
use App\Form\PasswordFormType;
use App\Entity\SoldeNotification;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Form\SoldeNotificationType;
use App\Repository\StatusRepository;
use App\Repository\UsettingRepository;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AuthorizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('/{_locale}/home/profile')]
class ProfileController extends AbstractController
{
    private $em;
    
	public function __construct(BaseUrl $baseUrl, Services $services, EntityManagerInterface $entityManager, TranslatorInterface $translator,
    RoleRepository $roleRepository, UserRepository $userRepository, PermissionRepository $permissionRepository, sMailer $sMailer,
    AuthorizationRepository $authorizationRepository, UrlGeneratorInterface $urlGenerator, uBrand $brand, ValidatorInterface $validator,
    BrickPhone $brickPhone, AddEntity $addEntity , StatusRepository $statusRepository, UsettingRepository $usettingRepository){
		$this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->userRepository  = $userRepository;
        $this->usettingRepository  = $usettingRepository;
        $this->roleRepository           = $roleRepository;
        $this->authorizationRepository  = $authorizationRepository;
        $this->permissionRepository     = $permissionRepository;
        $this->statusRepository         = $statusRepository;
        $this->validator                = $validator;
        $this->brickPhone               = $brickPhone;
        $this->addEntity                = $addEntity;
        $this->sMailer                = $sMailer;
        

        $this->permission = [
            "PFIL0", "PFIL1",  "PFIL2", "PFIL3", "PFIL4"
        ];
		$this->pAccess  =	$this->services->checkPermission($this->permission[0]);
		$this->pCreate  =	$this->services->checkPermission($this->permission[1]);
		$this->pList    =	$this->services->checkPermission($this->permission[2]);
		$this->pEdit    =	$this->services->checkPermission($this->permission[3]);
		$this->pDelete  =	$this->services->checkPermission($this->permission[4]);
	}

    #[Route('', name: 'app_profile')]
    public function index(): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

        $company          = new Company;
        $notification     = new SoldeNotification;
        $companyform      = $this->createForm(CompanyType::class, $company);
        $notificationform = $this->createForm(SoldeNotificationType::class, $notification);
        $this->services->addLog($this->intl->trans('Accès au menu profils'));
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'title'           => $this->intl->trans('Mon profil').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans('Mon profil')],
            ],
            'brand'           => $this->brand->get(),
            'baseUrl'         => $this->baseUrl->init(),
            'pCreateUser'     => $this->pCreate,
            'pEditUser'       => $this->pEdit,
            'pDeleteUser'     => $this->pDelete,
            'companyform'      => $companyform->createView(),
            'notificationform' => $notificationform->createView(),
            'form'      => $companyform->createView(),
        ]);
    }

    #[Route('/setting', name: 'app_profile_setting',methods: ['POST','GET'])]
    #[Route('/edit', name: 'app_user_profile_edit', methods: ['POST'])]
    public function profile_setting(Request $request): JsonResponse
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

        $user     = $this->getUser();
        $usetting = $user->getUsetting();
        $cemailform = $this->createForm(PasswordFormType::class, $user);

        if ($request->request->count() > 0)
        {
            // user datas
            $fname = $request->request->get('fname');
            $lname = $request->request->get('lname');
            $email = $request->request->get('email');
            $phone = $request->request->get('phone');
            $country   = $request->request->get('country');

            //usetting datas
            $language  = $request->request->get('language');
            $languageCode  = $language == 'fr' ? 'fr': 'en';
            $languageName =  $language == 'fr' ? 'Français - French': 'Anglais - English';

            $timezone  = $request->request->get('timezone');
            $currency  = $request->request->get('currency');
          
            //profil photo setting begin
            $avatarProcess = $this->addEntity->profilePhotoSetter($request , $user, true);
            if (isset($avatarProcess['error']) && $avatarProcess['error'] == true){
            return $this->services->msg_error($this->intl->trans('Traitement du fichier image de profile'), $avatarProcess['info']);
            } else
            $avatarProcess;
            //profil photo setting end

            //user data setting
            $user->setPhone($phone);
            $user->setProfilePhoto($avatarProcess);
            $user->setUpdatedAt(new \DatetimeImmutable());
            $this->userRepository->add($user);

            $language  = [ 'code' => $languageCode, 'name' => $languageName];
            $currency  = [ 'code' => $currency, 'name' => "West African CFA Franc"];

            $usetting->setFirstname($fname)
                ->setLastname($lname)
                ->setLanguage($language)
                ->setCurrency($currency)
                ->setTimezone($timezone)
                ->setUpdatedAt(new \DatetimeImmutable());
            $this->usettingRepository->add($usetting);
            
            $this->userRepository->add($user);
            return $this->services->msg_success(
                $this->intl->trans("Modification de profil ").$user->getEmail(),
                $this->intl->trans("Paramètre de compte modifié avec succès"),
                $user->getProfilePhoto(),
            );
        }
        return $this->services->msg_success(
            $this->intl->trans("Gestion de profil : ").$user->getEmail(),
            $this->intl->trans("Gestion de profil exécuté"),
            $user->getProfilePhoto(),
        );
    }

    #[Route('/init_email_change', name: 'app_user_email_edit', methods: ['POST'])]
    public function email_reset(Request $request, UserPasswordHasherInterface $userPasswordHasher)
    {
        $user = $this->getUser();
        if ($request->request->count() > 0) 
        {
            if (!$this->isCsrfTokenValid($user->getUid(), $request->request->get('_token'))) 
            return $this->services->no_access($this->intl->trans("Modification de l'adresse email").': '.$user->getEmail());

            $email	  = $request->request->get('email');
		    $password =	$request->request->get('cpassword');
            if ($userPasswordHasher->isPasswordValid($user, $password))
            {
                //Validation on Linksettingcontroller : app_user_email_setting
                $code = $this->services->numeric_generate(6);
                $user->setActiveCode($code);
                $user->setUpdatedAt(new \DatetimeImmutable());
                $this->userRepository->add($user);
                // Lien de réinitialisation
                $base = $this->baseUrl->init();
                $email = base64_encode($email);
                $url   = $base.$this->urlGenerator->generate('app_user_email_setting', ["email" => $email,  "uid" => $user->getUid(),
                'token' => $request->request->get('_token'), "code" => $code ]);

                //email
                $message = $this->render('email/change-email.html.twig', [
                    'title'           => $this->intl->trans("Modification d'adresse email").' - '. $brand['name'],
                    'brand'           => $brand,
                    'data'            => [
                        'url'      => $url,
                        'user'     => $user,
                        'base_url' => $this->baseUrl
                    ]
                ]);

                $this->sMailer->nativeSend( $this->brand->get()['emails']['support'], 
                    $email ,  $this->intl->trans("Modification d'adresse email"),  $message);

                return $this->services->msg_warning(
                    $this->intl->trans("Modification de l'adresse email").':'.$user->getEmail(),
                    $this->intl->trans("Modification initialisé, veuillez consulter 
                    votre nouvelle adresse pour le vérifier et finaliser cette opération")
                );
            }
            return $this->services->msg_warning(
                $this->intl->trans("Modification de l'adresse email").':'.$user->getEmail(),
                $this->intl->trans("Le mot de passe saisie est incorrecte, veuillez vérifier et saisir votre mot de passe.")
            );
        }
        return $this->services->failedcrud(
            $this->intl->trans("Modification de l' adresse email".':'.$user->getEmail())
        );
    }

    #[Route('/init_password_change', name: 'app_user_password_edit', methods: ['POST'])]
    public function password_reset(Request $request, UserPasswordHasherInterface $userPasswordHasher)
    {
        $user = $this->getUser();
        if ($request->request->count() > 0) 
        {
            if (!$this->isCsrfTokenValid($user->getUid(), $request->request->get('_token'))) 
            return $this->services->no_access($this->intl->trans("Modification du mot de passe").': '.$user->getPhone());

		    $password    =	$request->request->get('currentpassword');
		    $newpassword =	$request->request->get('newpassword');
            if ($userPasswordHasher->isPasswordValid($user, $password))
            {
                if  ($userPasswordHasher->isPasswordValid($user, $newpassword))
                return $this->services->msg_warning(
                    $this->intl->trans("Modification du mot de passe"),
                    $this->intl->trans("Veuillez saisir un mot de passe différent de l'actuel")
                );

                $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $newpassword));// encode the plain password
                $user->setUpdatedAt(new \DatetimeImmutable());
                $this->userRepository->add($user);
               
                $this->addFlash('info', $this->intl->trans("Connectez vous avec votre adresse email et votre nouveau mot de passe"));
                //dd($this->baseUrl->init().$this->urlGenerator->generate("app_logout"));
                return $this->services->msg_success(
                    $this->intl->trans("Modification du mot de passe"),
                    $this->intl->trans("Votre mot de passe à été modifié avec succès, veuillez vous reconnecter maintenant"), 
                    $this->baseUrl->init().$this->urlGenerator->generate("app_logout")
                );
            }
            return $this->services->msg_warning(
                $this->intl->trans("Modification du mot de passe"),
                $this->intl->trans("L'ancien mot de passe saisie est incorrecte, veuillez vérifier et saisir votre mot de passe.")
            );
        }
        return $this->services->failedcrud(
            $this->intl->trans("Modification du mot de passe"),
        );
    }
    
    #[Route('/init_account_change', name: 'app_user_account_disable', methods: ['POST'])]
    public function account_reset(Request $request)
    {
        $user = $this->getUser();
        if ($request->request->count() > 0) 
        {
            if (!$this->isCsrfTokenValid($user->getUid(), $request->request->get('_token'))) 
            return $this->services->no_access($this->intl->trans("Désactivation du compte").': '.$user->getPhone());

            $user->setStatus($this->services->status(4));
            $user->setUpdatedAt(new \DatetimeImmutable());
            $this->userRepository->add($user);
            $this->addFlash('warning', $this->intl->trans("Votre compte à été désactivé"));
            return $this->services->msg_success(
                $this->intl->trans("Désactivation du compte"),
                $this->intl->trans("Compte désactivé, vous allez nous manquer; revenez vite !"), 
                $this->urlGenerator->generate("app_logout")
            );
        }
        return $this->services->failedcrud(
            $this->intl->trans("Désactivation du compte"),
        );
    }
   
}
