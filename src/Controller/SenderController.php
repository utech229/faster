<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Sender;
use App\Entity\Status;
use App\Entity\Brand;
use App\Service\uBrand;
use App\Service\Services;
use App\Form\SenderType;
use App\Repository\SenderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('/{_locale}/sender')]
class SenderController extends AbstractController
{
    public function __construct(TranslatorInterface $intl, uBrand $brand, Services $src, UrlGeneratorInterface $ug, EntityManagerInterface $em)
	{
       $this->intl          = $intl;
       $this->brand         = $brand;
       $this->src           = $src;
       $this->ug            = $ug;
       $this->em            = $em;
       $this->permission    = [
           "SEND0", "SEND1",  "SEND2", "SEND3", "SEND4", "SEND5"
       ];
       $this->pAccess   =	$this->src->checkPermission($this->permission[0]);
       $this->pCreate   =	$this->src->checkPermission($this->permission[1]);
       $this->pList     =	$this->src->checkPermission($this->permission[2]);
       $this->pEdit	    =	$this->src->checkPermission($this->permission[3]);
       $this->pDelete	=	$this->src->checkPermission($this->permission[4]);
       $this->pStatus	=	$this->src->checkPermission($this->permission[5]);
    }

    #[Route('', name: 'sender', methods: ['GET'])]
    #[Route('/new', name: 'sender_new', methods: ['POST'])]
    #[Route('/{uid}/edit', name: 'sender_edit', methods: ['GET', 'POST'])]
    public function index(Request $request, Sender $sender = null, SenderRepository $senderRepository): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            $this->src->addLog($this->intl->trans("Acces refusé à Sender"), 417);
            return $this->redirectToRoute("app_home");
        }
        else if ($request->request->get('_token') && !$this->isCsrfTokenValid('sender', $request->request->get('_token')))
            return $this->src->no_access($this->intl->trans("Actions sur sender bloquées à l'utilisateur."));

        if(!$sender && $request->request->get("uid", null)) return $this->src->msg_error(
            $this->intl->trans("Action bloquée sur sender. (Erreur de requête)"),
            $this->intl->trans("Une erreur fatale dans la requête. Rechargez vous page."), []
        );

        if(!$sender) $sender = new Sender();

        $form = $this->createForm(SenderType::class, $sender);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if($sender->getUid()){
                $task = $this->intl->trans("Mise à jour du Sender : ".$sender->getName());
                if(!$this->pEdit) return $this->src->no_access($this->intl->trans("Acces refusé à la modification du sender : ".$sender->getName()));
                $message = $this->intl->trans("Mise à jour de l'identifiant effectuée.");
                $sender->setUpdatedAt(new \DateTimeImmutable());
            }else{
                $sender->setUid($this->src->getUniqid())->setCreatedAt(new \DateTimeImmutable())->setCreateBy($this->getUser());
                $task = $this->intl->trans("Création du Sender : ".$sender->getName());
                if(!$this->pCreate) return $this->src->no_access($this->intl->trans("Acces refusé à l'ajout du sender : ".$sender->getName()));
                $message = $this->intl->trans("Identifiant créé avec succès.");
            }

            $senderRepository->add($sender, true);

            if($request->isXmlHttpRequest()) return $this->src->msg_success($task , $message, []);

            $this->addFlash('success', $message);
            $this->src->addLog($task, 200);

            return $this->redirectToRoute('sender', [], Response::HTTP_SEE_OTHER);
        }

        if($form->isSubmitted() && $request->isXmlHttpRequest()) return $this->src->invalidForm($form);

        list($userType, $userRequest) = $this->src->checkThisUser($this->pList);
        $status = [
            $this->src->status(2),
            $this->src->status(3),
            $this->src->status(4),
        ];
        switch ($userType) {
            case 0:
                array_push($status, $this->src->status(5));
                break;
            case 1:
                array_push($status, $this->src->status(5));
                break;
            default: break;
        }
        $params = [];
        $merge = array_merge($params, $userRequest);
        $brands = $this->em->getRepository(Brand::class)->findBrandBy($userType, $merge);
        return $this->renderForm('sender/index.html.twig', [
            'form'      => $form,
            'sender'    => $sender,
            'brands'     => $brands,
            'status'    => $status,
            'brand'     => $this->brand->get(),
            'pAccess'   => $this->pAccess,
            'pCreate'   => $this->pCreate,
            'clnUser'     => $userType > 3 ? false : true,
            'pList'     => $this->pList,
            'pEdit'     => $this->pEdit,
            'pDelete'   => $this->pDelete,
            'pStatus'   => $this->pStatus,
            'pageTitle' => []
        ]);
    }

    #[Route('/all', name: 'sender_all', methods: ['POST'])]
    public function all(Request $request): JsonResponse
    {
        $session = $this->getUser();
        if (!$this->isCsrfTokenValid('sender', $request->request->get('_token')))
            return $this->src->no_access($this->intl->trans("Récupération des senders bloquée. (Erreur de requête)"));

        if(!$this->pAccess) return $this->src->no_access($this->intl->trans("Acces refusé à la récupération des senders"));

        $senders = [];
        $request_sender = [];

        $user_manage = ($request->request->get('manager') !== "") ? $this->em->getRepository(User::class)->findOneByUid($request->request->get('manager')) : null;
        if ($request->request->get('manager', null) && !$user_manage) return $this->src->msg_error(
            $this->intl->trans("Utilisateur inconnu : uid=".$request->request->get('manager')),
            $this->intl->trans("Utilisateur inconnu"),
            [
                "table"=>[],
                "permission"=>[
                    'pAccess'   => $this->pAccess,
                    'pCreate'   => $this->pCreate,
                    'pList'     => $this->pList,
                    'pEdit'     => $this->pEdit,
                    'pDelete'   => $this->pDelete,
                    'pStatus'   => $this->pStatus,
                ]
            ]
        );

        $brand = ($request->request->get('brand') !== "") ? $this->em->getRepository(Brand::class)->findOneByUid($request->request->get('brand')) : null;
        if($brand) $request_sender["brand"] = $brand->getId();

        $status = ($request->request->get('status') !== "") ? $this->em->getRepository(Status::class)->findOneByUid($request->request->get('status')) : null;
        if($status) $request_sender["status"] = $status->getId();

        list($userType, $userRequest) = $this->src->checkThisUser($this->pList);

        $merge = array_merge($request_sender, $userRequest);

        $senders = $this->em->getRepository(Sender::class)->userTypeFindBy($userType, $merge);

        $data = [];

        foreach ($senders as $key => $sender) {
            $data[$key][] = '';
            $data[$key][] = $sender->getName();
            $data[$key][] = [
                "name"=>$sender->getStatus()->getName(),
                "label"=>$sender->getStatus()->getLabel(),
                "code"=>$sender->getStatus()->getCode(),
                "uid"=>$sender->getStatus()->getUid(),
            ];
            $data[$key][] = $sender->getCreatedAt()->format("Y-m-d H:i:sP");
            $data[$key][] = $sender->getManager() ? [$sender->getManager()->getEmail(), $sender->getManager()->getUid()] : ["",""];
            $data[$key][] = $sender->getUpdatedAt() ? $sender->getUpdatedAt()->format("Y-m-d H:i:sP") : '';
            $data[$key][] = $sender->getObservation();
            $data[$key][] = $sender->getUid();
        }

        $manager_email = $user_manage ? $user_manage->getEmail() : "tous";

        return $this->src->msg_success(
            $this->intl->trans("Récupération des senders de ".$manager_email),
            "",
            [
                "table"=>$data,
                "permission"=>[
                    'pAccess'   => $this->pAccess,
                    'pCreate'   => $this->pCreate,
                    'pList'     => $this->pList,
                    'pEdit'     => $this->pEdit,
                    'pDelete'   => $this->pDelete,
                    'pStatus'   => $this->pStatus,
                ]
            ]
        );
    }

    #[Route('/{uid}/show', name: 'sender_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Sender $sender, SenderRepository $senderRepository): Response
    {
        return $this->src->msg_success(
            $this->intl->trans("Récupération des senders de ".$manager_email),
            "",
            []
        );
    }

    #[Route('/user/get', name: 'sender_user', methods: ['POST'])]
    public function user(Request $request): Response
    {
        $data = [
			"results"=>[
				["id"=>"", "text"=>""],
			]
		];
        if (!$this->isCsrfTokenValid('sender', $request->request->get('_token'))){
            $this->src->addLog($this->intl->trans("Actions sur sender bloquées à l'utilisateur (Erreur de requête)."));
            return new JsonResponse($data);
        }

        $brand = $this->em->getRepository(Brand::class)->findOneByUid($request->request->get("brand", "IwyXLGn0gH"));

        if($brand) $users = $this->em->getRepository(User::class)->getUsersByPermission("", 2, $brand->getManager()->getId(), 1);
        else $users = $this->em->getRepository(User::class)->getUsersByPermission("", null, null, 1);

        foreach ($users as $key => $user) {
            $data["results"][] = [
				"id"=>$user->getUid(),
				"text"=>$user->getEmail(),
			];
        }

        return new JsonResponse($data);
    }

    #[Route('/{uid}', name: 'sender_action', methods: ['GET', 'POST'])]
    public function action(Request $request, Sender $sender, SenderRepository $senderRepository): Response
    {
        if (!$this->isCsrfTokenValid('sender', $request->request->get('_token')))
            return $this->src->no_access($this->intl->trans("Actions sur sender bloquées à l'utilisateur (Erreur de requête)."));

        $action = (int)$request->request->get("action");

        if(!in_array($action, [0,1,2])) return $this->src->msg_error(
            $this->intl->trans("Acces refusé. (Erreur de requête)"),
            $this->intl->trans("Une erreur fatale dans la requête. Rechargez vous page."), []
        );

        switch ($action) {
            case 1: $action_text = $this->intl->trans("activation"); break;
            case 2: $action_text = $this->intl->trans("suppression"); break;
            default: $action_text = $this->intl->trans("désactivation"); break;
        }

        if(!$this->pAccess || (!$this->pEdit && $action === 0) || (!$this->pEdit && $action === 1) || (!$this->pDelete && $action === 2))
            return $this->src->no_access($this->intl->trans("Acces refusé pour %1% d'un identifiant d'envoi.", ["%1%"=>$action_text])." ".$sender->getName());

        if(!$sender && $request->request->get("uid", null)) return $this->src->msg_error(
            $this->intl->trans("Action bloquée sur sender. (Erreur de requête)"),
            $this->intl->trans("Une erreur fatale dans la requête. Rechargez vous page."), []
        );

        $sender->setUpdatedAt(new \DateTimeImmutable());

        switch ($action) {
            case 1: $sender->setStatus($this->src->status(3)); break;
            case 2: $sender->setStatus($this->src->status(5)); break;
            default: $sender->setStatus($this->src->status(4)); break;
        }

        $senderRepository->add($sender, true);

        return $this->src->msg_success(
            $this->intl->trans("%1% du sender %2%.", ["%1%"=>ucfirst($action_text), "%2%"=>$sender->getName()]),
            $this->intl->trans("%1% de l'identifiant effectuée avec succès.", ["%1%"=>ucfirst($action_text)]),
            []
        );
    }
}
