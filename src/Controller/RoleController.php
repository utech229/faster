<?php

namespace App\Controller;

use App\Entity\Role;
use App\Form\RoleType;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Entity\Authorization;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AuthorizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('/{_locale}/users/role')]
class RoleController extends AbstractController
{
    private $em;
	private $status;
	private $intl;
	private $services;
	private $pAcessRole;
	private $pCreateRole;
	private $pListRole;
	private $pEditRole;
	private $pDeleteRole;
	private $UrlGenerator;
    

	public function __construct(BaseUrl $baseUrl, Services $services, EntityManagerInterface $entityManager, TranslatorInterface $translator,
    RoleRepository $roleRepository, UserRepository $userRepository, PermissionRepository $permissionRepository,
    AuthorizationRepository $authorizationRepository, UrlGeneratorInterface $urlGenerator, uBrand $brand, ValidatorInterface $validator,
    StatusRepository $statusRepository){
		$this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->userRepository  = $userRepository;
        $this->roleRepository           = $roleRepository;
        $this->authorizationRepository  = $authorizationRepository;
        $this->permissionRepository     = $permissionRepository;
        $this->statusRepository         = $statusRepository;
        $this->validator                = $validator;

        $this->permission = [
            "PER0", "PER1",  "PER2", "PER3", "PER4",
            "ROL0", "ROL1",  "ROL2", "ROL3", "ROL4",
        ];
		$this->pAccessPermission	=	$this->services->checkPermission($this->permission[0]);
		$this->pCreatePermission	=	$this->services->checkPermission($this->permission[1]);
		$this->pListPermission	    =	$this->services->checkPermission($this->permission[2]);
		$this->pEditPermission	    =	$this->services->checkPermission($this->permission[3]);
		$this->pDeletePermission	=	$this->services->checkPermission($this->permission[4]);
		$this->pAccessRole	        =	$this->services->checkPermission($this->permission[5]);
		$this->pCreateRole		    =	$this->services->checkPermission($this->permission[6]);
		$this->pListRole			=	$this->services->checkPermission($this->permission[7]);
		$this->pEditRole			=	$this->services->checkPermission($this->permission[8]);
		$this->pDeleteRole		    =	$this->services->checkPermission($this->permission[9]);
	}

    #[Route('/', name: 'app_user_role', methods: ['GET'])]
    #[Route('/add_role', name: 'app_user_role_add', methods: ['POST'])]
    #[Route('/{code}/update_role', name: 'app_user_role_update', methods: ['POST'])]
    public function index(Request $request, Role $newRole = null, bool $isRoleAdd = false): Response
    {
        if(!$this->pAccessRole)  {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

        /*----------MANAGE ROLE CRU BEGIN -----------*/
        //define role if method is role add 
        (!$newRole) ?  $isRoleAdd = true : $isRoleAdd = false;
        (!$newRole) ?  $newRole   = new Role() : $newRole;
       
        $form = $this->createForm(RoleType::class, $newRole);
        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            if ($isRoleAdd == true) { //method calling
                if (!$this->pCreateRole) return $this->services->no_access($this->intl->trans('Création de rôle'));
                return $this->addRole($request, $form, $newRole);
            }else {
                if (!$this->pEditRole)   return $this->services->no_access($this->intl->trans('Modification rôle'));
                return $this->updateRole($request, $form, $newRole);
            }
        }
        /*----------MANAGE ROLE CRU END -----------*/
        $RPA  = $this->rolesPermissionAuthorizationData();
        return $this->render('role/index.html.twig', [
            'controller_name' => 'RoleController',
            'title'           => $this->intl->trans('Mes Roles').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans('Gestion utilisateurs')],
                [$this->intl->trans('Roles')],
            ],
            'brand'           => $this->brand->get(),
            'baseUrl'         => $this->baseUrl->init(),
            'roleform'        => $form->createView(),
            'roles'           =>  $RPA['roles'],
            'permissions'     =>  $RPA['permissions'],
            'totalUsersByRole' => $RPA['totalUsersByRole'],
            'roleProperty'          => json_encode($RPA['roleProperty']),
            'permissionProperty'    => json_encode($RPA['permissionProperty']),
            'authorizationByRole'   => json_encode($RPA['authorizationByRole']),
            'pCreateRole'		 =>	$this->pCreateRole,
            'pEditRole'			 =>	$this->pEditRole,
            'pDeleteRole'		 =>	$this->pDeleteRole,
        ]);
    }

    #[Route('/view', name: 'app_user_role_view', methods: ['POST'])]
    public function view(Request $request): Response
    {
        if(!$this->pAccessRole) return $this->redirectToRoute("home");
        $submittedToken = $request->request->get('_token');
        // 'delete-item' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('view-role', $submittedToken) && $request->request->get('code_name')) 
        {
            $RPA = $this->rolePermissionAuthorizationData($request);

            $role = $RPA['role'];

            $form = $this->createForm(RoleType::class, $role);

            
            return $this->render('role/show.html.twig', [
                'controller_name' => 'RoleController',
                'title'           => $this->intl->trans('Mon Role').' - '. $this->brand->get()['name'],
                'pageTitle'       => [
                    [$this->intl->trans('Gestion rôles'), $this->urlGenerator->generate('app_user_role') ],
                    [$this->intl->trans('Rôle')],
                ],
                'roleform'         => $form->createView(),
                'brand'            => $this->brand->get(),
                'baseUrl'          => $this->baseUrl->init(),
                'role'             =>  $role,
                'permissions'      =>  $RPA['permissions'],
                'totalUsersByRole' =>  $RPA['totalUsersByRole'],
                'roleProperty'          => json_encode($RPA['roleProperty']),
                'permissionProperty'    => json_encode($RPA['permissionProperty']),
                'authorizationByRole'   => json_encode($RPA['authorizationByRole']),
                'pCreateRole'		=>	$this->pCreateRole,
                'pEditRole'		    =>	$this->pEditRole,
                'pDeleteRole'   	=>	$this->pDeleteRole,
                
            ]);
        }
        return $this->redirectToRoute('app_logout');
    }

   
    //Add role function
    public function addRole($request, $form, $role): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {

            $description = $request->request->get('description');
            $role->setStatus($this->services->status(3));
            $role->setDescription($description);
            $role->setCreatedAt(new \DatetimeImmutable());
            $this->roleRepository->add($role);

            //update authorization for role and permission
            $this->authorizationUpdate($request, $role);
            
            return $this->services->msg_success(
                $this->intl->trans("Ajout d'un nouveau role"),
                $this->intl->trans("Nouveau role ajouté avec succès")
            );
        }
        else 
        {
            //return $this->services->invalidForm($form, $this->intl);
            return $this->services->formErrorsNotification($this->validator, $this->intl, $role);
        }
        return $this->services->failedcrud($this->intl->trans("Ajout de nouveau role : "  .$request->request->get('role_name')));
    }

    //update role function
    public function updateRole($request, $form, $role): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $key_ = $request->request->get('role_id');
            $description = $request->request->get('description');
            $role->setDescription($description);
            $this->roleRepository->add($role);

            //update authorization for role and permission
            $this->authorizationUpdate($request, $role);
        
            return $this->services->msg_success(
                $this->intl->trans("Modification du role ").$role->getName(),
                $this->intl->trans("Role modifié avec succès").' : '.$role->getName()
            );
        }
        else 
        {
            return $this->services->invalidForm($form, $this->intl);
        }
        return $this->services->failedcrud($this->intl->trans("Modification du role : "  .$request->request->get('role_name')));
    }


    #[Route('/{code}/delete', name: 'app_user_role_delete', methods: ['POST'])]
    public function delete(Request $request, Role $role): Response
    {
        if (!$this->pDeleteRole)  return $this->services->no_access($this->intl->trans('Suppression du rôle').' : '.$role->getName());
        if (count($role->getUsers()) > 0 ) return $this->services->ajax_warning_crud(
            $this->intl->trans("Echec de suppression du role ").$role->getName(),
            $this->intl->trans("Vous ne pouvez pas supprimmer un rôle ayant un ou plusieurs utilisateur(s) associé(s)")
        );
        
        if ($this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
        {
            $this->roleRepository->remove($role);
            return $this->services->msg_success(
                $this->intl->trans("Suppression réussie du rôle").' '.$role->getName(),
                $this->intl->trans("Suppression du rôle effectué succès").': Role : '.$role->getName()
            );
        }
        return $this->services->ajax_error_crud(
            $this->intl->trans("Echec de suppression du role ").$role->getName(),
            $this->intl->trans("Echec de suppression du role ").$role->getName()
        );
    }

    public function rolePermissionAuthorizationData($request)
    {
        $authorizationByRole = [];
        $roleProperty        = [];
        $permissionProperty  = [];
        $temp                = [];

        $role        = $this->roleRepository->findOneByCode($request->request->get('code_name'));
        $permissions = $this->permissionRepository->findAll();
        $tempRole    = [$role->getId(), $role->getCode(), $role->getName(),$role->getDescription(), $role->getLevel()];
        foreach($role->getAuthorizations() as $autorization  )
        {
            if ($autorization->getStatus()->getCode() == 3) 
            $temp[$autorization->getPermission()->getCode()] = $autorization->getPermission()->getCode();
        }
        
        $authorizationByRole[$role->getCode()] = json_encode($temp);
        $roleProperty[$role->getCode()]        = $tempRole;
        $totalUsersByRole[$role->getId()]      = count($role->getUsers());

        foreach($permissions as $perm )
        {
            $permissionProperty[$perm->getCode()] = [$perm->getId(), $perm->getCode(), $perm->getName()];
        }

        return [
            'role'                  => $role,
            'permissions'           => $permissions,
            'authorizationByRole'   => $authorizationByRole,
            'roleProperty'          => $roleProperty ,
            'permissionProperty'    => $permissionProperty,
            'totalUsersByRole'      => $totalUsersByRole,
        ];
    }

    public function rolesPermissionAuthorizationData()
    {
        $roles       = $this->roleRepository->findAll();
        $permissions = $this->permissionRepository->findAll();
        $totalUsersByRole    = [];
        $authorizationByRole = [];
        $roleProperty        = [];
        $permissionProperty  = [];
        foreach($roles as $index => $role)
        {
            $temp = [];
            $tempRole = [$role->getId(), $role->getCode(), $role->getName(),$role->getDescription(), $role->getLevel()];
            foreach($role->getAuthorizations() as $autorization )
            {
                if ($autorization->getStatus()->getCode() == 3) 
                $temp[$autorization->getPermission()->getCode()] = $autorization->getPermission()->getCode(); 
            }
            $authorizationByRole[$role->getCode()] = json_encode($temp);
            $roleProperty[$role->getCode()]        = $tempRole;
            $totalUsersByRole[$role->getId()]      = count($role->getUsers());
        }

        foreach($permissions as $perm)
        {
            $permissionProperty[$perm->getCode()] = [$perm->getId(), $perm->getCode(), $perm->getName()];
        }

        $data = [
            'roles'                 => $roles,
            'permissions'           => $permissions,
            'authorizationByRole'   => $authorizationByRole,
            'roleProperty'          => $roleProperty ,
            'permissionProperty'    => $permissionProperty,
            'totalUsersByRole'      => $totalUsersByRole,
        ];

        return $data;
    }

    public function authorizationUpdate($request, $role):Void
    {
        $permissions = $this->permissionRepository->findAll();
        foreach ($permissions as $key => $permission) 
        {
            $isCheck = false;
            $isCheck = ($request->request->get($permission->getCode()) !==  null) ? true : false;
            $authorization = $this->authorizationRepository->findOneBy(['role' => $role,'permission' => $permission]);
            
            if($authorization && $isCheck)
            {
                $authorization->setStatus($this->services->status(3))->setUpdatedAt(new \DateTimeImmutable());
            }
            elseif($authorization && !$isCheck)
            {
                $authorization->setStatus($this->services->status(4))->setUpdatedAt(new \DateTimeImmutable())->setUpdatedAt(new \DateTimeImmutable());
            }
            elseif(!$authorization && $isCheck)
            {
                $authorization = new Authorization();
                $authorization->setStatus($this->services->status(3))
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setUpdatedAt(null)
                    ->setDescription($request->request->get('description')." - ".$permission->getDescription())
                    ->setRole($role)
                    ->setPermission($permission);
                
            }
            ($authorization) ?  $this->authorizationRepository->add($authorization) : '';
        }
    }



    
}
