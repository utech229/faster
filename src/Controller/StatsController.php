<?php

namespace App\Controller;

use App\Entity\SMSMessage;
use App\Entity\SMSCampaign;
use App\Entity\SMSMessageFile;
use App\Entity\Brand;
use App\Entity\User;
use App\Entity\Sender;
use App\Entity\Status;

use App\Service\uBrand;
use App\Service\Services;
use App\Service\Message;
use App\Service\BrickPhone;

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
#[Route('/{_locale}/messages/sms/statistiques')]
class StatsController extends AbstractController
{
	public function __construct(TranslatorInterface $intl, uBrand $brand, Services $src, UrlGeneratorInterface $ug, EntityManagerInterface $em, Message $sMessage, BrickPhone $brickPhone)
	{
		$this->intl          = $intl;
		$this->brand         = $brand;
		$this->src           = $src;
		$this->urlg          = $ug;
		$this->em            = $em;
		$this->sMessage      = $sMessage;
		$this->brickPhone    = $brickPhone;
		$this->permission    = [
			"STAT0", "STAT1", "STAT2", "STAT3", "STAT4",
		];
		$this->pAccess	= $this->src->checkPermission($this->permission[0]);
		$this->pCreate	= $this->src->checkPermission($this->permission[1]);
		$this->pList	= $this->src->checkPermission($this->permission[2]);
		$this->pEdit	= $this->src->checkPermission($this->permission[3]);
		$this->pDelete	= $this->src->checkPermission($this->permission[4]);

		$this->status    =   [
			"0"=>$this->src->status(0),
			"1"=>$this->src->status(1),
			"8"=>$this->src->status(8),
			"9"=>$this->src->status(9),
			"5"=>$this->src->status(5),
		];
	}

	#[Route('', name: 'message_sms_stats', methods: ['GET'])]
	public function index(Request $request): Response
	{
		if(!$this->pAccess)
		{
			$this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
			$this->src->addLog($this->intl->trans("Acces refusé aux messages"), 417);
			return $this->redirectToRoute("app_home");
		}

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		$brands = $this->em->getRepository(Brand::class)->findBrandBy($userType, $userRequest);

		switch ($userType) {
			case 4: $users = [$this->getUser()]; break;
			case 5: $users = [$this->getUser()->getAffiliateManager()]; break;
			default: $users = []; break;
		}

		return $this->renderForm('stats/index.html.twig', [
			'brands'    => $brands,
			'users'     => $users,
			'userType'  => $userType,
			'status'    => $this->status,
			'brand'     => $this->brand->get(),
			'pAccess'   => $this->pAccess,
			'pCreate'   => $this->pCreate,
			'pList'     => $this->pList,
			'pEdit'     => $this->pEdit,
			'pDelete'   => $this->pDelete,
			'pageTitle' => $pageTitle = [
				[$this->intl->trans('Messages SMS'), $this->urlg->generate("message_sms")],
				[$this->intl->trans('Statistiques')]
			],
		]);
	}

	#[Route('/get', name: 'message_sms_stats_get', methods: ['GET', 'POST'])]
	public function listen(Request $request)
	{
		$session = $this->getUser();

		if(!$this->pAccess) return $this->src->no_access($this->intl->trans("Acces refusé à la récupération du statistique des messages"));

		if (!$this->isCsrfTokenValid('message', $request->request->get('_token'))) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$uidBrand   = $request->request->get("brand");
		$uidManager = $request->request->get("manager");
		$sender     = $request->request->get("sender");
		$uidStatus  = $request->request->get("status");
		$periode    = $request->request->get("periode");

		$status     = $this->em->getRepository(Status::class)->findOneByUid($uidStatus);

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		if($from == "campaign")
		{
			$campaign = $this->em->getRepository(SMSCampaign::class)->findOneByUid($of);

			if(
				!$campaign
				|| ($userType == 1 && $masterId != $campaign->getManager()->getAccountManager()->getId())
				|| (($userType == 2 || $userType == 3) && $masterId != $campaign->getManager()->getBrand()->getManager()->getId())
				|| (($userType == 4 || $userType == 5) && $masterId != $campaign->getManager()->getId())
			) return $this->src->msg_error(
				$this->intl->trans("Récupération des messages de la campagne %1%. Erreur dans la requête.", ["%1%"=>$from]),
				$this->intl->trans("Aucune données trouvées."),
				[]
			);

			$campaignFile = $this->em->getRepository(SMSMessageFile::class)->findOneByCampaign($campaign);

			if(!$campaignFile) return $this->src->msg_error(
				$this->intl->trans("Fichier de la campagne %1% n'existe pas dans la base de données.", ["%1%"=>$campaign->getUid()]),
				$this->intl->trans("Cette campagne n'est plus utilisable. Veuillez créer une autre."),
				[],
			);

			if(!is_file($campaignFile->getUrl())) return $this->src->msg_error(
				$this->intl->trans("Fichier de la campagne %1% introuvable.", ["%1%"=>$campaign->getUid()]),
				$this->intl->trans("Cette campagne n'est plus utilisable. Veuillez créer une autre."),
				[],
			);

			$messages = json_decode(file_get_contents($campaignFile->getUrl()), true);

			$currency = ($campaign->getManager()->getUsetting()->getCurrency())["code"];

			$data = [];
			$key = 0;
			foreach ($messages as $message) {
				$myStatus = $this->status[(string)$message["status"]];
				//$sendingAt = (new \DateTime($message["sendingAt"]))->setTimezone(new \DateTimeZone($message["timezone"]));
				if(!$status || $status == $myStatus)
				{
					$data[$key][] = $message["phone"];
					$data[$key][] = $message["sender"];
					$data[$key][] = $message["sendingAt"];
					$data[$key][] = [
						"code"=>$myStatus->getCode(),
						"label"=>$myStatus->getLabel(),
						"name"=>$myStatus->getName(),
						"uid"=>$myStatus->getUid(),
					];
					$data[$key][] = $message["pages"];
					$data[$key][] = $message["phoneCountry"];
					$data[$key][] = $message["message"];
					$data[$key][] = null;

					$key++;
				}
			}

			return $this->src->msg_success(
				$this->intl->trans("Récupération des messages de la campagne %1%.", ["%1%"=>$from]),
				"",
				[
					"table"=>$data,
					"permission"=>[
						"pAccess"=>$this->pAccess,
						"pCreate"=>$this->pCreate,
						"pList"=>$this->pList,
						"pEdit"=>$this->pEdit,
						"pDelete"=>$this->pDelete,
					]
				],
			);
		}

		$messages = [];
		$request_messages = [];

		// $today = new \DateTime();
		switch ($periode) {
			case '1m': $lastday = new \DateTime('-1 month'); break;
			case '3m': $lastday = new \DateTime('-3 month'); break;
			case '1y': $lastday = new \DateTime('-1 year'); break;
			default: $lastday = new \DateTime('-1 week'); break;
		}

		$brand = ($uidBrand !== "") ? $this->em->getRepository(Brand::class)->findOneByUid($uidBrand) : null;
		if($brand) $request_messages["brand"] = $brand->getId();

		$user_manage = ($uidManager !== "") ? $this->em->getRepository(User::class)->findOneByUid($uidManager) : null;
		if($user_manage) $request_messages["manager"] = $user_manage->getId();

		if($status) $request_messages["status"] = $status->getId();

		$campaign = ($of !== "") ? $this->em->getRepository(SMSCampaign::class)->findOneByUid($of) : null;
		if($campaign) $request_messages["campaign"] = $campaign->getId();

		if($sender !== "") $request_messages["sender"] = $sender;

		$request_messages["from"] = ($from == "campaign" || $from == "api") ? $from : null;

		// $request_messages["today"] = $today->format("Y-m-d H:i:sP");

		$request_messages["lastday"] = $lastday->format("Y-m-d H:i:sP");

		$merge = array_merge($request_messages, $userRequest);

		$messages = $this->em->getRepository(SMSMessage::class)->userTypeFindBy($userType, $merge);

		$data = [];
		foreach ($messages as $key => $message) {
			$currency = ($message->getManager()->getUsetting()->getCurrency())["code"];
			$sendingAt = ($message->getSendingAt())->setTimezone(new \DateTimeZone($message->getTimezone()));

			$data[$key][] = $message->getPhone();
			$data[$key][] = $message->getSender();
			$data[$key][] = $sendingAt->format("Y-m-d H:i:sP");
			$data[$key][] = [
				"code"=>$message->getStatus()->getCode(),
				"label"=>$message->getStatus()->getLabel(),
				"name"=>$message->getStatus()->getName(),
				"uid"=>$message->getStatus()->getUid(),
			];
			$data[$key][] = $message->getPages();
			$data[$key][] = $message->getPhoneCountry();
			$data[$key][] = $message->getMessage();
			$data[$key][] = $message->getUid();
		}

		return $this->src->msg_success(
			$this->intl->trans("Récupération des messages %1%.", ["%1%"=>$from]),
			"",
			[
				"table"=>$data,
				"permission"=>[
					"pAccess"=>$this->pAccess,
					"pCreate"=>$this->pCreate,
					"pList"=>$this->pList,
					"pEdit"=>$this->pEdit,
					"pDelete"=>$this->pDelete,
				]
			],
		);
	}
}
