<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Accounting;
use App\Entity\Status;
use App\Entity\Brand;

use App\Service\uBrand;
use App\Service\Services;

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
#[Route('/{_locale}/history/balance')]
class BalanceController extends AbstractController
{
	public function __construct(TranslatorInterface $intl, uBrand $brand, Services $src, UrlGeneratorInterface $ug, EntityManagerInterface $em)
	{
		$this->intl			= $intl;
		$this->brand		= $brand;
		$this->src			= $src;
		$this->ug			= $ug;
		$this->em			= $em;
		$this->permission	= [
			"BALA0", "BALA1",  "BALA2", "BALA3", "BALA4"
		];
		$this->pAccess		= $this->src->checkPermission($this->permission[0]);
		$this->pCreate		= $this->src->checkPermission($this->permission[1]);
		$this->pList		= $this->src->checkPermission($this->permission[2]);
		$this->pEdit		= $this->src->checkPermission($this->permission[3]);
		$this->pDelete		= $this->src->checkPermission($this->permission[4]);
	}

	#[Route('', name: 'history_balance')]
	public function index(): Response
	{
		if(!$this->pAccess)
		{
			$this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
			$this->src->addLog($this->intl->trans("Acces refusé à Sender"), 417);
			return $this->redirectToRoute("app_home");
		}

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		$status = [
			$this->src->status(3),
		];

		$brands = $this->em->getRepository(Brand::class)->findBrandBy($userType, $userRequest);

		switch ($userType) {
			case 4: $users = [$this->getUser()]; break;
			case 5: $users = [$this->getUser()->getAffiliateManager()]; break;
			default: $users = []; break;
		}

		return $this->renderForm('balance/index.html.twig', [
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
			'pageTitle'	=> [
				[$this->intl->trans("Historique de la balance")]
			]
		]);
	}

	#[Route('/all', name: 'balance_all', methods: ['POST'])]
	public function all(Request $request): JsonResponse
	{
		if(!$this->pAccess) return $this->src->no_access($this->intl->trans("Acces refusé à la récupération de l'historique de la balance"));

		if (!$this->isCsrfTokenValid('balance', $request->request->get('_token'))) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$historique = [];
		$request_balance = [];

		$brand = ($request->request->get('brand') !== "") ? $this->em->getRepository(Brand::class)->findOneByUid($request->request->get('brand')) : null;
		if($brand) $request_balance["brand"] = $brand->getId();

		$user_manage = ($request->request->get('manager') !== "") ? $this->em->getRepository(User::class)->findOneByUid($request->request->get('manager')) : null;
		if($user_manage) $request_balance["manager"] = $user_manage->getId();

		$status = ($request->request->get('status') !== "") ? $this->em->getRepository(Status::class)->findOneByUid($request->request->get('status')) : null;
		if($status) $request_balance["status"] = $status->getId();
		else $request_balance["status"] = $this->src->status(3)->getId();

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		$merge = array_merge($request_balance, $userRequest);

		$historique = $this->em->getRepository(Accounting::class)->userTypeFindBy($userType, $merge);

		$data = [];

		foreach ($historique as $key => $line) {
			$data[$key][] = $line->getUser()->getEmail();
			$data[$key][] = $line->getBeforeBalance();
			$data[$key][] = $line->getAmount();
			$data[$key][] = $line->getAfterBalance();
			$data[$key][] = $line->getCreatedAt()->format("Y-m-d H:i:sP");
			$data[$key][] = $line->getDescription();
			$data[$key][] = $line->getIdTrace();
			$data[$key][] = [
				"name"=>$line->getStatus()->getName(),
				"label"=>$line->getStatus()->getLabel(),
				"code"=>$line->getStatus()->getCode(),
				"uid"=>$line->getStatus()->getUid(),
			];
		}

		$manager_email = $user_manage ? $user_manage->getEmail() : "tous";

		return $this->src->msg_success(
			$this->intl->trans("Récupération de l'historique de la balance de ".$manager_email),
			"",
			[
				"table"=>$data,
				"permission"=>[
					'pAccess'   => $this->pAccess,
					'pCreate'   => $this->pCreate,
					'pList'     => $this->pList,
					'pEdit'     => $this->pEdit,
					'pDelete'   => $this->pDelete,
					'userType'	=> $userType
				]
			]
		);
	}
}
