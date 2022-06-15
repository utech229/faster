<?php

namespace App\Controller;

use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Entity\Status;
use App\Entity\Permission;
use App\Form\StatusType;
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
#[Route('/{_locale}/home/status')]
class StatusController extends AbstractController
{
	public function __construct(BaseUrl $baseUrl, Services $services, EntityManagerInterface $entityManager, TranslatorInterface $translator,
    RoleRepository $roleRepository, UserRepository $userRepository,StatusRepository $statusRepository,
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
        $this->statusRepository         = $statusRepository;
        $this->validator                = $validator;

        $this->permission = [
            "STUT0", "STUT1",  "STUT2", "STUT3", "STUT4"
        ];
		$this->pAccess  =	$this->services->checkPermission($this->permission[0]);
		$this->pCreate  =	$this->services->checkPermission($this->permission[1]);
		$this->pList    =	$this->services->checkPermission($this->permission[2]);
		$this->pEdit	=	$this->services->checkPermission($this->permission[3]);
		$this->pDelete	=	$this->services->checkPermission($this->permission[4]);
	}

    #[Route('', name: 'app_status_index', methods: ['GET'])]
    #[Route('/add_status', name: 'app_status_add', methods: ['POST'])]
    #[Route('/{uid}/update_status', name: 'app_status_update', methods: ['POST'])]
    public function index(Request $request, Status $status = null, bool $isStatusrAdd = false): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        } 
            
         /*----------MANAGE Router CRU BEGIN -----------*/
        //define Router if method is status add 
        (!$status) ?  $isStatusrAdd = true : $isStatusrAdd = false;
        (!$status) ?  $status   = new Status() : $status;
       
        $form = $this->createForm(StatusType::class, $status);
        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            if ($isStatusrAdd == true) { //method calling
                if (!$this->pCreate) return $this->services->no_access($this->intl->trans('Création de status'));
                return $this->addStatus($request, $form, $status);
            }else {
                if (!$this->pEdit)   return $this->services->no_access($this->intl->trans('Modification status'));
                return $this->updateStatus($request, $form, $status);
            }
        }

        $this->services->addLog($this->intl->trans('Accès au menu status'));
        return $this->render('status/index.html.twig', [
            'controller_name' => 'RoleController',
            'title'           => $this->intl->trans('Status Système').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans('Gestion status')],
                [$this->intl->trans('Status')],
            ],
            'brand'              => $this->brand->get(),
            'baseUrl'            => $this->baseUrl->init(),
            'statusform'         => $form->createView(),
            'pCreate'       =>	$this->pCreate,
            'pEdit'	     =>	$this->pEdit,
            'pDelete'       =>	$this->pDelete,
        ]);
    }

    //Add role function
    public function addStatus($request, $form, $status): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {

        $description = $request->request->get('description');
        $status->setUid($this->services->idgenerate(15));
       
        $status->setDescription($description);
        $status->setCreatedAt(new \DatetimeImmutable());
        $this->statusRepository->add($status);
        
        return $this->services->msg_success(
            $this->intl->trans("Ajout d'une nouvelle route"),
            $this->intl->trans("Status ajouté avec succès"));
        }
        else 
        {
            //return $this->services->invalidForm($form, $this->intl);
            return $this->services->formErrorsNotification($this->validator, $this->intl, $status);
        }
        return $this->services->failedcrud($this->intl->trans("Ajout d'une nouvelle Status : "  .$request->request->get('status_name')));
    }
 
    //update Status function
    public function updateStatus($request, $form, $status): JsonResponse
    {
       
        if ($form->isSubmitted() && $form->isValid()) {
            $description = $request->request->get('description');
            $status->setDescription($description);
            $status->setUpdatedAt(new \DatetimeImmutable());
            $this->statusRepository->add($status);

            return $this->services->msg_success(
                $this->intl->trans("Modification de la Status ").$status->getName(),
                $this->intl->trans("Status modifié avec succès").' : '.$status->getName()
            );
        }
        else 
        {
        //return $this->services->invalidForm($form, $this->intl);
        //return $this->services->invalidForm($form)
        return $this->services->formErrorsNotification($this->validator, $status);
        }
    
    }

    #[Route('/list', name: 'app_status_list')]
    public function getUsers(TranslatorInterface $translator, Request $request, EntityManagerInterface $manager) : Response
    {
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) //Vérification du tokken
        return $this->redirectToRoute("app_home");

        $data = [];
        $statuss = (!$this->pList) ? [] : $this->statusRepository->findBy([],["createdAt"=>"DESC"]);
        
        foreach ($statuss  as $status) 
		{
            $row                 = array();
            $row['OrderId']      = null;
            $row['Name']         = $status->getName();
            $row['Code']         = $status->getCode();
            $row['Description']  = $status->getDescription();
            $row['CreatedAt']    = $status->getCreatedAt()->format("Y-m-d H:i:sP");
            $row['UpdatedAt']    = ($status->getUpdatedAt()) ? $status->getUpdatedAt()->format("Y-m-d H:i:sP") : $this->intl->trans('Non mdifié');
            $row['Actions']      = $status->getUid();
            $data []             = $row;
		}
        $this->services->addLog($translator->trans('Lecture de la liste des routes'));
        $output = array("data" => $data);
        return new JsonResponse($output);
    }

    #[Route('/{uid}/get', name: 'app_status_get', methods: ['POST'])]
    public function getCurrentStatus(Request $request, Status $status): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
        return $this->services->no_access($this->intl->trans("Récupération d'une route"));

        $data['id']          = $status->getId();
        $data['name']        = $status->getName();
        $data['code']        = $status->getCode();
        $data['description'] = $status->getDescription();
        return new JsonResponse(['data' => $data]);
    }

    #[Route('/{uid}/delete', name: 'app_status_delete', methods: ['POST'])]
    public function delete(Request $request, Status $status): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
            return $this->services->no_access($this->intl->trans("Suppression d'une route").': '.$status->getCode());

        if (count($status->getUsers()) > 0) 
        return $this->services->msg_error(
            $this->intl->trans("Suppression de la Status ").$status->getName(),
            $this->intl->trans("Vous ne pouvez pas supprimer une Status utilisé").' : '.$status->getName());

        $this->statusRepository->remove($status);
        return $this->services->msg_success(
            $this->intl->trans("Suppression de la Status ").$status->getName(),
            $this->intl->trans("Status supprimé avec succès").' : '.$status->getName()
        );
    }

    
}
