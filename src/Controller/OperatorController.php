<?php

namespace App\Controller;

use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Entity\Operator;
use App\Entity\Permission;
use App\Form\OperatorType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use App\Repository\OperatorRepository;
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
#[Route('/{_locale}/home/operator')]
class OperatorController extends AbstractController
{
	public function __construct(BaseUrl $baseUrl, Services $services, EntityManagerInterface $entityManager, TranslatorInterface $translator,
    RoleRepository $roleRepository, UserRepository $userRepository, OperatorRepository $operatorRepository,StatusRepository $statusRepository,
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
        $this->OperatorRepository         = $operatorRepository;
        $this->statusRepository         = $statusRepository;
        $this->validator                = $validator;

        $this->permission = [
            "OPRT0", "OPRT1",  "OPRT2", "OPRT3", "OPRT4"
        ];
		$this->pAccess  =	$this->services->checkPermission($this->permission[0]);
		$this->pCreate  =	$this->services->checkPermission($this->permission[1]);
		$this->pList    =	$this->services->checkPermission($this->permission[2]);
		$this->pEdit	=	$this->services->checkPermission($this->permission[3]);
		$this->pDelete	=	$this->services->checkPermission($this->permission[4]);
	}

    #[Route('', name: 'app_operator_index', methods: ['GET'])]
    #[Route('/add_operator', name: 'app_operator_add', methods: ['POST'])]
    #[Route('/{uid}/update_operator', name: 'app_operator_update', methods: ['POST'])]
    public function index(Request $request,Operator $operator = null, bool $isOperatorAdd = false): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        } 
            
         /*----------MANAGE Operator CRU BEGIN -----------*/
        //define Operator if method is role add 
        (!$operator) ?  $isOperatorAdd = true : $isOperatorAdd = false;
        (!$operator) ?  $operator   = new Operator() : $operator;
       
        $form = $this->createForm(OperatorType::class, $operator);
        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            if ($isOperatorAdd == true) { //method calling
                if (!$this->pCreate) return $this->services->no_access($this->intl->trans('Création de route'));
                return $this->addOperator($request, $form, $operator);
            }else {
                if (!$this->pEdit)   return $this->services->no_access($this->intl->trans('Modification route'));
                return $this->updateOperator($request, $form, $operator);
            }
        }

        $this->services->addLog($this->intl->trans('Accès au menu opérateur'));
        return $this->render('operator/index.html.twig', [
            'controller_name' => 'RoleController',
            'title'           => $this->intl->trans('Mes opérateurs').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans('Gestion opérateurs')],
                [$this->intl->trans('opérateurs')],
            ],
            'brand'              => $this->brand->get(),
            'baseUrl'            => $this->baseUrl->init(),
            'operatorform'       => $form->createView(),
            'pCreate'            =>	$this->pCreate,
            'pEdit'	             =>	$this->pEdit,
            'pDelete'            =>	$this->pDelete,
        ]);
    }

    //Add role function
    public function addOperator($request, $form, $operator): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {

        $description = $request->request->get('description');
        $operator->setUid($this->services->idgenerate(15));
        $operator->setStatus($this->services->status(3));
        $operator->setDescription($description);
        $operator->setCreatedAt(new \DatetimeImmutable());
        $this->OperatorRepository->add($operator);
        
        return $this->services->msg_success(
            $this->intl->trans("Ajout d'une nouvelle opérateur"),
            $this->intl->trans("Opérateur ajouté avec succès"));
        }
        else 
        {
            return $this->services->formErrorsNotification($this->validator, $this->intl, $operator);
        }
        return $this->services->failedcrud($this->intl->trans("Ajout d'une nouvelle opérateur : "  .$request->request->get('operator_name')));
    }
 
    //update opérateur function
    public function updateOperator($request, $form, $operator): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {
        $description = $request->request->get('description');
        $operator->setStatus($this->services->status(3));
        $operator->setDescription($description);
        $operator->setUpdatedAt(new \DatetimeImmutable());
        $this->OperatorRepository->add($operator);

        return $this->services->msg_success(
            $this->intl->trans("Modification de la opérateur ").$operator->getName(),
            $this->intl->trans("Opérateur modifié avec succès").' : '.$operator->getName()
        );
        }
        else 
        {
        //return $this->services->invalidForm($form, $this->intl);
        return $this->services->formErrorsNotification($this->validator, $this->intl, $operator);
        }
        return $this->services->failedcrud($this->intl->trans("Modification de la opérateur : "  .$request->request->get('operator_name')));
    }

    #[Route('/list', name: 'app_operator_list')]
    public function getUsers(TranslatorInterface $translator, Request $request, EntityManagerInterface $manager) : Response
    {
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) //Vérification du tokken
        return $this->redirectToRoute("app_home");

        $data = [];
        $operators = (!$this->pList) ? [] : $this->OperatorRepository->findBy([],["createdAt"=>"DESC"]);
        
        foreach ($operators  as $operator) 
		{
            $row                 = array();
            $row['OrderId']      = null;
            $row['Name']         = $operator->getName();
            $row['Description']  = $operator->getDescription();
            $row['CreatedAt']    = $operator->getCreatedAt()->format("Y-m-d H:i:sP");
            $row['UpdatedAt']    = ($operator->getUpdatedAt()) ? $operator->getUpdatedAt()->format("Y-m-d H:i:sP") : $this->intl->trans('Non modifié');
            $row['Actions']      = $operator->getUid();
            $data []             = $row;
		}
        $this->services->addLog($translator->trans('Lecture de la liste des routes'));
        $output = array("data" => $data);
        return new JsonResponse($output);
    }

    #[Route('/{uid}/get', name: 'app_operator_get', methods: ['POST'])]
    public function getCurrentOperator(Request $request, Operator $operator): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
        return $this->services->no_access($this->intl->trans("Récupération d'une route"));

        $data['id']          = $operator->getId();
        $data['name']        = $operator->getName();
        $data['description'] = $operator->getDescription();
        return new JsonResponse(['data' => $data]);
    }

    #[Route('/{uid}/delete', name: 'app_operator_delete', methods: ['POST'])]
    public function delete(Request $request, Operator $operator): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
            return $this->services->no_access($this->intl->trans("Suppression d'une route").': '.$operator->getCode());

        if (count($operator->getUsers()) > 0) 
        return $this->services->msg_error(
            $this->intl->trans("Suppression de la route ").$operator->getName(),
            $this->intl->trans("Vous ne pouvez pas supprimer une route utilisé").' : '.$operator->getName());

        $this->OperatorRepository->remove($operator);
        return $this->services->msg_success(
            $this->intl->trans("Suppression de la route ").$operator->getName(),
            $this->intl->trans("Route supprimé avec succès").' : '.$operator->getName()
        );
    }

    
}
