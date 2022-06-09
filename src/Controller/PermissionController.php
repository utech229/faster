<?php

namespace App\Controller;

use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Entity\Permission;
use App\Form\PermissionType;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('/{_locale}/users/permissions')]
class PermissionController extends AbstractController
{
	public function __construct(BaseUrl $baseUrl, Services $services, EntityManagerInterface $entityManager, TranslatorInterface $translator,
    RoleRepository $roleRepository, UserRepository $userRepository, PermissionRepository $permissionRepository,StatusRepository $statusRepository,
    AuthorizationRepository $authorizationRepository, UrlGeneratorInterface $urlGenerator, uBrand $brand, ValidatorInterface $validator){
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
            "PER0", "PER1",  "PER2", "PER3", "PER4"
        ];
		$this->pAccessPermission	=	$this->services->checkPermission($this->permission[0]);
		$this->pCreatePermission	=	$this->services->checkPermission($this->permission[1]);
		$this->pListPermission	    =	$this->services->checkPermission($this->permission[2]);
		$this->pEditPermission	    =	$this->services->checkPermission($this->permission[3]);
		$this->pDeletePermission	=	$this->services->checkPermission($this->permission[4]);
	}

    #[Route('/', name: 'app_user_permission_index', methods: ['GET'])]
    #[Route('/add_permission', name: 'app_user_permission_add', methods: ['POST'])]
    #[Route('/{uid}/update_permission', name: 'app_user_permission_update', methods: ['POST'])]
    public function index(Request $request,Permission $permission = null, bool $isPermissionAdd = false): Response
    {
        if(!$this->pAccessPermission)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        } 
            
         /*----------MANAGE PERMISSION CRU BEGIN -----------*/
        //define permission if method is role add 
        (!$permission) ?  $isPermissionAdd = true : $isPermissionAdd = false;
        (!$permission) ?  $permission   = new Permission() : $permission;
       
        $form = $this->createForm(PermissionType::class, $permission);
        if ($request->request->count() > 0)
        {
            dd($request->request);
            $form->handleRequest($request);
            if ($isPermissionAdd == true) { //method calling
                if (!$this->pCreatePermission) return $this->services->no_access($this->intl->trans('Création de permission'));
                return $this->addPermission($request, $form, $permission);
            }else {
                if (!$this->pEditPermission)   return $this->services->no_access($this->intl->trans('Modification permission'));
                return $this->updatePermission($request, $form, $permission);
            }
        }

        $this->services->addLog($this->intl->trans('Accès au menu permission'));
        return $this->render('permission/index.html.twig', [
            'controller_name' => 'RoleController',
            'title'           => $this->intl->trans('Mes Permissions').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                'one'   => $this->intl->trans('Permissions'),
                'two'   => $this->intl->trans('Mes Permissions'),
                'none'  => $this->intl->trans('Gestion utilisateur'),
            ],
            'brand'              => $this->brand->get(),
            'baseUrl'            => $this->baseUrl->init(),
            'permissionform'     => $form->createView(),
            'pCreatePermission'  =>	$this->pCreatePermission,
            'pEditPermission'	 =>	$this->pEditPermission,
            'pDeletePermission'  =>	$this->pDeletePermission,
        ]);
    }

    //Add role function
    public function addpermission($request, $form, $permission): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {
        
        $isCheck = false;
        $isCheck = ($request->request->get('permission_core') !==  null) ? true : false;

        $description = $request->request->get('description');
        $permission->setUid($this->services->idgenerate(11));
        $permission->setStatus(1);
        $permission->setDescription($description);
        $permission->setIsCore($isCheck);
        $permission->setCreatedAt(new \DatetimeImmutable());
        $this->permissionRepository->add($permission);
        
        return $this->services->ajax_success_crud(
            $this->intl->trans("Ajout d'une nouvelle permission"),
            $this->intl->trans("Permission ajouté avec succès")
        );
        }
        else 
        {
            //return $this->services->invalidForm($form, $this->intl);
            return $this->services->formErrorsNotification($this->validator, $this->intl, $permission);
        }
        return $this->services->failedcrud($this->intl->trans("Ajout d'une nouvelle permission : "  .$request->request->get('permission_name')));
    }
 
    //update permission function
    public function updatepermission($request, $form, $permission): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {
        $key_        = $request->request->get('permission_id');
        $description = $request->request->get('description');
        $isCheck     = ($request->request->get('permission_core')) ? 1 : 0 ;
        $permission->setStatus(1);
        $permission->setDescription($description);
        $permission->setIsCore($isCheck);
        $permission->setUpdatedAt(new \DatetimeImmutable());
        $this->permissionRepository->add($permission);

        return $this->services->ajax_success_crud(
            $this->intl->trans("Modification de la permission ").$permission->getName(),
            $this->intl->trans("Permission modifié avec succès").' : '.$permission->getName()
        );
        }
        else 
        {
        //return $this->services->invalidForm($form, $this->intl);
        return $this->services->formErrorsNotification($this->validator, $this->intl, $permission);
        }
        return $this->services->failedcrud($this->intl->trans("Modification de la permission : "  .$request->request->get('permission_name')));
    }

    #[Route('/list', name: 'app_permission_list')]
    public function getUsers(TranslatorInterface $translator, Request $request, EntityManagerInterface $manager) : Response
    {
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) //Vérification du tokken
        return $this->redirectToRoute("app_home");

        $data = [];
        $permissions = (!$this->pListPermission) ? [] : $this->permissionRepository->findBy([],["createdAt"=>"DESC"]);
        
        foreach ($permissions  as $permission) 
		{
           
            $row                 = array();
            $row['OrderId']      = null;
            $row['Name']         = $permission->getName();
            $row['Code']         = $permission->getCode();
            $row['IsCore']       = $permission->getIsCore();
            $row['Description']  = $permission->getDescription();
            $row['Status']       = $permission->getStatus();
            $row['CreatedAt']    = $permission->getCreatedAt()->format("c");
            $row['UpdatedAt']    = ($permission->getUpdatedAt()) ? $permission->getUpdatedAt()->format("c") : $this->intl->trans('Non mdifié');
            $row['Roles']        = '';
            $row['Actions']      = $permission->getUid();
            $data []             = $row;
		}
        $this->services->addLog($translator->trans('Lecture de la liste des permissions'));
        $output = array("data" => $data);
        return new JsonResponse($output);
    }

    #[Route('/{uid}/get', name: 'app_permission_get', methods: ['POST'])]
    public function getCurrentPermission(Request $request, Permission $permission): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
        return $this->services->no_access($this->intl->trans("Récupération d'une permission"));

        $data['id']          = $permission->getId();
        $data['name']        = $permission->getName();
        $data['code']        = $permission->getCode();
        $data['description'] = $permission->getDescription();
        $data['iscore']      = $permission->getIsCore();
        $data['status']      = $permission->getStatus();
        return new JsonResponse(['data' => $data]);
    }

    #[Route('/{uid}/delete', name: 'app_permission_delete', methods: ['POST'])]
    public function delete(Request $request, Permission $permission): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
            return $this->services->no_access($this->intl->trans("Suppression d'une permission").': '.$permission->getCode());
        if (count($permission->getAuthorizations()) > 0) 
        return $this->services->ajax_error_crud(
            $this->intl->trans("Suppression de la permission ").$permission->getName(),
            $this->intl->trans("Vous ne pouvez pas supprimer une permission attribué").' : '.$permission->getName());
        $this->permissionRepository->remove($permission);
        return $this->services->ajax_success_crud(
            $this->intl->trans("Suppression de la permission ").$permission->getName(),
            $this->intl->trans("Permission supprimé avec succès").' : '.$permission->getName()
        );
    }

    
}
