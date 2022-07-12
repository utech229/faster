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
		$this->intl			= $intl;
		$this->brand		= $brand;
		$this->src			= $src;
		$this->ug			= $ug;
		$this->em			= $em;
		$this->permission	= [
			"SEND0", "SEND1",  "SEND2", "SEND3", "SEND4", "SEND5"
		];
		$this->pAccess		= $this->src->checkPermission($this->permission[0]);
		$this->pCreate		= $this->src->checkPermission($this->permission[1]);
		$this->pList		= $this->src->checkPermission($this->permission[2]);
		$this->pEdit		= $this->src->checkPermission($this->permission[3]);
		$this->pDelete		= $this->src->checkPermission($this->permission[4]);
		$this->pStatus		= $this->src->checkPermission($this->permission[5]);
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

		if ($request->request->get('_token') && !$this->isCsrfTokenValid('sender', $request->request->get('_token')))
			return $this->src->no_access($this->intl->trans("Actions sur sender bloquées à l'utilisateur."));

		if(!$sender && $request->request->get("uid", null)) return $this->src->msg_error(
			$this->intl->trans("Action bloquée sur sender. (Erreur de requête)"),
			$this->intl->trans("Une erreur fatale dans la requête. Rechargez vous page.")
		);

		if(!$sender){
			$sender = new Sender();
			$sender->setStatus($this->src->status(2));
		}

		if($request->isXmlHttpRequest()){
			$uidBrand	= trim($request->request->get("brand"));
			$uidUser	= trim($request->request->get("manager"));
			$name		= trim($request->request->get("name"));
			$observ		= trim($request->request->get("observation"));

			//$brand = $this->em->getRepository(Brand::class)->findOneByUid($uidBrand);

			$user = $this->em->getRepository(User::class)->findOneBy([
				"uid"=>$uidUser,
				//"brand"=>$brand,
			]);

			if(!$user) return $this->src->msg_error(
				$this->intl->trans("Echec lors de l'édition d'expéditeur. Utilisateur %1% inconnu.", ["%1%"=>$uidUser]),
				$this->intl->trans("Impossible de retrouver cet utilisateur."),
			);

			if($user->getBrand()->getUid() != $uidBrand) return $this->src->msg_error(
				$this->intl->trans("Echec lors de l'édition d'expéditeur'. Erreur dans requête."),
				$this->intl->trans("La marque n'est pas indiquée."),
			);

			if($sender->getUid() && $this->pEdit){
				$sender->setName($name)
					->setUpdatedAt(new \DateTimeImmutable())
					->setCreateBy($this->getUser())
					->setObservation($observ);

				$task = $this->intl->trans("Mise à jour du Sender : ".$sender->getName());
				$message = $this->intl->trans("Mise à jour de l'expéditeur effectuée.");
			}
			else if($this->pCreate){
				$sender->setUid($this->src->getUniqid())
					->setName($name)
					->setManager($user)
					->setCreatedAt(new \DateTimeImmutable())
					->setUpdatedAt(null)
					->setCreateBy($this->getUser())
					->setObservation($observ);

				$task = $this->intl->trans("Création du Sender : ".$sender->getName());
				$message = $this->intl->trans("Expéditeur créé avec succès.");
			}
			else{
				return $this->src->no_access($this->intl->trans("Acces refusé à l'édition de sender."));
			}

			$senderRepository->add($sender, true);

			return $this->src->msg_success($task , $message);
		}

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);
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

		switch ($userType) {
			case 4: $users = [$this->getUser()]; break;
			case 5: $users = [$this->getUser()->getAffiliateManager()]; break;
			default: $users = []; break;
		}

		return $this->renderForm('sender/index.html.twig', [
			// 'form'      => $form,
			'sender'	=> $sender,
			'brands'	=> $brands,
			'users'		=> $users,
			'senders'	=> [],
			'status'	=> $status,
			'brand'		=> $this->brand->get(),
			'pAccess'	=> $this->pAccess,
			'pCreate'	=> $this->pCreate,
			'userType'	=> $userType,
			'pList'		=> $this->pList,
			'pEdit'		=> $this->pEdit,
			'pDelete'	=> $this->pDelete,
			'pStatus'	=> $this->pStatus,
			'pageTitle'	=> [
				[$this->intl->trans("Expéditeur")]
			]
		]);
	}

	#[Route('/all', name: 'sender_all', methods: ['POST'])]
	public function all(Request $request): JsonResponse
	{
		if(!$this->pAccess) return $this->src->no_access($this->intl->trans("Acces refusé à la récupération des senders"));

		if (!$this->isCsrfTokenValid('sender', $request->request->get('_token'))) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$senders = [];
		$request_sender = [];

		$user_manage = ($request->request->get('manager') !== "") ? $this->em->getRepository(User::class)->findOneByUid($request->request->get('manager')) : null;
		if($user_manage) $request_sender["manager"] = $user_manage->getId();

		$brand = ($request->request->get('brand') !== "") ? $this->em->getRepository(Brand::class)->findOneByUid($request->request->get('brand')) : null;
		if($brand) $request_sender["brand"] = $brand->getId();

		$status = ($request->request->get('status') !== "") ? $this->em->getRepository(Status::class)->findOneByUid($request->request->get('status')) : null;
		if($status) $request_sender["status"] = $status->getId();

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		$merge = array_merge($request_sender, $userRequest);

		$senders = $this->em->getRepository(Sender::class)->userTypeFindBy($userType, $merge);

		$data = [];

		foreach ($senders as $key => $sender) {
			$data[$key][] = $sender->getName();
			$data[$key][] = [
				"name"=>$sender->getStatus()->getName(),
				"label"=>$sender->getStatus()->getLabel(),
				"code"=>$sender->getStatus()->getCode(),
				"uid"=>$sender->getStatus()->getUid(),
			];
			$data[$key][] = $sender->getCreatedAt()->format("Y-m-d H:i:sP");
			$data[$key][] = [$sender->getManager()->getBrand()->getName(), $sender->getManager()->getBrand()->getUid()];
			$data[$key][] = [$sender->getManager()->getEmail(), $sender->getManager()->getUid()];
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
					'userType'	=> $userType
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

	#[Route('/{uid}', name: 'sender_action', methods: ['GET', 'POST'])]
	public function action(Request $request, Sender $sender = null, SenderRepository $senderRepository): Response
	{
		if (!$this->isCsrfTokenValid('sender', $request->request->get('_token')))
			return $this->src->no_access($this->intl->trans("Actions sur sender bloquées à l'utilisateur (Erreur de requête)."));

		$action = (int)trim($request->request->get("action"));

		if(!in_array($action, [0,1,2])) return $this->src->msg_error(
			$this->intl->trans("Acces refusé. (Erreur de requête)"),
			$this->intl->trans("Une erreur fatale dans la requête. L'action est indéterminée.")
		);

		switch ($action) {
			case 1: $action_text = $this->intl->trans("activation"); break;
			case 2: $action_text = $this->intl->trans("suppression"); break;
			default: $action_text = $this->intl->trans("désactivation"); break;
		}

		if(!$this->pAccess || (!$this->pStatus && ($action === 0 || $action === 1)) || (!$this->pDelete && $action === 2))
			return $this->src->no_access($this->intl->trans("Acces refusé pour %1% de l'expéditeur %2%.", ["%1%"=>$action_text, "%2%"=>$sender->getName()]));

		if(!$sender) return $this->src->msg_error(
			$this->intl->trans("Action bloquée sur sender %1%. (Erreur de requête)", ["%1%"=>$sender->getName()]),
			$this->intl->trans("Une erreur fatale dans la requête. Rechargez vous page.")
		);

		$sender->setUpdatedAt(new \DateTimeImmutable());

		switch ($action) {
			case 0: $sender->setStatus($this->src->status(4)); break;
			case 1: $sender->setStatus($this->src->status(3)); break;
			default:break;
		}

		$textAction = ucfirst($action_text);

		$nameSender = $sender->getName();

		if($action == 2){
			$this->em->remove($sender);
		}
		else{
			$senderRepository->add($sender, true);
		}

		return $this->src->msg_success(
			$this->intl->trans("%1% du sender %2%.", ["%1%"=>$textAction, "%2%"=>$nameSender]),
			$this->intl->trans("%1% de l'expéditeur effectuée avec succès.", ["%1%"=>ucfirst($action_text)]),
		);
	}
}
