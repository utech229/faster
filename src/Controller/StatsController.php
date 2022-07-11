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
			'senders'	=> [],
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
		$session		= $this->getUser();
		$allMessages	= [];
		$programming	= 0;
		$pending		= 0;
		$undelivred		= 0;
		$delivred		= 0;

		if(!$this->pAccess) return $this->src->no_access($this->intl->trans("Acces refusé à la récupération du statistique des messages"));

		if (!$this->isCsrfTokenValid('message', $request->request->get('_token'))) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$uidBrand   = trim($request->request->get("brand"));
		$uidManager = trim($request->request->get("manager"));
		$sender     = trim($request->request->get("sender"));
		$uidStatus  = trim($request->request->get("status"));
		$periode    = trim($request->request->get("periode"));

		switch ($periode) {
			case '1m': $lastday = new \DateTime('-1 month'); break;
			case '3m': $lastday = new \DateTime('-3 month'); break;
			case '1y': $lastday = new \DateTime('-1 year'); break;
			default: $lastday = new \DateTime('-1 week'); break;
		}

		list($dataGraph1, $dataGraph2) = $this->statistiques($periode);
		$dataGraph3 = [];

		$request_campaigns = [];

		$brand = ($uidBrand !== "") ? $this->em->getRepository(Brand::class)->findOneByUid($uidBrand) : null;
		if($brand) $request_campaigns["brand"] = $brand->getId();

		$user_manage = ($uidManager !== "") ? $this->em->getRepository(User::class)->findOneByUid($uidManager) : null;
		if($user_manage) $request_campaigns["manager"] = $user_manage->getId();

		$this->status['2'] = $this->src->status(2);

		$status = ($uidStatus !== "") ? $this->em->getRepository(Status::class)->findOneByUid($uidStatus) : null;
		if($status && $status->getCode() == 1)
		{
			$request_campaigns["status"] = [$status->getId(), $this->status['2']->getId()];
		}else if($status && $status->getCode() == 1)
		{
			$request_campaigns["status"] = [$status->getId()];
		}else{
			$request_campaigns["status"] = [
				$this->status['0']->getId(),
				$this->status['1']->getId(),
				$this->status['2']->getId(),
				$this->status['8']->getId(),
				$this->status['9']->getId(),
			];
		}

		if($sender !== "") $request_campaigns["sender"] = $sender;

		$request_campaigns["lastday"] = $lastday->format("Y-m-d 23:59:59P");

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		$merge = array_merge($request_campaigns, $userRequest);

		$campaigns = $this->em->getRepository(SMSCampaign::class)->userTypeFindBy($userType, $merge);

		$data = [];

		foreach ($campaigns as $key => $campaign) {
			$campaignFile = $this->em->getRepository(SMSMessageFile::class)->findOneByCampaign($campaign);

			$myStatus = $campaign->getStatus();

			if($campaignFile){
				if(is_file($campaignFile->getUrl())){
					$messages = json_decode(file_get_contents($campaignFile->getUrl()), true);
					$data = [];
					$key = 0;
					foreach ($messages as $message) {
						if($myStatus->getCode() == 8) $myStatus = $this->status[(string)$message["status"]];
						else if($myStatus->getCode() == 2) $myStatus = $this->status["1"];

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
						$data[$key][] = ["campaign", $this->intl->trans("Campagne")];
						$data[$key][] = $message["message"];
						$data[$key][] = $message["createdAt"];

						$dateIndex = null;

						switch ($periode) {
							case '3m':
								$dateTime = new \DateTime($message["createdAt"]);
								$monday = clone $dateTime->modify(('Sunday' == $dateTime->format('l')) ? 'Monday last week' : 'Monday this week');
								$sunday = clone $dateTime->modify('Sunday this week');

								$dateIndex = $monday->format('Ymd').'_'.$sunday->format('Ymd');
								break;
							case '1y':
								$dateIndex = (new \DateTime($message["createdAt"]))->format('Ym01000000');
								break;
							default:
								$dateIndex = (new \DateTime($message["createdAt"]))->format('Ymd000000');
								break;
						}

						if($dateIndex){
							$dataGraph2[$dateIndex][0][0] += $message["pages"];

							switch ($myStatus->getCode()) {
								case 1:
									$dataGraph1[$dateIndex][0][1] += $message["pages"];
									$pending		+= $message["pages"];
									break;
								case 9:
									$dataGraph1[$dateIndex][0][2] += $message["pages"];
									$undelivred		+= $message["pages"];
									break;
								case 8:
									$dataGraph1[$dateIndex][0][3] += $message["pages"];
									$delivred		+= $message["pages"];
									break;
								default:
									$dataGraph1[$dateIndex][0][0] += $message["pages"];
									$programming	+= $message["pages"];
									break;
							}
						}

						$thisOp = strtoupper($message["phoneCountry"]["operator"])."-".$message["phoneCountry"]["name"];
						if(isset($dataGraph3[$thisOp])){
							$dataGraph3[$thisOp] += $message["pages"];
						}else{
							$dataGraph3[$thisOp] = $message["pages"];
						}

						$key++;
					}

					$merge			= array_merge($allMessages, $data);
					$allMessages	= $merge;
				}
			}
		}

		$merge = array_merge($request_campaigns, $userRequest);

		$messages = $this->em->getRepository(SMSMessage::class)->userTypeFindBy($userType, $merge);

		$data = [];
		foreach ($messages as $key => $message) {
			$sendingAt = ($message->getSendingAt())->setTimezone(new \DateTimeZone($message->getTimezone()));

			$myStatus = $message->getStatus();

			if($myStatus->getCode() == 2) $myStatus = $this->status["1"];

			switch ($message->getCreateFrom()) {
				case 'campaign': $source = $this->intl->trans("Campagne SMS"); break;
				case 'api': $source = $this->intl->trans("SMS API"); break;
				default: $source = $this->intl->trans("SMS"); break;
			}

			$data[$key][] = $message->getPhone();
			$data[$key][] = $message->getSender();
			$data[$key][] = $sendingAt->format("Y-m-d 23:59:59P");
			$data[$key][] = [
				"code"=>$myStatus->getCode(),
				"label"=>$myStatus->getLabel(),
				"name"=>$myStatus->getName(),
				"uid"=>$myStatus->getUid(),
			];
			$data[$key][] = $message->getPages();
			$data[$key][] = $message->getPhoneCountry();
			$data[$key][] = [$message->getCreateFrom(), $source];
			$data[$key][] = $message->getMessage();
			$data[$key][] = $message->getCreatedAt()->format("Y-m-d H:i:sP");

			$dateIndex = null;

			switch ($periode) {
				case '3m':
					$dateTime = new \DateTime($message->getCreatedAt()->format("Y-m-d H:i:sP"));
					$monday = clone $dateTime->modify(('Sunday' == $dateTime->format('l')) ? 'Monday last week' : 'Monday this week');
					$sunday = clone $dateTime->modify('Sunday this week');

					$dateIndex = $monday->format('Ymd').'_'.$sunday->format('Ymd');
					break;
				case '1y':
					$dateIndex = (new \DateTime($message->getCreatedAt()->format("Y-m-d H:i:sP")))->format('Ym01000000');
					break;
				default:
					$dateIndex = (new \DateTime($message->getCreatedAt()->format("Y-m-d H:i:sP")))->format('Ymd000000');
					break;
			}

			if($dateIndex){
				switch ($message->getCreateFrom()) {
					case 'campaign':
						$dataGraph2[$dateIndex][0][0] += $message->getPages();
						break;
					case 'api':
						$dataGraph2[$dateIndex][0][1] += $message->getPages();
						break;
					default:
						$dataGraph2[$dateIndex][0][2] += $message->getPages();
						break;
				}

				switch ($myStatus->getCode()) {
					case 1:
						$dataGraph1[$dateIndex][0][1] += $message->getPages();
						$pending	+= $message->getPages();
						break;
					case 9:
						$dataGraph1[$dateIndex][0][2] += $message->getPages();
						$undelivred	+= $message->getPages();
						break;
					case 8:
						$dataGraph1[$dateIndex][0][3] += $message->getPages();
						$delivred	+= $message->getPages();
						break;
					default:
						$dataGraph1[$dateIndex][0][0] += $message->getPages();
						$programming	+= $message->getPages();
						break;
				}
			}

			$thisOp = strtoupper($message->getPhoneCountry()["operator"])."-".$message->getPhoneCountry()["name"];
			if(isset($dataGraph3[$thisOp])){
				$dataGraph3[$thisOp] += $message->getPages();
			}else{
				$dataGraph3[$thisOp] = $message->getPages();
			}
		}

		$merge			= array_merge($allMessages, $data);
		$allMessages	= $merge;

		return $this->src->msg_success(
			$this->intl->trans("Récupération des messages pour les statistiques."),
			"",
			[
				"table"=>$allMessages,
				"permission"=>[
					"pAccess"=>$this->pAccess,
					"pCreate"=>$this->pCreate,
					"pList"=>$this->pList,
					"pEdit"=>$this->pEdit,
					"pDelete"=>$this->pDelete,
				],
				"graphs"=>[
					"graph1"=>$dataGraph1,
					"graph2"=>$dataGraph2,
					"graph3"=>$dataGraph3,
				],
				"stats"=>[
					$programming,
					$pending,
					$undelivred,
					$delivred
				]
			],
		);
	}

	private function statistiques($periode){
		switch ($periode) {
			case '3m':
				for ($i=6; $i > 0; $i--) {
					$dateTime = new \DateTime('-'.$i.' week');
					$monday = clone $dateTime->modify(('Sunday' == $dateTime->format('l')) ? 'Monday last week' : 'Monday this week');
					$sunday = clone $dateTime->modify('Sunday this week');
					$table[$monday->format('Ymd').'_'.$sunday->format('Ymd')] = [
						$monday->format('Y-m-d 00:00:00'),
						$sunday->format('Y-m-d 00:00:00')
					];
				}

				$dateTime = new \DateTime();
				$monday = clone $dateTime->modify(('Sunday' == $dateTime->format('l')) ? 'Monday last week' : 'Monday this week');
				$sunday = clone $dateTime->modify('Sunday this week');
				$table[$monday->format('Ymd').'_'.$sunday->format('Ymd')] = [
					$monday->format('Y-m-d 00:00:00'),
					$sunday->format('Y-m-d 00:00:00')
				];
				break;
			case '1m':
				for ($i=30; $i > 0; $i--) {
					$table[(new \DateTime('-'.$i.' day'))->format('Ymd000000')] = (new \DateTime('-'.$i.' day'))->format('Y-m-d 00:00:00');
				}
				$table[(new \DateTime())->format('Ymd000000')] = (new \DateTime())->format('Y-m-d 00:00:00');
				break;
			case '1y':
				for ($i=11; $i > 0; $i--) {
					$table[(new \DateTime('-'.$i.' month'))->format('Ym01000000')] = (new \DateTime('-'.$i.' month'))->format('Y-m-01 00:00:00');
				}
				$table[(new \DateTime())->format('Ym01000000')] = (new \DateTime())->format('Y-m-01 00:00:00');
				break;
			default:
				for ($i=6; $i > 0; $i--) {
					$table[(new \DateTime('-'.$i.' day'))->format('Ymd000000')] = (new \DateTime('-'.$i.' day'))->format('Y-m-d 00:00:00');
				}
				$table[(new \DateTime())->format('Ymd000000')] = (new \DateTime())->format('Y-m-d 00:00:00');
				break;
		}

		foreach ($table as $key => $line) {
			$graph1[$key] = [
				[0,0,0,0],
				$line
			];

			$graph2[$key] = [
				[0,0,0],
				$line
			];
		}

		return [$graph1, $graph2];
	}
}
