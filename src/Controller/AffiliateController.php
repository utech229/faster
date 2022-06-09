<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\User;
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
    EntityManagerInterface $entityManager, TranslatorInterface $translator, StatusRepository $statusRepository,
    RoleRepository $roleRepository, UserRepository $userRepository, PermissionRepository $permissionRepository,
    AuthorizationRepository $authorizationRepository, uBrand $brand,ValidatorInterface $validator,
    DbInitData $dbInitData, AddEntity $addEntity)
    {
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brickPhone      = $brickPhone;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->addEntity	     = $addEntity;
        $this->userRepository    = $userRepository;
        $this->roleRepository    = $roleRepository;
        $this->statusRepository  = $statusRepository;
        $this->validator         = $validator;
        $this->DbInitData        = $dbInitData;

        $this->permission      =    ["AFFL0", "AFFL1", "AFFL2", "AFFL3", "AFFL4"];
        $this->pAccess         =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate         =    $this->services->checkPermission($this->permission[1]);
        $this->pView           =    $this->services->checkPermission($this->permission[2]);
        $this->pUpdate         =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete         =    $this->services->checkPermission($this->permission[4]);
    }

    #[Route('', name: 'app_affiliate_index', methods: ['GET', 'POST'])]
    #[Route('/new', name: 'app_affiliate_add', methods: ['POST'])]
    #[Route('/{uid}/edit', name: 'app_affiliate_edit', methods: ['POST'])]
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, User $affiliate = null, 
    ValidatorInterface $validator): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

         /*----------MANAGE user CRU BEGIN -----------*/
        //define if method is user add 
        $isAffiliateAdd = (!$affiliate) ? true : false;
        $affiliate      = (!$affiliate) ? new User() : $affiliate;
       
        $form = $this->createForm(AffiliateType::class, $affiliate);
        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            if ($isAffiliateAdd == true) { //method calling
                if (!$this->pCreate) return $this->services->ajax_ressources_no_access($this->intl->trans("Création d'un affilié"));
                return $this->addAffiliate($request, $form, $affiliate , $userPasswordHasher);
            }else {
                if (!$this->pUpdate)   return $this->services->ajax_ressources_no_access($this->intl->trans("Modification d'un affilié"));
                return $this->updateAffiliate($request, $form, $affiliate);
            }
        }
        
        $statistics =  $this->statisticsData();
        $this->services->addLog($this->intl->trans('Accès au menu affiliés'));
        return $this->render('user/affiliate.html.twig', [
            'controller_name' => 'UserController',
            'role'            => $this->roleRepository->findAll(),
            'title'           => $this->intl->trans('Mes affiliés').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                'one'   => $this->intl->trans('affiliés'),
                'two'   => $this->intl->trans('Mes affiliés'),
                'none'  => $this->intl->trans('Gestion affilié'),
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
    public function addAffiliate($request, $form, $affiliate, $userPasswordHasher): Response
    {
        if(($form->isSubmitted() && $form->isValid()))
        {
            $affiliateUid = time().uniqid();
            $affiliate->setUid($affiliateUid);
            //profil photo setting begin
            $avatarProcess = $this->addEntity->profilePhotoSetter($request , $affiliate);
            if (isset($avatarProcess['error']) && $avatarProcess['error'] == true){
            return $this->services->ajax_error_crud($this->intl->trans('Traitement du fichier image de profile'), $avatarProcess['info']);
            } else
            $avatarProcess;
            //profil photo setting end

            $role          = $request->request->get('role');
            $countryCode   = strtoupper($request->request->get('country'));
            $countryDatas  = $this->brickPhone->getCountryByCode($countryCode);
            $countryDatas  = [
                'dial_code' => $countryDatas['dial_code'],
                'code'      => $countryCode,
                'name'      => $countryDatas['name']
            ];
            $currentUser   = $this->getUser(); //connected user
            $role          = $this->roleRepository->findOneBy(['code' => 'AFF']);
            //creation oh referralcode
            $password = strtoupper($this->services->idgenerate(10));
            //user data setting
            $affiliate->setBalance(0);
            $affiliate->setCreatedAt(new \DatetimeImmutable());
            $affiliate->setPassword($userPasswordHasher->hashPassword($affiliate, strtolower($password)));
            $affiliate->setRole($role);
            $affiliate->setAdmin($currentUser);
            $affiliate->setRoles(['ROLE_'.$role->getName()]);
            $affiliate->setCountry($countryDatas);
            $affiliate->setApiKey($currentUser->getApikey());
            $affiliate->setIsAffiliate(true);
            $affiliate->setProfilePhoto($avatarProcess);
            $this->userRepository->add($affiliate);
            $this->addEntity->defaultUsetting($affiliate);
            return $this->services->msg_success(
                $this->intl->trans("Création d'un nouvel affilié"),
                $this->intl->trans("Affilié ajouté avec succès")
            );
        }
        else 
        {
            //return $this->services->invalidForm($form, $this->intl);
            return $this->services->formErrorsNotification($this->validator, $this->intl, $affiliate);
        }
        return $this->services->failedcrud($this->intl->trans("Création d'un nouvel affilié : "  .$affiliate->getEmail()));
    }
 
    //update user function
    public function updateAffiliate($request, $form, $affiliate): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {
        
            $currentUser   = $this->getUser();
            //profil photo setting begin
            $avatarProcess = $this->addEntity->profilePhotoSetter($request , $affiliate, true);
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
            //user data setting
            $affiliate->setCountry($countryDatas);
            $affiliate->setProfilePhoto($avatarProcess);
            $affiliate->setUpdatedAt(new \DatetimeImmutable());
       
            $this->userRepository->add($affiliate);
            return $this->services->msg_success(
                $this->intl->trans("Modification de l'affilié ").$affiliate->getEmail(),
                $this->intl->trans("Affilié modifié avec succès").' : '.$affiliate->getEmail()
            );
        }
        else 
        {
            //return $this->services->invalidForm($form, $this->intl);
            return $this->services->formErrorsNotification($this->validator, $this->intl, $affiliate);
        }
        return $this->services->failedcrud($this->intl->trans("Modification de l'affilié : " .$request->request->get('user_name')));
    }

    #[Route('/{uid}/get', name: 'get_this_affiliate', methods: ['POST'])]
    public function get_this_affiliate(Request $request, User $affiliate): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
        return $this->services->ajax_ressources_no_access($this->intl->trans("Récupération de l'affilié").': '.$affiliate->getEmail());

        $row['orderId']      = $affiliate->getUid();
        $row['firstname']    = $affiliate->getFirstName();
        $row['lastname']     = $affiliate->getLastName();
        $row['email']        = $affiliate->getEmail();
        $row['photo']        = $affiliate->getProfilePhoto();
        $row['phone']        = $affiliate->getPhone();
        $row['gender']       = $affiliate->getGender();
        $row['role']         = $affiliate->getRole()->getCode();
        $row['countryCode']  = $affiliate->getCountry()['code'];
        $row['countryName']  = $affiliate->getCountry()['name'];
        $row['balance']      = $affiliate->getBalance();
        $row['status']       = $affiliate->getStatus();
        $row['lastLogin']    = ($affiliate->getLastLoginAt()) ? $affiliate->getLastLoginAt()->format("c") : null;
        $row['createdAt']    = $affiliate->getCreatedAt()->format("c");

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
        $affiliates = $this->userRepository->findBy(['admin' => $this->getUser(), 'isAffiliate' => true]);
        foreach ($affiliates  as $affiliate) 
		{          
            $row                 = array();
            $country = $affiliate->getCountry();
            $row['orderId']      = $affiliate->getUid();
            $row['user']         = ['name'  => $affiliate->getFirstName().' '.$affiliate->getLastName(), 
                                    'email' => $affiliate->getEmail(), 
                                    'photo' => $affiliate->getProfilePhoto()];
            $row['phone']        = $affiliate->getPhone();
            $row['role']         = $affiliate->getRoles()[0];
            $row['country']      = $affiliate->getCountry()['name'];
            $row['balance']      = $affiliate->getBalance();
            $row['status']       = $affiliate->getStatus();
            $row['lastLogin']    = ($affiliate->getLastLoginAt()) ? $affiliate->getLastLoginAt()->format("c") : null;
            $row['createdAt']    = $affiliate->getCreatedAt()->format("c");
            $row['action']       = $affiliate->getUid();
            $data []             = $row;
		}
        $this->services->addLog($this->intl->trans('Lecture de la liste des affiliés'));
        $output = array("data" => $data);
        return new JsonResponse($output);
    }

    #[Route('/{uid}/delete', name: 'app_affiliate_delete', methods: ['POST'])]
    public function delete(Request $request, User $affiliate): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
            return $this->services->ajax_ressources_no_access($this->intl->trans("Suppression d'un affilié").': '.$affiliate->getEmail());

        $affiliate->setStatus(4);
        $this->userRepository->add($affiliate);
        return $this->services->msg_success(
            $this->intl->trans("Suppression de l'affilié ").$affiliate->getEmail(),
            $this->intl->trans("Affilié supprimé avec succès").' : '.$affiliate->getEmail(),
        );
    }

    public function statisticsData()
    {
        $allUsers          = $this->userRepository->countAllUsers()[0][1];
        $pendingUsers      = $this->userRepository->countAllUsersByStatus(0)[0][1];
        $activeUsers       = $this->userRepository->countAllUsersByStatus(1)[0][1];
        $desactivatedUsers = $this->userRepository->countAllUsersByStatus(2)[0][1];
        $suspendedUsers    = $this->userRepository->countAllUsersByStatus(3)[0][1];
        $deletedUsers      = $this->userRepository->countAllUsersByStatus(4)[0][1];

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
    

    


    
}
