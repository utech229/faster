<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\User;
use App\Entity\Brand;
use App\Form\UserType;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\AddEntity;
use App\Form\AffiliateType;
use App\Service\BrickPhone;
use App\Service\DbInitData;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\BrandRepository;
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
#[Route('/{_locale}/home/affiliates')]
class AffiliateController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, UrlGeneratorInterface $urlGenerator, Services $services, BrickPhone $brickPhone,  
    EntityManagerInterface $entityManager, TranslatorInterface $translator,
    RoleRepository $roleRepository, UserRepository $userRepository, PermissionRepository $permissionRepository,
    AuthorizationRepository $authorizationRepository, uBrand $brand,ValidatorInterface $validator,
    DbInitData $dbInitData, AddEntity $addEntity, StatusRepository $statusRepository, BrandRepository $brandRepository)
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
        $this->brandRepository  = $brandRepository;
        $this->validator         = $validator;
        $this->DbInitData        = $dbInitData;


        $this->permission      =    ["AFFL0","AFFL1", "AFFL2", "AFFL3", "AFFL4"];
        $this->pAccess         =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate         =    $this->services->checkPermission($this->permission[1]);
        $this->pView           =    $this->services->checkPermission($this->permission[2]);
        $this->pUpdate         =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete         =    $this->services->checkPermission($this->permission[4]);
    }

    #[Route('', name: 'app_affiliate_index', methods: ['GET', 'POST'])]
    #[Route('/new', name: 'app_affiliate_add', methods: ['POST'])]
    #[Route('/{uid}/edit', name: 'app_affiliate_edit', methods: ['POST'])]
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, User $user = null, 
    ValidatorInterface $validator): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }
        //define if method is user add 
        $isAffiliateAdd = (!$user) ? true : false;
        $user      = (!$user) ? new User() : $user;
       
        $form = $this->createForm(AffiliateType::class, $user);
        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            if ($isAffiliateAdd == true) { //method calling
                if (!$this->pCreate) return $this->services->no_access($this->intl->trans("Création d'un affilié"));
                return $this->addUser($request, $form, $user, $userPasswordHasher);
            }else {
                if (!$this->pUpdate)   return $this->services->no_access($this->intl->trans("Modification d'un affilié"));
                return $this->updateUser($request, $form, $user);
            }
        }
        
        $statistics =  $this->statisticsData();
        $this->services->addLog($this->intl->trans('Accès au menu affiliés'));

        list($userType, $masterId, $userRequest) = $this->services->checkThisUser($this->pView);
        $brands = $this->em->getRepository(Brand::class)->findBrandBy($userType,  $userRequest);
        
        return $this->render('affiliate/index.html.twig', [
            'controller_name' => 'UserController',
            'role'            => $this->roleRepository->getManageUserRole($this->getUser()->getRole()->getLevel()),
            'title'           => $this->intl->trans('Mes affiliés').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans('Affiliés')],
            ],
            'brand'           => $this->brand->get(),
            'brands'          => $brands,
            'baseUrl'         => $this->baseUrl->init(),
            'users'           => $this->userRepository->findAll(),
            'userform'        => $form->createView(),
            'pCreateUser'     => $this->pCreate,
            'pEditUser'       => $this->pUpdate,
            'pDeleteUser'     => $this->pDelete,
            'pViewUser'       => $this->pView,
            'stats'           => $statistics,
        ]);
    }

    //Add user function
    public function addUser($request, $form, $user, $userPasswordHasher): Response
    {
        if(($form->isSubmitted() && $form->isValid()))
        {
            $admin   = $this->getUser(); //connected user

            //profil photo setting begin
            $avatarProcess = $this->addEntity->profilePhotoSetter($request , $user);
            if (isset($avatarProcess['error']) && $avatarProcess['error'] == true){
            return $this->services->msg_error($this->intl->trans('Traitement du fichier image de profile'), $avatarProcess['info']);
            } else
            $avatarProcess;
            //profil photo setting end
            $countryCode   = strtoupper($request->request->get('country'));
            $countryDatas  = $this->brickPhone->getInfosCountryFromCode($countryCode);
            if ($countryDatas) {
                $countryDatas  = [
                    'dial_code' => $countryDatas['dial_code'],
                    'code'      => $countryCode,
                    'name'      => $countryDatas['name']
                ];
            }else
            return $this->services->msg_error(
                $this->intl->trans("Insertion du tableau de données pays"),
                $this->intl->trans("La recherche du nom du pays à échoué : BrickPhone"),
            );

            //user data setting
            $user->setBrand($admin->getBrand());
            $user->setBalance($admin->getBalance());
            $user->setPaymentAccount($admin->getPaymentAccount());
            $user->setApikey($admin->getApikey());
            $user->setCreatedAt(new \DatetimeImmutable());
            $user->setPassword($userPasswordHasher->hashPassword($user, strtoupper(123456)));
            $user->setRoles(['ROLE_'.$form->get('role')->getData()->getName()]);
            $user->setCountry($countryDatas);
            $user->setPrice($admin->getPrice());
            $user->setUid(time().uniqid());
            $user->setProfilePhoto($avatarProcess);
            dd($user);
            $this->userRepository->add($user);
            $setDefaultSetting = $this->addEntity->defaultUsetting($user,$form->get('firstname')->getData(), $form->get('lastname')->getData());
            return $this->services->msg_success(
                $this->intl->trans("Création d'un nouvel affilié"),
                $this->intl->trans("Affilié ajouté avec succès")
            );
        }
        else 
        {
            //return $this->services->invalidForm($form, $this->intl);
            return $this->services->formErrorsNotification($this->validator, $this->intl, $user);
        }
        return $this->services->failedcrud($this->intl->trans("Création d'un nouvel affilié : "  .$user->getEmail()));
    }
 
    //update user function
    public function updateUser($request, $form, $user): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {
        
            //profil photo setting begin
            $avatarProcess = $this->addEntity->profilePhotoSetter($request , $user, true);
            if (isset($avatarProcess['error']) && $avatarProcess['error'] == true){
            return $this->services->msg_error($this->intl->trans('Traitement du fichier image de profile'), $avatarProcess['info']);
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
            //user data setting
            $user->setRoles(['ROLE_'.$form->get('role')->getData()->getName()]);
            $user->setCountry($countryDatas);
            $user->setPaymentAccount($this->comptes);
            $user->setProfilePhoto($avatarProcess);
            $user->setUpdatedAt(new \DatetimeImmutable());
            //$user usetting data
            $user->getUsetting()->setFirstname($form->get('firstname')->getData())
                                ->setLastname($form->get('lastname')->getData());

            return $this->services->msg_success(
                $this->intl->trans("Modification de l'affilié ").$user->getEmail(),
                $this->intl->trans("affilié modifié avec succès").' : '.$user->getEmail()
            );
        }
        else 
        {
            //return $this->services->invalidForm($form, $this->intl);
            return $this->services->formErrorsNotification($this->validator, $this->intl, $user);
        }
        return $this->services->failedcrud($this->intl->trans("Modification de l'affilié : " .$request->request->get('user_name')));
    }

    #[Route('/{uid}/get', name: 'get_this_user', methods: ['POST'])]
    public function get_this_user(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
        return $this->services->no_access($this->intl->trans("Récupération de l'affilié").': '.$user->getEmail());
        $usetting            = $user->getUsetting();
        $role                = $user->getRole();
        $brand               =  $user->getBrand();
        $route               =  $user->getRouter();
        $sender              =  $brand->getDefaultSender();


        $row['orderId']      = $user->getUid();
        $row['user']         = [   'name'  => $usetting->getFirstname().' '.$usetting->getLastname(),'firstname' => $usetting->getFirstname(),
                                   'lastname'  => $usetting->getLastname(), 'email' => $user->getEmail(), 'photo' => $user->getProfilePhoto()];
        $row['role']         =  ['name'  => $role->getName(),'level' => $role->getLevel(),'code' => $role->getCode()];
        $row['brand']        = [ 'name' => $brand->getName(), 'uid' => $brand->getUid()];
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

    #[Route('/list', name: 'app_affiliate_list', methods: ['POST'])]
    public function getUsers(Request $request, EntityManagerInterface $manager) : Response
    {
        //Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
            return $this->services->invalid_token_ajax_list($this->intl->trans('Récupération de la liste des affiliés : token invalide'));

        $data = [];
        $users = (!$this->pAccess) ? [] : $this->getUsersByRoles();
        foreach ($users  as $user) 
		{          
            $row                 = array();
            $usetting            = $user->getUsetting();
            $country             = $user->getCountry();
            $brand               = $user->getBrand();
            $row['orderId']      = $user->getUid();
            $row['user']         =  [   'name'  => $usetting->getFirstname().' '.$usetting->getLastname(),
                                        'firstname' => $usetting->getFirstname(),
                                        'lastname'  => $usetting->getLastname(), 
                                        'email' => $user->getEmail(), 
                                        'photo' => $user->getProfilePhoto()];
            $row['phone']        = $user->getPhone();
            $row['brand']        = [   'name'  => $brand->getName(),'uid' => $brand->getUid(),'roleLevel' => $user->getRole()->getLevel()];
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
        $this->services->addLog($this->intl->trans('Lecture de la liste des affiliés'));
        $output = array("data" => $data);
        return new JsonResponse($output);
    }

    #[Route('/{uid}/delete', name: 'app_affiliate_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
            return $this->services->no_access($this->intl->trans("Suppression d'un affilié").': '.$user->getEmail());

        if ($user->getId() == 1 or $user->getBrand()->getManager() ==  $user) 
        return $this->services->msg_warning(
            $this->intl->trans("Suppression de l'affilié ").$user->getEmail(),
            $this->intl->trans("Vous ne pouvez pas supprimer cet affilié car il s'agit de l'administrateur de la marque active"),
        );

        //$user->setStatus($this->services->status(3));
        $this->userRepository->remove($user);
        return $this->services->msg_success(
            $this->intl->trans("Suppression de l'affilié ").$user->getEmail(),
            $this->intl->trans("affilié supprimé avec succès").' : '.$user->getEmail(),
        );
    }

    //user retriving by permissions
    public function getUsersByRoles() 
    {
        list($userType, $masterId, $userRequest) = $this->services->checkThisUser($this->pView);
        $users = $this->userRepository->getUsersByPermission('',$userType,$masterId, 2);
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
