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
#[Route('/{_locale}/home/affiliate')]
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
    public function index(Request $request, UserPasswordHasherInterface $affiliatePasswordHasher, User $affiliate = null, 
    ValidatorInterface $validator): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }
        //define if method is user add 
        $isAffiliateAdd = (!$affiliate) ? true  : false;
        $affiliate      = (!$affiliate) ? new User() : $affiliate;
       
        $form = $this->createForm(AffiliateType::class, $affiliate);
        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            if ($isAffiliateAdd == true) { //method calling
                if (!$this->pCreate) return $this->services->no_access($this->intl->trans("Création d'un affilié"));
                return $this->addAffiliate($request, $form, $affiliate, $affiliatePasswordHasher);
            }else {
                if (!$this->pUpdate)   return $this->services->no_access($this->intl->trans("Modification d'un affilié"));
                return $this->updateAffiliate($request, $form, $affiliate);
            }
        }
        
        $statistics =  $this->statisticsData();
        $this->services->addLog($this->intl->trans('Accès au menu affiliés'));

        list($affiliateType, $masterId, $affiliateRequest) = $this->services->checkThisUser($this->pView);
        $brands = $this->em->getRepository(Brand::class)->findBrandBy($affiliateType,  $affiliateRequest);
        
        return $this->render('affiliate/index.html.twig', [
            'controller_name' => 'AffiliateController',
            'role'            => $this->roleRepository->getManageUserRole($this->getUser()->getRole()->getLevel()),
            'title'           => $this->intl->trans('Mes affiliés').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans('Affiliés')],
            ],
            'brand'           => $this->brand->get(),
            'brands'          => $brands,
            'baseUrl'         => $this->baseUrl->init(),
            'affiliateform'        => $form->createView(),
            'pCreateAffiliate'     => $this->pCreate,
            'pEditAffiliate'       => $this->pUpdate,
            'pDeleteAffiliate'     => $this->pDelete,
            'pViewAffiliate'       => $this->pView,
            'stats'           => $statistics,
        ]);
    }

    //Add affiliate function
    public function addAffiliate($request, $form, $affiliate, $affiliatePasswordHasher): Response
    {
        if(($form->isSubmitted() && $form->isValid()))
        {
            $admin  = $this->getUser(); //connected user
            $brand  =  $admin->getBrand();
            //and verify email
            $email = $form->get('email')->getData();
            $isExistedAffiliate = $this->userRepository->findOneBy(['email' => $email, 'brand' => $brand]);
            if ($isExistedAffiliate) {
                return $this->services->msg_error(
                    $this->intl->trans("Ajout d'un nouvel utilisateur"),
                    $this->intl->trans("Cet adresse email appartient à un compte existant, veuillez le changer"),
                );
            }//end verify email
            //begin role definer
            if ($admin->getRole()->getName() == 'RESELLER') {
                $role = $this->roleRepository->findOneByName('AFFILIATE_RESELLER');
            }else
            $role = $this->roleRepository->findOneByName('AFFILIATE_USER');
            //end role definer
            //profil photo setting begin
            $avatarProcess = $this->addEntity->profilePhotoSetter($request , $affiliate);
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
            //affiliate data setting
            $affiliate->setBrand($admin->getBrand());
            $affiliate->setBalance($admin->getBalance());
            $affiliate->setPaymentAccount($admin->getPaymentAccount());
            $affiliate->setApikey($admin->getApikey());
            $affiliate->setCreatedAt(new \DatetimeImmutable());
            $affiliate->setPassword($affiliatePasswordHasher->hashPassword($affiliate, strtoupper(123456)));
            $affiliate->setRoles(['ROLE_'.$role->getName()]);
            $affiliate->setRole($role);
            $affiliate->setAffiliateManager($admin);
            $affiliate->setCountry($countryDatas);
            $affiliate->setPrice($admin->getPrice());
            $affiliate->setIsDlr($admin->getIsDlr());
            $affiliate->setRouter($admin->getRouter());
            $affiliate->setPostPay($admin->isPostPay());
            $affiliate->setUid(time().uniqid());
            $affiliate->setProfilePhoto($avatarProcess);
            $this->userRepository->add($affiliate);
            $settingData = [
                'ccode' => $admin->getUsetting()->getCurrency()['code'],
                'cname' => $admin->getUsetting()->getCurrency()['name'],
                'ufirstname' => $form->get('firstname')->getData(),
                'ulastname'  => $form->get('lastname')->getData(),
            ];
            $setDefaultSetting = $this->addEntity->defaultUsetting($affiliate, $settingData);

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
 
    //update affiliate function
    public function updateAffiliate($request, $form, $affiliate): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {
        
            //profil photo setting begin
            $avatarProcess = $this->addEntity->profilePhotoSetter($request , $affiliate, true);
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
            //affiliate data setting
            $affiliate->setCountry($countryDatas);
            $affiliate->setProfilePhoto($avatarProcess);
            $affiliate->setUpdatedAt(new \DatetimeImmutable());
            //$affiliate usetting data
            $affiliate->getUsetting()->setFirstname($form->get('firstname')->getData())
                                ->setLastname($form->get('lastname')->getData());

            return $this->services->msg_success(
                $this->intl->trans("Modification de l'affilié ").$affiliate->getEmail(),
                $this->intl->trans("affilié modifié avec succès").' : '.$affiliate->getEmail()
            );
        }
        else 
        {
            //return $this->services->invalidForm($form, $this->intl);
            return $this->services->formErrorsNotification($this->validator, $this->intl, $affiliate);
        }
        return $this->services->failedcrud($this->intl->trans("Modification de l'affilié : " .$request->request->get('affiliate_name')));
    }

    #[Route('/{uid}/get', name: 'get_this_affiliate', methods: ['POST'])]
    public function get_this_affiliate(Request $request, User $affiliate): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
        return $this->services->no_access($this->intl->trans("Récupération de l'affilié").': '.$affiliate->getEmail());
        $usetting            = $affiliate->getUsetting();
        $role                = $affiliate->getRole();
        $brand               =  $affiliate->getBrand();
        $route               =  $affiliate->getRouter();
        $sender              =  $brand->getDefaultSender();


        $row['orderId']      = $affiliate->getUid();
        $row['affiliate']         = [   'name'  => $usetting->getFirstname().' '.$usetting->getLastname(),'firstname' => $usetting->getFirstname(),
                                   'lastname'  => $usetting->getLastname(), 'email' => $affiliate->getEmail(), 'photo' => $affiliate->getProfilePhoto()];
        $row['role']         =  ['name'  => $role->getName(),'level' => $role->getLevel(),'code' => $role->getCode()];
        $row['brand']        = [ 'name' => $brand->getName(), 'uid' => $brand->getUid()];
        $row['route']        = $affiliate->getRouter()->getName();
        $row['email']        = $affiliate->getEmail();
        $row['photo']        = $affiliate->getProfilePhoto();
        $row['phone']        = $affiliate->getPhone();
        $row['apikey']       = $affiliate->getApikey();
        $row['isPostPay']    = $affiliate->IsPostPay() ? '1' : '0';
        $row['isDlr']        = $affiliate->getIsDlr() ? '1' : '0';
        $row['language']     = $usetting->getLanguage()['code'];
        $row['currency']     = $usetting->getCurrency()['code'];
        $row['timezone']     = $usetting->getTimezone();
        $row['countryCode']  = $affiliate->getCountry()['code'];
        $row['countryName']  = $affiliate->getCountry()['name'];
        $row['balance']      = $affiliate->getBalance();
        $row['status']       = $affiliate->getStatus()->getUid();
        $row['lastLogin']    = ($affiliate->getLastLoginAt()) ? $affiliate->getLastLoginAt()->format("c") : null;
        $row['createdAt']    = $affiliate->getCreatedAt()->format("c");

        return new JsonResponse([
            'data' => $row, 
            'message' => $this->intl->trans('Vos données sont chargés avec succès.')]);
    }  

    #[Route('/list', name: 'app_affiliate_list', methods: ['POST'])]
    public function getAffiliates(Request $request, EntityManagerInterface $manager) : Response
    {
        //Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
            return $this->services->invalid_token_ajax_list($this->intl->trans('Récupération de la liste des affiliés : token invalide'));

        $data = [];
        $affiliates = (!$this->pAccess) ? [] : $this->getAffiliatesByRoles();
        foreach ($affiliates  as $affiliate) 
		{          
            $row                 = array();
            $usetting            = $affiliate->getUsetting();
            $country             = $affiliate->getCountry();
            $brand               = $affiliate->getBrand();
            $row['orderId']      = $affiliate->getUid();
            $row['affiliate']         =  [   'name'  => $usetting->getFirstname().' '.$usetting->getLastname(),
                                        'firstname' => $usetting->getFirstname(),
                                        'lastname'  => $usetting->getLastname(), 
                                        'email' => $affiliate->getEmail(), 
                                        'photo' => $affiliate->getProfilePhoto()];
            $row['phone']        = $affiliate->getPhone();
            $row['brand']        = [   'name'  => $brand->getName(),'uid' => $brand->getUid(),'roleLevel' => $affiliate->getRole()->getLevel()];
            $row['role']         = $affiliate->getRoles()[0];
            $row['country']      = $affiliate->getCountry()['name'];
            $row['postPay']      = $affiliate->IsPostPay();
            $row['isDlr']        = $affiliate->getIsDlr();
            $row['balance']      = $affiliate->getBalance();
            $row['status']       = $affiliate->getStatus()->getCode();
            $row['lastLogin']    = ($affiliate->getLastLoginAt()) ? $affiliate->getLastLoginAt()->format("Y-m-d H:i:sP") : null;
            $row['createdAt']    = $affiliate->getCreatedAt()->format("Y-m-d H:i:sP");
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
            return $this->services->no_access($this->intl->trans("Suppression d'un affilié").': '.$affiliate->getEmail());

        if ($affiliate->getId() == 1 or $affiliate->getBrand()->getManager() ==  $affiliate) 
        return $this->services->msg_warning(
            $this->intl->trans("Suppression de l'affilié ").$affiliate->getEmail(),
            $this->intl->trans("Vous ne pouvez pas supprimer cet affilié car il s'agit de l'administrateur de la marque active"),
        );

        //$affiliate->setStatus($this->services->status(3));
        $this->userRepository->remove($affiliate);
        return $this->services->msg_success(
            $this->intl->trans("Suppression de l'affilié ").$affiliate->getEmail(),
            $this->intl->trans("affilié supprimé avec succès").' : '.$affiliate->getEmail(),
        );
    }

    //affiliate retriving by permissions
    public function getAffiliatesByRoles() 
    {
        list($affiliateType, $masterId, $affiliateRequest) = $this->services->checkThisUser($this->pView);
        $affiliates = $this->userRepository->getUsersByPermission('',$affiliateType,$masterId, 2);
        return $affiliates;
    }

    public function statisticsData()
    {
        $allAffiliates     = $this->userRepository->countAllUsers()[0][1];
        $pendingAffiliates = $this->userRepository->countAllUsersByStatus(0)[0][1];
        $activeAffiliates  = $this->userRepository->countAllUsersByStatus(1)[0][1];
        $desactivatedAffiliates = $this->userRepository->countAllUsersByStatus(2)[0][1];
        $suspendedAffiliates = $this->userRepository->countAllUsersByStatus(3)[0][1];
        $deletedAffiliates = $this->userRepository->countAllUsersByStatus(4)[0][1];

        return [
            'all'          => $allAffiliates,
            'pending'      => $pendingAffiliates,
            'active'       => $activeAffiliates,
            'desactivated' => $desactivatedAffiliates,
            'suspended'    => $suspendedAffiliates,
            'deleted'      => $deletedAffiliates,
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
