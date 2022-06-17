<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\User;
use App\Form\UserType;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\AddEntity;
use App\Service\BrickPhone;
use App\Service\DbInitData;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AuthorizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('/{_locale}/home/users')]
class UserController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, UrlGeneratorInterface $urlGenerator, Services $services, BrickPhone $brickPhone,  
    EntityManagerInterface $entityManager, TranslatorInterface $translator,
    RoleRepository $roleRepository, UserRepository $userRepository, PermissionRepository $permissionRepository,
    AuthorizationRepository $authorizationRepository, uBrand $brand,ValidatorInterface $validator,
    DbInitData $dbInitData, AddEntity $addEntity, StatusRepository $statusRepository)
    {
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brickPhone      = $brickPhone;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->addEntity	   = $addEntity;
        $this->userRepository  = $userRepository;
        $this->roleRepository    = $roleRepository;
        $this->statusRepository  = $statusRepository;
        $this->validator         = $validator;
        $this->DbInitData        = $dbInitData;

        $this->permission      =    ["UTI0", "UTI1", "UTI2", "UTI3", "UTI4","AFFL0", "AFFL1", "AFFL2", "AFFL3", "AFFL4"];
        $this->pAccess         =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate         =    $this->services->checkPermission($this->permission[1]);
        $this->pView           =    $this->services->checkPermission($this->permission[2]);
        $this->pUpdate         =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete         =    $this->services->checkPermission($this->permission[4]);
        $this->pAffiliateAccess         =    $this->services->checkPermission($this->permission[5]);
        $this->pAffiliateCreate         =    $this->services->checkPermission($this->permission[6]);
        $this->pAffiliateView           =    $this->services->checkPermission($this->permission[7]);
        $this->pAffiliateUpdate         =    $this->services->checkPermission($this->permission[8]);
        $this->pAffiliateDelete         =    $this->services->checkPermission($this->permission[9]);
    }

    #[Route('', name: 'app_user_index', methods: ['GET', 'POST'])]
    #[Route('/new', name: 'app_user_add', methods: ['POST'])]
    #[Route('/{uid}/edit', name: 'app_user_edit', methods: ['POST'])]
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, User $user = null, 
    ValidatorInterface $validator): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }
        //define if method is user add 
        $isUserAdd = (!$user) ? true : false;
        $user      = (!$user) ? new User() : $user;
       
        $form = $this->createForm(UserType::class, $user);
        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            if ($isUserAdd == true) { //method calling
                if (!$this->pCreate) return $this->services->ajax_ressources_no_access($this->intl->trans("Création d'un utilisateur"));
                return $this->addUser($request, $form, $user , $userPasswordHasher);
            }else {
                if (!$this->pUpdate)   return $this->services->ajax_ressources_no_access($this->intl->trans("Modification d'un utilisateur"));
                return $this->updateUser($request, $form, $user);
            }
        }
        
        $statistics =  $this->statisticsData();
        $this->services->addLog($this->intl->trans('Accès au menu utilisateurs'));
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'role'            => $this->roleRepository->findAll(),
            'title'           => $this->intl->trans('Mes utilisateurs').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans('Utilisateurs')],
            ],
            'brand'           => $this->brand->get(),
            'baseUrl'         => $this->baseUrl->init(),
            'users'           => $this->userRepository->findAll(),
            'userform'        => $form->createView(),
            'pCreateUser'     => $this->pCreate,
            'pEditUser'       => $this->pUpdate,
            'pDeleteUser'     => $this->pDelete,
            'stats'           => $statistics,
        ]);
    }

    //Add user function
    public function addUser($request, $form, $user, $userPasswordHasher): Response
    {
        if(($form->isSubmitted() && $form->isValid()))
        {
            //profil photo setting begin
            $avatarProcess = $this->addEntity->profilePhotoSetter($request , $user);
            if (isset($avatarProcess['error']) && $avatarProcess['error'] == true){
            return $this->services->ajax_error_crud($this->intl->trans('Traitement du fichier image de profile'), $avatarProcess['info']);
            } else
            $avatarProcess;
            //profil photo setting end
            $countryCode   = strtoupper($request->request->get('country'));
            $countryDatas  = $this->brickPhone->getInfosCountryFromCode($countryCode);
            $countryDatas  = [
                'dial_code' => $countryDatas['dial_code'],
                'code'      => $countryCode,
                'name'      => $countryDatas['name']
            ];
            $currentUser   = $this->getUser(); //connected user
            //user data setting
            $user->setBalance(0);
            $user->setApikey($this->services->idgenerate(32));
            $user->setCreatedAt(new \DatetimeImmutable());
            $user->setPassword($userPasswordHasher->hashPassword($user, strtoupper($this->services->idgenerate(8))));
            $user->setRoles(['ROLE_'.$form->get('role')->getData()->getName()]);
            $user->setCountry($countryDatas);
            $user->setCountry($countryDatas);
            $user->setUid(time().uniqid());
            $user->setProfilePhoto($avatarProcess);
            $this->userRepository->add($user);
            $setDefaultSetting = $this->addEntity->defaultUsetting($user,$form->get('firstname')->getData(), $form->get('lastname')->getData());
            return $this->services->msg_success(
                $this->intl->trans("Création d'un nouvel utilisateur"),
                $this->intl->trans("Utilisateur ajouté avec succès")
            );
        }
        else 
        {
            //return $this->services->invalidForm($form, $this->intl);
            return $this->services->formErrorsNotification($this->validator, $this->intl, $user);
        }
        return $this->services->failedcrud($this->intl->trans("Création d'un nouvel utilisateur : "  .$user->getEmail()));
    }
 
    //update user function
    public function updateUser($request, $form, $user): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {
        
            //profil photo setting begin
            $avatarProcess = $this->addEntity->profilePhotoSetter($request , $user, true);
            if (isset($avatarProcess['error']) && $avatarProcess['error'] == true){
            return $this->services->ajax_error_crud($this->intl->trans('Traitement du fichier image de profile'), $avatarProcess['info']);
            } else
            $avatarProcess;
            //profil photo setting end
            $countryCode   = strtoupper($request->request->get('country'));
            $countryDatas  = $this->brickPhone->getCountryByCode($countryCode);
            $countryDatas  = [
                'dial_code' => $countryDatas['dial_code'],
                'code'      => $countryCode,
                'name'      => $countryDatas['name']
            ];
            $city          = $request->request->get('user_city');
            //user data setting
            $user->setRoles(['ROLE_'.$form->get('role')->getData()->getName()]);
            $user->setCountry($countryDatas);
            $user->setProfilePhoto($avatarProcess);
            $user->setUpdatedAt(new \DatetimeImmutable());
            //$user usetting data
            $user->getUsetting()->setFirstname($form->get('firstname')->getData())
                                ->setLastname($form->get('lastname')->getData());

            return $this->services->msg_success(
                $this->intl->trans("Modification de l'utilisateur ").$user->getEmail(),
                $this->intl->trans("Utilisateur modifié avec succès").' : '.$user->getEmail()
            );
        }
        else 
        {
            //return $this->services->invalidForm($form, $this->intl);
            return $this->services->formErrorsNotification($this->validator, $this->intl, $user);
        }
        return $this->services->failedcrud($this->intl->trans("Modification de l'utilisateur : " .$request->request->get('user_name')));
    }

    #[Route('/{uid}/get', name: 'get_this_user', methods: ['POST'])]
    public function get_this_user(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
        return $this->services->ajax_ressources_no_access($this->intl->trans("Récupération de l'utilisateur").': '.$user->getEmail());
        $usetting            = $user->getUsetting();
        $role                = $user->getRole();
        $brand               =  $user->getBrand();
        $route               =  $user->getRouter();
        $sender              =  $brand->getDefaultSender();


        $row['orderId']      = $user->getUid();
        $row['user']         = [   'name'  => $usetting->getFirstname().' '.$usetting->getLastname(),'firstname' => $usetting->getFirstname(),
                                   'lastname'  => $usetting->getLastname(), 'email' => $user->getEmail(), 'photo' => $user->getProfilePhoto()];
        $row['role']         =  ['name'  => $role->getName(),'level' => $role->getLevel(),'code' => $role->getCode()];
        $row['brand']        = $brand->getName();
        $row['route']        = $user->getRouter()->getName();
        $row['email']        = $user->getEmail();
        $row['photo']        = $user->getProfilePhoto();
        $row['phone']        = $user->getPhone();
        $row['apikey']       = $user->getApikey();
        $row['isPostPay']    = $user->IsPostPay() ? '1' : '0';
        $row['isDlr']        = $user->getIsDlr() ? '1' : '0';
        $row['language']     = $usetting->getLanguage()['code'];
        $row['currency']     = $usetting->getCurrency()['code'];
        $row['timezone']     = $usetting->getTimezone();
        $row['countryCode']  = $user->getCountry()['code'];
        $row['countryName']  = $user->getCountry()['name'];
        $row['balance']      = $user->getBalance();
        $row['status']       = $user->getStatus()->getUid();
        $row['lastLogin']    = ($user->getLastLoginAt()) ? $user->getLastLoginAt()->format("c") : null;
        $row['createdAt']    = $user->getCreatedAt()->format("c");

        return new JsonResponse([
            'data' => $row, 
            'message' => $this->intl->trans('Vos données sont chargés avec succès.')]);
    }  

    #[Route('/list', name: 'app_user_list', methods: ['POST'])]
    public function getUsers(Request $request, EntityManagerInterface $manager) : Response
    {
        //Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
            return $this->services->invalid_token_ajax_list($this->intl->trans('Récupération de la liste des utilisateurs : token invalide'));

        $data = [];
        $users = (!$this->pAccess) ? [] : $this->getUsersByRoles();
        foreach ($users  as $user) 
		{          
            $row                 = array();
            $usetting            = $user->getUsetting();
            $country             = $user->getCountry();
            $row['orderId']      = $user->getUid();
            $row['user']         =  [   'name'  => $usetting->getFirstname().' '.$usetting->getLastname(),
                                        'firstname' => $usetting->getFirstname(),
                                        'lastname'  => $usetting->getLastname(), 
                                        'email' => $user->getEmail(), 
                                        'photo' => $user->getProfilePhoto()];
            $row['phone']        = $user->getPhone();
            $row['role']         = $user->getRoles()[0];
            $row['country']      = $user->getCountry()['name'];
            $row['postPay']      = $user->IsPostPay();
            $row['isDlr']        = $user->getIsDlr();
            $row['balance']      = $user->getBalance();
            $row['status']       = $user->getStatus()->getCode();
            $row['lastLogin']    = ($user->getLastLoginAt()) ? $user->getLastLoginAt()->format("Y-m-d H:i:sP") : null;
            $row['createdAt']    = $user->getCreatedAt()->format("Y-m-d H:i:sP");
            $row['action']       = $user->getUid();
            $data []             = $row;
		}
        $this->services->addLog($this->intl->trans('Lecture de la liste des utilisateurs'));
        $output = array("data" => $data);
        return new JsonResponse($output);
    }

    #[Route('/{uid}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
            return $this->services->ajax_ressources_no_access($this->intl->trans("Suppression d'un utilisateur").': '.$user->getEmail());

        if ($user->getId() == 1 or $user->getBrand()->getManager() ==  $user) 
        return $this->services->msg_warning(
            $this->intl->trans("Suppression de l'utilisateur ").$user->getEmail(),
            $this->intl->trans("Vous ne pouvez pas supprimer cet utilisateur car il s'agit de l'administrateur de la marque active"),
        );

        $user->setStatus(4);
        $this->userRepository->add($user);
        return $this->services->msg_success(
            $this->intl->trans("Suppression de l'utilisateur ").$user->getEmail(),
            $this->intl->trans("Utilisateur supprimé avec succès").' : '.$user->getEmail(),
        );
    }

    public function getUsersByRoles() 
    {
        /*list($userType, $masterId, $userRequest) = $this->services->checkThisUser($this->pView);
        $users = $this->em->getRepository(User::class)->getUsersByPermission("", $userType, $masterId, 1);*/
        $users = $this->em->getRepository(User::class)->findAll();
        return $users;
    }

    public function statisticsData()
    {
        $allUsers     = $this->userRepository->countAllUsers()[0][1];
        $pendingUsers = $this->userRepository->countAllUsersByStatus(0)[0][1];
        $activeUsers  = $this->userRepository->countAllUsersByStatus(1)[0][1];
        $desactivatedUsers = $this->userRepository->countAllUsersByStatus(2)[0][1];
        $suspendedUsers = $this->userRepository->countAllUsersByStatus(3)[0][1];
        $deletedUsers = $this->userRepository->countAllUsersByStatus(4)[0][1];

        return [
            'all'          => $allUsers,
            'pending'      => $pendingUsers,
            'active'       => $activeUsers,
            'desactivated' => $desactivatedUsers,
            'suspended'    => $suspendedUsers,
            'deleted'      => $deletedUsers,
        ];
    }

    #[Route('/statisticsDataByAjax', name: 'get_statistics_data', methods: ['POST'])]
    public function statisticsDataByAjax(Request $request): Response
    {
        $data =  $this->statisticsData();
        return new JsonResponse(['data' =>$data]);
    }

    #[Route('/brand', name: 'get_statista')]
    public function stati(Request $request, uBrand $brand): JsonResponse
    {
       return new JsonResponse([
           'dede' => $brand->index()['author']['name']
       ]);
    }
    

    


    
}
