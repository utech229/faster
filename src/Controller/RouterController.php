<?php

namespace App\Controller;

use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Entity\Router;
use App\Entity\Permission;
use App\Form\RouterType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use App\Repository\RouterRepository;
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
#[Route('/{_locale}/home/router')]
class RouterController extends AbstractController
{
	public function __construct(BaseUrl $baseUrl, Services $services, EntityManagerInterface $entityManager, TranslatorInterface $translator,
    RoleRepository $roleRepository, UserRepository $userRepository, RouterRepository $routerRepository,StatusRepository $statusRepository,
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
        $this->routerRepository         = $routerRepository;
        $this->statusRepository         = $statusRepository;
        $this->validator                = $validator;

        $this->permission = [
            "ROUT0", "ROUT1",  "ROUT2", "ROUT3", "ROUT4"
        ];
		$this->pAccess  =	$this->services->checkPermission($this->permission[0]);
		$this->pCreate  =	$this->services->checkPermission($this->permission[1]);
		$this->pList    =	$this->services->checkPermission($this->permission[2]);
		$this->pEdit	=	$this->services->checkPermission($this->permission[3]);
		$this->pDelete	=	$this->services->checkPermission($this->permission[4]);
	}

    #[Route('', name: 'app_router_index', methods: ['GET'])]
    #[Route('/add_router', name: 'app_router_add', methods: ['POST'])]
    #[Route('/{uid}/update_router', name: 'app_router_update', methods: ['POST'])]
    public function index(Request $request,Router $router = null, bool $isRouterAdd = false): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        } 
            
         /*----------MANAGE Router CRU BEGIN -----------*/
        //define Router if method is role add 
        (!$router) ?  $isRouterAdd = true : $isRouterAdd = false;
        (!$router) ?  $router   = new Router() : $router;
       
        $form = $this->createForm(RouterType::class, $router);
        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            if ($isRouterAdd == true) { //method calling
                if (!$this->pCreate) return $this->services->no_access($this->intl->trans('Création de route'));
                return $this->addRouter($request, $form, $router);
            }else {
                if (!$this->pEdit)   return $this->services->no_access($this->intl->trans('Modification route'));
                return $this->updateRouter($request, $form, $router);
            }
        }

        $this->services->addLog($this->intl->trans('Accès au menu route'));
        return $this->render('router/index.html.twig', [
            'controller_name' => 'RoleController',
            'title'           => $this->intl->trans('Mes routes').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans('Gestion routes')],
                [$this->intl->trans('Routes')],
            ],
            'brand'              => $this->brand->get(),
            'baseUrl'            => $this->baseUrl->init(),
            'routerform'         => $form->createView(),
            'pCreate'       =>	$this->pCreate,
            'pEdit'	     =>	$this->pEdit,
            'pDelete'       =>	$this->pDelete,
        ]);
    }

    //Add role function
    public function addRouter($request, $form, $router): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {

        $description = $request->request->get('description');
        $router->setUid($this->services->idgenerate(15));
        $router->setStatus($this->services->status(3));
        $router->setDescription($description);
        $router->setCreatedAt(new \DatetimeImmutable());
        $this->routerRepository->add($router);
        
        return $this->services->msg_success(
            $this->intl->trans("Ajout d'une nouvelle route"),
            $this->intl->trans("Route ajouté avec succès"));
        }
        else 
        {
            //return $this->services->invalidForm($form, $this->intl);
            return $this->services->formErrorsNotification($this->validator, $this->intl, $router);
        }
        return $this->services->failedcrud($this->intl->trans("Ajout d'une nouvelle route : "  .$request->request->get('router_name')));
    }
 
    //update route function
    public function updateRouter($request, $form, $router): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {
        $description = $request->request->get('description');
        $router->setStatus($this->services->status(3));
        $router->setDescription($description);
        $router->setUpdatedAt(new \DatetimeImmutable());
        $this->routerRepository->add($router);

        return $this->services->msg_success(
            $this->intl->trans("Modification de la route ").$router->getName(),
            $this->intl->trans("Route modifié avec succès").' : '.$router->getName()
        );
        }
        else 
        {
        //return $this->services->invalidForm($form, $this->intl);
        return $this->services->formErrorsNotification($this->validator, $this->intl, $router);
        }
        return $this->services->failedcrud($this->intl->trans("Modification de la route : "  .$request->request->get('router_name')));
    }

    #[Route('/list', name: 'app_router_list')]
    public function getUsers(TranslatorInterface $translator, Request $request, EntityManagerInterface $manager) : Response
    {
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) //Vérification du tokken
        return $this->redirectToRoute("app_home");

        $data = [];
        $routers = (!$this->pList) ? [] : $this->routerRepository->findBy([],["createdAt"=>"DESC"]);
        
        foreach ($routers  as $router) 
		{
            $row                 = array();
            $row['OrderId']      = null;
            $row['Name']         = $router->getName();
            $row['Description']  = $router->getDescription();
            $row['CreatedAt']    = $router->getCreatedAt()->format("c");
            $row['UpdatedAt']    = ($router->getUpdatedAt()) ? $router->getUpdatedAt()->format("c") : $this->intl->trans('Non mdifié');
            $row['Actions']      = $router->getUid();
            $data []             = $row;
		}
        $this->services->addLog($translator->trans('Lecture de la liste des routes'));
        $output = array("data" => $data);
        return new JsonResponse($output);
    }

    #[Route('/{uid}/get', name: 'app_router_get', methods: ['POST'])]
    public function getCurrentRouter(Request $request, Router $router): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
        return $this->services->no_access($this->intl->trans("Récupération d'une route"));

        $data['id']          = $router->getId();
        $data['name']        = $router->getName();
        $data['description'] = $router->getDescription();
        return new JsonResponse(['data' => $data]);
    }

    #[Route('/{uid}/delete', name: 'app_router_delete', methods: ['POST'])]
    public function delete(Request $request, Router $router): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
            return $this->services->no_access($this->intl->trans("Suppression d'une route").': '.$router->getCode());

        if (count($router->getUsers()) > 0) 
        return $this->services->msg_error(
            $this->intl->trans("Suppression de la route ").$router->getName(),
            $this->intl->trans("Vous ne pouvez pas supprimer une route utilisé").' : '.$router->getName());

        $this->routerRepository->remove($router);
        return $this->services->msg_success(
            $this->intl->trans("Suppression de la route ").$router->getName(),
            $this->intl->trans("Route supprimé avec succès").' : '.$router->getName()
        );
    }

    
}
