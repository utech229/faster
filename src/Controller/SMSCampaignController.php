<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Sender;
use App\Entity\Status;
use App\Entity\Brand;
use App\Entity\SMSCampaign;
use App\Entity\SMSMessageFile;
use App\Entity\Contact;
use App\Entity\ContactGroup;

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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('/{_locale}/sms/campaign')]
class SMSCampaignController extends AbstractController
{
	private $pathRacine		= "campaign/";
	private $pathImport		= "campaign/import/temp/";
	private $initDataPhone	= [
		"dial_code"=>"null",
		"code"=>"null",
		"name"=>"null",
		"operator"=>"null",
	];

	public function __construct(TranslatorInterface $intl, uBrand $brand, Services $src, UrlGeneratorInterface $ug, EntityManagerInterface $em, Message $sMessage, BrickPhone $brickPhone)
	{
		$this->intl			= $intl;
		$this->brand		= $brand;
		$this->src			= $src;
		$this->urlg			= $ug;
		$this->em			= $em;
		$this->sMessage		= $sMessage;
		$this->brickPhone	= $brickPhone;
		$this->permission	= [
			"SMSC0", "SMSC1", "SMSC2", "SMSC3", "SMSC4", "SMSS1"
		];
		$this->pAccess	= $this->src->checkPermission($this->permission[0]);
		$this->pCreate	= $this->src->checkPermission($this->permission[1]);
		$this->pList	= $this->src->checkPermission($this->permission[2]);
		$this->pEdit	= $this->src->checkPermission($this->permission[3]);
		$this->pDelete	= $this->src->checkPermission($this->permission[4]);

		$this->pCreateMessage	= $this->src->checkPermission($this->permission[5]);

		$this->programming	= $this->src->status(0);
		$this->progressing	= $this->src->status(1);
		$this->suspend		= $this->src->status(5);
		$this->brouillon	= $this->src->status(10);
		$this->waiting		= $this->src->status(2);

		$this->status    =   [
			"0"=>$this->src->status(0),
			"1"=>$this->src->status(1),
			"8"=>$this->src->status(8),
			"5"=>$this->src->status(5),
			"10"=>$this->src->status(10),
		];
	}

	#[Route('', name: 'campaign_sms', methods: ['GET'])]
	public function index(): Response
	{
		if(!$this->pAccess)
		{
			$this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
			$this->src->addLog($this->intl->trans("Acces refusé à campagne"), 417);
			return $this->redirectToRoute("app_home");
		}

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		$params = [];

		$brands = $this->em->getRepository(Brand::class)->findBrandBy($userType, $userRequest);

		switch ($userType) {
			case 4: $users = [$this->getUser()]; break;
			case 5: $users = [$this->getUser()->getAffiliateManager()]; break;
			default: $users = []; break;
		}

		return $this->renderForm('smscampaign/index.html.twig', [
			'brands'    => $brands,
			'users'     => $users,
			'senders'	=> [],
			'status'    => $this->status,
			'brand'     => $this->brand->get(),
			'pAccess'   => $this->pAccess,
			'pCreate'   => $this->pCreate,
			'userType'  => $userType,// > 3 ? false : true,
			'pList'     => $this->pList,
			'pEdit'     => $this->pEdit,
			'pDelete'   => $this->pDelete,
			'pageTitle' => [
				[$this->intl->trans("Campagnes SMS")]
			]
		]);
	}

	#[Route('/listen', name: 'campaign_sms_listen', methods: ['GET','POST'])]
	public function listen(Request $request)
	{
		// $campaigns = $this->em->getRepository(SMSCampaign::class)->findAll();
		// foreach ($campaigns as $campaign) {
		// 	if($campaign->getStatus()->getCode() == 5){
		// 		$campaign->setStatus($this->brouillon);
		// 		$this->em->persist($campaign);
		// 	}
		// }
		// $this->em->flush();

		if(!$this->pAccess) return $this->src->no_access($this->intl->trans("Acces refusé à la récupération des campagnes"));

		if (!$this->isCsrfTokenValid('campaign', $request->request->get('_token'))) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$uidBrand   = trim($request->request->get("brand"));
		$uidManager = trim($request->request->get("manager"));
		$sender     = trim($request->request->get("sender"));
		$uidStatus  = trim($request->request->get("status"));
		$periode    = trim($request->request->get("periode"));

		$campaigns = [];
		$request_campaigns = [];

		switch ($periode) {
			case '1m': $lastday = new \DateTime('-1 month'); break;
			case '3m': $lastday = new \DateTime('-3 month'); break;
			case '1y': $lastday = new \DateTime('-1 year'); break;
			default: $lastday = new \DateTime('-1 week'); break;
		}

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
				$this->status['5']->getId(),
				$this->status['8']->getId(),
				$this->status['10']->getId(),
			];
		}

		if($sender !== "") $request_campaigns["sender"] = $sender;

		$request_campaigns["lastday"] = $lastday->format("Y-m-d H:i:sP");

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		$merge = array_merge($request_campaigns, $userRequest);

		$campaigns = $this->em->getRepository(SMSCampaign::class)->userTypeFindBy($userType, $merge);

		$data = [];

		foreach ($campaigns as $key => $campaign) {
			$counting = $campaign->getCounting();

			$data[$key][] = $campaign->getName();
			$data[$key][] = $campaign->getSender();
			$data[$key][] = ($campaign->getSendingAt()->setTimezone(new \DateTimeZone($campaign->getTimezone())))->format("Y-m-d H:i:sP");
			$data[$key][] = [$counting["persons"],$counting["pages"]];
			$data[$key][] = [
				"name"=>$campaign->getStatus()->getName(),
				"label"=>$campaign->getStatus()->getLabel(),
				"code"=>$campaign->getStatus()->getCode(),
				"uid"=>$campaign->getStatus()->getUid(),
			];
			$data[$key][] = $campaign->getMessage();
			$data[$key][] = $campaign->getCreatedAt()->format("Y-m-d H:i:sP");
			$data[$key][] = $campaign->getUid();
		}

		$manager_email = $user_manage ? $user_manage->getEmail() : "tous";

		return $this->src->msg_success(
			$this->intl->trans("Récupération des campagnes de ".$manager_email),
			"",
			[
				"table"=>$data,
				"permission"=>[
					'pAccess'   => $this->pAccess,
					'pCreate'   => $this->pCreate,
					'pList'     => $this->pList,
					'pEdit'     => $this->pEdit,
					'pDelete'   => $this->pDelete,
				]
			]
		);
	}

	#[Route('/disable', name: 'campaign_sms_disable', methods: ['GET','POST'])]
	public function disableCampaign(Request $request)
	{
		if(!$this->pEdit) return $this->src->no_access(
			$this->intl->trans("Acces refusé à la suspension de la campagne."),
		);

		if (!$this->isCsrfTokenValid('campaign', $request->request->get('_token'))) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$campaign = $this->em->getRepository(SMSCampaign::class)->findOneByUid($request->request->get('campaign'));

		if(!$campaign || $campaign->getStatus()->getCode() != 0) return $this->src->msg_error(
			$this->intl->trans("Suspension de campagne rejetée. Erreur dans la requête."),
			$this->intl->trans("La requête n'est pas traitée. Veuillez reprendre."),
			[]
		);

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		switch ($userType) {
			case 1:
				if($masterId != $campaign->getManager()->getAccountManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suspension de la campagne."),
				);
				break;
			case 2:
				if($masterId != $campaign->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suspension de la campagne."),
				);
				break;
			case 3:
				if($masterId != $campaign->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suspension de la campagne."),
				);
				break;
			case 4:
				if($masterId != $campaign->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suspension de la campagne."),
				);
				break;
			case 5:
				if($masterId != $campaign->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suspension de la campagne."),
				);
				break;
			default:
				// code...
				break;
		}

		return $this->applyDisableCampaign($campaign, $this->suspend);
	}

	#[Route('/delete', name: 'campaign_sms_delete', methods: ['GET','POST'])]
	public function deleteCampaign(Request $request)
	{
		if(!$this->pDelete) return $this->src->no_access(
			$this->intl->trans("Acces refusé à la suspension de la campagne."),
		);

		if (!$this->isCsrfTokenValid('campaign', $request->request->get('_token'))) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
			[]
		);

		$campaign = $this->em->getRepository(SMSCampaign::class)->findOneByUid($request->request->get('campaign'));

		if(!$campaign) return $this->src->msg_error(
			$this->intl->trans("Suppression de campagne rejetée. Erreur dans la requête."),
			$this->intl->trans("La requête n'est pas traitée. Veuillez reprendre."),
			[]
		);

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		switch ($userType) {
			case 1:
				if($masterId != $campaign->getManager()->getAccountManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suppression de la campagne."),
				);
				break;
			case 2:
				if($masterId != $campaign->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suppression de la campagne."),
				);
				break;
			case 3:
				if($masterId != $campaign->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suppression de la campagne."),
				);
				break;
			case 4:
				if($masterId != $campaign->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suppression de la campagne."),
				);
				break;
			case 5:
				if($masterId != $campaign->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suppression de la campagne."),
				);
				break;
			default:
				// code...
				break;
		}

		if($campaign->getStatus()->getCode() == 0)
		{
			$manager	= $campaign->getManager();

			$myBalance	= $manager->getBalance();

			$amount		= $campaign->getCampaignAmount();

			$manager->setBalance($myBalance + $amount);

			$this->em->persist($manager);

			$this->src->addBalanceChange($manager, $myBalance, $amount, $this->intl->trans("Suppression de la campagne SMS '%1%'", ["%1%"=>$campaign->getName()]), $campaign->getUid());
		}

		$file = $this->em->getRepository(SMSMessageFile::class)->findOneByCampaign($campaign);

		if($file)
		{
			if(is_file($file->getUrl())) unlink($file->getUrl());

			$this->em->remove($file);
		}

		$this->em->remove($campaign);

		return $this->src->msg_success(
			$this->intl->trans("Suppression de campagne %1%.", ["%1%"=>$campaign->getUid()]),
			$this->intl->trans("La campagne vient d'être supprimée avec succès."),
		);
	}

	#[Route('/enable', name: 'campaign_sms_enable', methods: ['GET','POST'])]
	public function enableCampaign(Request $request)
	{
		if(!$this->pEdit) return $this->src->no_access(
			$this->intl->trans("Acces refusé à l'activation de la campagne."),
		);

		if (!$this->isCsrfTokenValid('campaign', $request->request->get('_token'))) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$campaign = $this->em->getRepository(SMSCampaign::class)->findOneBy([
			"uid"=>$request->request->get('campaign'),
			"status"=>$this->suspend
		]);

		if(!$campaign) return $this->src->msg_error(
			$this->intl->trans("Relance de la campagne rejetée. Erreur dans la requête."),
			$this->intl->trans("La requête n'est pas traitée. Veuillez reprendre."),
		);

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		switch ($userType) {
			case 1:
				if($masterId != $campaign->getManager()->getAccountManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la relance de la campagne."),
				);
				break;
			case 2:
				if($masterId != $campaign->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la relance de la campagne."),
				);
				break;
			case 3:
				if($masterId != $campaign->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la relance de la campagne."),
				);
				break;
			case 4:
				if($masterId != $campaign->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la relance de la campagne."),
				);
				break;
			case 5:
				if($masterId != $campaign->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la relance de la campagne."),
				);
				break;
			default:
				// code...
				break;
		}

		$campaignFile = $this->em->getRepository(SMSMessageFile::class)->findOneByCampaign($campaign);

		if(!$campaignFile) return $this->src->msg_error(
			$this->intl->trans("Fichier de la campagne %1% n'existe pas dans la base de données.", ["%1%"=>$campaign->getUid()]),
			$this->intl->trans("Cette campagne n'est plus utilisable. Veuillez créer une autre."),
		);

		if(!is_file($campaignFile->getUrl())) return $this->src->msg_error(
			$this->intl->trans("Fichier de la campagne %1% introuvable.", ["%1%"=>$campaign->getUid()]),
			$this->intl->trans("Cette campagne n'est plus utilisable. Veuillez créer une autre."),
		);

		$messages	= json_decode(file_get_contents($campaignFile->getUrl()), true);

		list($nbrErrors, $errors, $amount) = $this->checkMessages($messages, $campaign);

		if($nbrErrors > 0)
		{
			return $this->src->msg_warning(
				$this->intl->trans("Relance de la campagne %1% échouée.", ["%1%"=>$campaign->getUid()]),
				$this->intl->trans("Echec du relancement. %1% ligne(s) d'erreur trouvée(s).", ["%1%"=>$nbrErrors]),
				$errors,
			);
		}

		return $this->applyEnableCampaign($campaign, $amount);
	}

	#[Route('/check', name: 'campaign_sms_check', methods: ['POST'])]
	public function checkCampaign(Request $request)
	{
		$campaignUid = trim($request->request->get("campaign"));

		$campaign = $this->em->getRepository(SMSCampaign::class)->findOneBy(["uid"=>$campaignUid]);

		if(!$campaign) return $this->src->msg_error(
			$this->intl->trans("Campagne introuvable. Erreur dans la requête."),
			$this->intl->trans("Erreur dans la requête. Rechargez votre page."),
			[],
		);

		$campaignFile = $this->em->getRepository(SMSMessageFile::class)->findOneByCampaign($campaign);

		if(!$campaignFile) return new JsonResponse([
			"type"=>"error",
			"message"=>$this->intl->trans("Cette campagne n'est plus utilisable. Veuillez créer une autre."),
			"data"=>[]
		]);

		if(!is_file($campaignFile->getUrl())) return new JsonResponse([
			"type"=>"error",
			"message"=>$this->intl->trans("Cette campagne n'est plus utilisable. Veuillez créer une autre."),
			"data"=>[]
		]);

		$messages = json_decode(file_get_contents($campaignFile->getUrl()), true);

		list($nbr, $dataError) = $this->checkMessages($messages, $campaign);

		return new JsonResponse(["type"=>"success","data"=>$dataError]);
	}

	#[Route('/view/created', name: 'campaign_sms_view_created', methods: ['GET','POST'])]
	public function viewCreated(Request $request): Response
	{
		$string = "\tPar ailleurs, l'«hygiénisme moral» trans-national débuté au <a href='' title='XIXe siècle'><abbr class='abbr' title='19ᵉ siècle'><span class='romain'>XIX</span><sup style='font-size:72%'>e</sup></abbr> siècle</a> (à ne pas confondre avec la <a href='' class='mw-redirect' title='Médecine alternative'>médecine alternative</a> créée par <a href='/wiki/Herbert_Shelton' class='mw-redirect' title='Herbert Shelton'>Herbert Shelton</a>) est une doctrine contre le «relâchement des mœurs», ce qui serait le meilleur moyen de garantir la santé.\n\r";

		//dd($this->sMessage->trueLength($string));

		if(!$this->pEdit)
		{
			$this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
			$this->src->addLog($this->intl->trans("Acces refusé à la création de campagne"), 417);
			return $this->redirectToRoute("campaign_sms");
		}

		$groups		= [];
		$allGroup	= [];

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		$campaign	= $this->em->getRepository(SMSCampaign::class)->findOneBy(["uid"=>$request->get("cgn")]);

		if($campaign){
			switch ($userType) {
				case 1:
					if($masterId != $campaign->getManager()->getAccountManager()->getId()) return $this->src->no_access(
						$this->intl->trans("Acces refusé à la relance de la campagne."),
					);
					break;
				case 2:
					if($masterId != $campaign->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
						$this->intl->trans("Acces refusé à la relance de la campagne."),
					);
					break;
				case 3:
					if($masterId != $campaign->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
						$this->intl->trans("Acces refusé à la relance de la campagne."),
					);
					break;
				case 4:
					if($masterId != $campaign->getManager()->getId()) return $this->src->no_access(
						$this->intl->trans("Acces refusé à la relance de la campagne."),
					);
					break;
				case 5:
					if($masterId != $campaign->getManager()->getId()) return $this->src->no_access(
						$this->intl->trans("Acces refusé à la relance de la campagne."),
					);
					break;
				default:
					// code...
					break;
			}

			$status		= $campaign->getStatus();
			if($status->getCode() == 1 || $status->getCode() == 8)
			{
				$this->addFlash('error', $this->intl->trans("Cette campagne ne peut plus être modifiée !"));
				$this->src->addLog($this->intl->trans("Modification d'une campagne %1% refusée.", ["%1%"=>$status->getName()]), 417);
				return $this->redirectToRoute("campaign_sms");
			}

			$brands		= [$campaign->getManager()->getBrand()];
			$users		= [$campaign->getManager()];
			$sender		= $this->em->getRepository(Sender::class)->findOneByName($campaign->getSender());
			$senders	= $sender?[$sender]:[];
			$sendingAt	= ($campaign->getSendingAt())->setTimezone(new \DateTimeZone($campaign->getTimezone()));
			$statusCode	= $campaign->getStatus()->getCode();
			$userType	= 2;
		}else{
			$getGroups	= trim((string)$request->get("grps"));
			$userUid	= trim((string)$request->get("user"));
			$user		= $this->em->getRepository(User::class)->findOneByUid($userUid);
			$groupsName	= explode("_", $getGroups);

			foreach ($groupsName as $group) {
				$grp = $this->em->getRepository(ContactGroup::class)->findOneBy(["name"=>$group, "manager"=>$user]);
				if(!$grp) $groups = [];
				else $groups[] = $grp->getUid();
			}

			if(!$user || $groups == []){
				$user		= null;
				$brands		= $this->em->getRepository(Brand::class)->findBrandBy($userType, $userRequest);
			}
			else{
				$allGroup	= $this->em->getRepository(ContactGroup::class)->findBy(["manager"=>$user]);
				switch ($userType) {
					case 1:
						if($masterId != $user->getAccountManager()->getId()) return $this->src->no_access(
							$this->intl->trans("Acces refusé à la relance de la campagne."),
						);
						break;
					case 2:
						if($masterId != $user->getBrand()->getManager()->getId()) return $this->src->no_access(
							$this->intl->trans("Acces refusé à la relance de la campagne."),
						);
						break;
					case 3:
						if($masterId != $user->getBrand()->getManager()->getId()) return $this->src->no_access(
							$this->intl->trans("Acces refusé à la relance de la campagne."),
						);
						break;
					case 4:
						if($masterId != $user->getId()) return $this->src->no_access(
							$this->intl->trans("Acces refusé à la relance de la campagne."),
						);
						break;
					case 5:
						if($masterId != $user->getId()) return $this->src->no_access(
							$this->intl->trans("Acces refusé à la relance de la campagne."),
						);
						break;
					default:
						// code...
						break;
				}
				$brands		= $this->em->getRepository(Brand::class)->findBrandBy(2, ["user"=>$user->getId()]);
			}

			switch ($userType) {
				case 4: $users = [$this->getUser()]; break;
				case 5: $users = [$this->getUser()->getAffiliateManager()]; break;
				default: $users = $user?[$user]:[]; break;
			}

			$senders	= [];
			$sendingAt	= null;
			$statusCode	= -1;
		}

		return $this->renderForm('smscampaign/new.html.twig', [
			'brands'	=> $brands,
			'users'		=> $users,
			'senders'	=> $senders,
			'campaign'	=> $campaign,
			'sendingAt'	=> $sendingAt,
			'statusCode'=> $statusCode,
			'groups'	=> json_encode($groups),
			'allGroup'	=> $allGroup,
			'status'	=> $this->status,
			'brand'		=> $this->brand->get(),
			'pAccess'	=> $this->pAccess,
			'pCreate'	=> $this->pCreateMessage,
			'userType'	=> $userType,
			'pList'		=> $this->pList,
			'pEdit'		=> $this->pEdit,
			'pDelete'	=> $this->pDelete,
			'pageTitle'	=> [
				[$this->intl->trans('Campagnes SMS'), $this->urlg->generate("campaign_sms")],
				[$this->intl->trans('Messages')]
			]
		]);
	}

	#[Route('/created', name: 'campaign_sms_created', methods: ['GET','POST'])]
	public function created(Request $request)
	{
		if(!$this->pCreate) return $this->src->no_access(
			$this->intl->trans("Acces refusé à la création de campagne."),
		);

		if (!$this->isCsrfTokenValid('campaign_create', $request->request->get('_token'))) return $this->src->msg_error(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$response = $this->createCampaign($request);

		if($response) return $response;

		return $this->redirectToRoute("campaign_sms");
	}

	#[Route('/import/file', name: 'smscampaign_import', methods: ['POST'])]
	public function importFile(Request $request)
	{
		/** @var UploadedFile $FILE */
		$FILE	=	$request->files->get('file');

		if(!isset($FILE)) return $this->src->msg_error(
			$this->intl->trans("Fichier d'importation pour une campagne échoué."),
			$this->intl->trans("Echèc de l'importation du fichier."),
			[]
		);

		$return	= $this->src->checkFile($FILE, ["xls", "xlsx", "csv"], 1150028);

		if($return['error'] == true) {
			return $this->src->msg_error(
				$this->intl->trans("Erreur sur le fichier d'importation pour une campagne."),
				$return['info'],
				[]
			);
		}

		$url = $this->src->renameFile($FILE, $this->pathImport, true, $this->pathImport, $this->src->getUniqid());

		return $this->src->msg_success(
			$this->intl->trans("Importation de fichier pour une campagne."),
			$this->intl->trans("Importation de fichier effectuée."),
			[
				"filename"=>$FILE->getFilename(),
				"url"=>$this->pathImport.$url,
			]
		);
	}

	#[Route('/message/correct', name: 'smscampaign_edit', methods: ['POST'])]
	public function editMessage(Request $request)
	{
		$campaignUid = trim($request->request->get("campaign"));
		$positionNbr = (int)trim($request->request->get("position"));
		$phoneNumber = trim($request->request->get("full_number"));
		$messageText = trim($request->request->get("message"));

		$campaign = $this->em->getRepository(SMSCampaign::class)->findOneByUid($campaignUid);

		if(!$campaign || $positionNbr < 0) return $this->src->msg_error(
			$this->intl->trans("Echec de la correction d'un message. Erreur dans la requête."),
			$this->intl->trans("Erreur dans la requête. Rechargez votre page."),
			[],
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

		if(!isset($messages[$positionNbr])) return $this->src->msg_error(
			$this->intl->trans("La position %1% ne se trouve pas dans le fichier '%2%'.", ["%1%"=>$positionNbr, "%2%"=>$campaignFile->getUrl()]),
			$this->intl->trans("La position %1% introuvable dans le fichier."),
			[],
		);

		$lastMessage = $messages[$positionNbr];

		$errors = [];

		$phone_data = $this->brickPhone->getInfosCountryFromCode($phoneNumber);
		if(!$phone_data) $errors[] = $this->intl->trans("Le n° de téléphone %1% indiqué est incorrect.", ["%1%"=>$phoneNumber]);

		$contact = [
			"number"=>$phoneNumber,
			"param1"=>$lastMessage["contact"]["param1"],
			"param2"=>$lastMessage["contact"]["param2"],
			"param3"=>$lastMessage["contact"]["param3"],
			"param4"=>$lastMessage["contact"]["param4"],
			"param5"=>$lastMessage["contact"]["param5"],
			"param6"=>$lastMessage["contact"]["param6"],
		];

		$dataMessage = $this->sMessage->setParameters($messageText, $contact);
		if(!$dataMessage[0]) $errors[] = $this->intl->trans("Le message contient %1% caractères pour %2% page(s).", ["%1%"=>$dataMessage[2],"%2%"=>$dataMessage[3]]);

		$dataAmount = $this->sMessage->getAmountSMS($dataMessage[3], $campaign->getManager(), $phone_data?$phone_data["code"]:"null");
		if(!$dataAmount[0]) $errors[] = $dataAmount[2];

		if($phone_data) $phone_data["operator"] = $this->sMessage->phoneOperator($phone_data["code"], $phoneNumber, $this->brickPhone);

		$lastMessage["updatedAt"]       = (new \DateTimeImmutable())->format("Y-m-d H:i:sP");
		$lastMessage["message"]         = $dataMessage[1];
		$lastMessage["messageAmount"]   = $dataAmount[1];
		$lastMessage["status"]          = count($errors) == 0 ? $this->programming->getCode() : $this->suspend->getCode();
		$lastMessage["phone"]           = str_replace("+","",$phoneNumber);
		$lastMessage["phoneCountry"]    = $phone_data?$phone_data:$this->initDataPhone;
		$lastMessage["errors"]          = implode("; ", $errors);
		$lastMessage["contact"]         = $contact;

		$messages[$positionNbr] = $lastMessage;

		file_put_contents($campaignFile->getUrl(), json_encode($messages));

		list($nbrErrors, $errors, $amount) = $this->checkMessages($messages, $campaign);

		if($nbrErrors > 0)
		{
			return $this->src->msg_warning(
				$this->intl->trans("Relance de la campagne %1% échouée.", ["%1%"=>$campaign->getUid()]),
				$this->intl->trans("Echec du relancement. %1% ligne(s) d'erreur trouvée(s).", ["%1%"=>$nbrErrors]),
				$errors,
			);
		}

		return $this->applyEnableCampaign($campaign, $amount);
	}

	#[Route('/message/delete', name: 'smscampaign_delete', methods: ['POST'])]
	public function deleteMessage(Request $request)
	{
		$campaignUid = trim($request->request->get("campaign"));
		$positionNbr = (int)trim($request->request->get("position"));

		$campaign = $this->em->getRepository(SMSCampaign::class)->findOneByUid($campaignUid);

		if(!$campaign || $positionNbr < 0) return $this->src->msg_error(
			$this->intl->trans("Echec de la suppression d'un message de campagne. Erreur dans la requête."),
			$this->intl->trans("Erreur dans la requête. Rechargez votre page."),
			[],
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

		if($positionNbr > count($messages)) return $this->src->msg_error(
			$this->intl->trans("La position %1% ne se trouve pas dans le fichier '%2%'.", ["%1%"=>$positionNbr, "%2%"=>$campaignFile->getUrl()]),
			$this->intl->trans("La position %1% introuvable dans le fichier.", ["%1%"=>$positionNbr]),
		);

		array_splice($messages, $positionNbr, 1);

		file_put_contents($campaignFile->getUrl(), json_encode($messages));

		list($nbrErrors, $errors, $amount) = $this->checkMessages($messages, $campaign);

		if($nbrErrors > 0)
		{
			return $this->src->msg_warning(
				$this->intl->trans("Relance de la campagne %1% échouée.", ["%1%"=>$campaign->getUid()]),
				$this->intl->trans("Echec du relancement. %1% ligne(s) d'erreur trouvée(s).", ["%1%"=>$nbrErrors]),
				$errors,
			);
		}

		return $this->applyEnableCampaign($campaign, $amount);
	}

	private function createCampaign($request)
	{
		$reqData = $request; // En présence d'un select multiple, utiliser $request->get() pour toutes les récupérations
		$brand = $this->em->getRepository(Brand::class)->findOneByUid($reqData->get("brand"));

		if(!$brand) return $this->src->msg_error(
			$this->intl->trans("Echec lors de la création d'une campagne SMS. Erreur dans requête."),
			$this->intl->trans("La marque n'est pas indiquée."),
		);

		$user = $this->em->getRepository(User::class)->findOneBy([
			"uid"=>$reqData->get("user"),
			"brand"=>$brand,
		]);

		if(!$user) return $this->src->msg_error(
			$this->intl->trans("Echec lors de la création d'une campagne SMS. Utilisateur %1% inconnu sous la marque %2%.", ["%1%"=>$reqData->get("user"), "%2%"=>$brand->getName()]),
			$this->intl->trans("Impossible de retrouver cet utilisateur."),
		);

		$uidSender = $reqData->get("sender");

		if($uidSender == "" || $uidSender == null){
			$sender = $brand->getDefaultSender();
		}
		else{
			$sender = $this->em->getRepository(Sender::class)->findOneBy([
				"uid"=>$reqData->get("sender"),
				"manager"=>$user,
			]);
		}

		if(!$sender) return $this->src->msg_error(
			$this->intl->trans("Echec lors de la création d'une campagne SMS. L'identifiant %1% inconnu pour l'utilisateur %2%.", ["%1%"=>$reqData->get("sender"), "%2%"=>$user->getEmail()]),
			$this->intl->trans("Impossible de retrouver cet identifiant."),
		);

		$phones		= [];
		$allMessages= [];
		$counting	= [
			"persons"=>0,
			"pages"=>0
		];
		$name		= trim($reqData->get("name", null));
		$dateTime	= trim($reqData->get("datetime", null));
		$timezone	= trim($reqData->get("timezone", (new \DateTime())->format("P")));
		$type		= (int)$reqData->get("type", "1");
		$message	= trim($reqData->get("messageText", ""));
		$saveMode	= trim($reqData->get("saveMode", "live"));
		$campaignUid= trim($reqData->get("id"));

		try {
			if($dateTime == "" || $dateTime == null)
			{
				$sendingAt = new \DateTimeImmutable("now");
			}else{
				$dateTime   .= ":00";

				$sendingAt = new \DateTimeImmutable($dateTime." ".$timezone);
				$sendingAt = $sendingAt->setTimezone(new \DateTimeZone(date_default_timezone_get()));
			}
		}
		catch (\Exception $e) {
			return $this->src->msg_error(
				$this->intl->trans("Echec lors de la création d'une campagne SMS. Le format de la date est incorrect."),
				$this->intl->trans("Veuillez renseigner une date correcte."),
			);
		}

		$thisStatusCode	= 10;
		$campaign		= $this->em->getRepository(SMSCampaign::class)->findOneByUid($campaignUid);

		if($campaign){
			$thisStatusCode = $campaign->getStatus()->getCode();

			if($thisStatusCode != 0 && $thisStatusCode != 5 && $thisStatusCode != 10){
				return $this->src->msg_error(
					$this->intl->trans("Campagne %1% non modifiable.", ["%1%"=>$campaign->getUid()]),
					$this->intl->trans("Cette campagne ne peut pas être modifiée."),
				);
			}
		}

		switch (true) {
			case ($reqData->get("groups", "") != ""):
				$phones = $this->campaignByGroupContacts($request->get("groups"));
				break;
			case ($reqData->get("phones", "") != ""):
				$phones = $this->campaignByWriteNumbers($reqData->get("phones", ""));
				break;
			case ($reqData->get("fileUrl", "") != ""):
				$phones = $this->campaignByImportation($reqData->get("fileUrl", ""));
				break;
			default: break;
		}

		$campaignFile	= null;

		if($campaign){
			$campaignFile = $this->em->getRepository(SMSMessageFile::class)->findOneByCampaign($campaign);

			if(!$campaignFile) return $this->src->msg_error(
				$this->intl->trans("Le fichier de la campagne %1% n'est plus disponible dans la base de données.", ["%1%"=>$campaign->getUid()]),
				$this->intl->trans("Cette campagne n'est plus utilisable. Veuillez créer une autre."),
			);

			if(!is_file($campaignFile->getUrl())) return $this->src->msg_error(
				$this->intl->trans("Le fichier de la campagne %1% est introuvable.", ["%1%"=>$campaign->getUid()]),
				$this->intl->trans("Cette campagne n'est plus utilisable. Veuillez créer une autre."),
			);

			$allMessages = json_decode(file_get_contents($campaignFile->getUrl()), true);
		}

		if($phones == [] && $allMessages == []) return $this->src->msg_error(
			$this->intl->trans("Création de campagne échouée."),
			$this->intl->trans("Aucun contact trouvé."),
		);

		if($reqData->get("saveContacts", "false") == "true") $this->saveAsContacts($phones, $user);

		$pattern = "/({param1}|{param2}|{param3}|{param4}|{param5}|{param6})/i";
		$isParam = (preg_match($pattern, $message)) ? true : false;

		if(!$campaign){
			$campaign = new SMSCampaign();
			$campaign->setSendingAt($sendingAt)
				->setCreatedAt(new \DateTimeImmutable())
				->setUpdatedAt(null)
				->setMessage($message)
				->setCampaignAmount(0)
				->setSmsType($type)
				->setTimezone($timezone)
				->setIsParameterized($isParam)
				->setManager($user)
				->setSender($sender->getName())
				->setStatus($this->brouillon)
				->setUid($this->src->getUniqid())
				->setName($name)
				->setCreateBy($this->getUser())
			;
		}
		else {
			$campaign->setSendingAt($sendingAt)
				->setUpdatedAt(new \DateTimeImmutable())
				->setSmsType($type)
				->setTimezone($timezone)
				->setSender($sender->getName())
				->setName($name)
				->setCreateBy($this->getUser())
			;

			if($thisStatusCode == 10){
				$campaign->setMessage($message)
					->setCampaignAmount(0)
					->setIsParameterized($isParam)
					->setManager($user)
					->setStatus($this->brouillon)
				;
			}
		}

		$this->em->persist($campaign);

		$lastPhones = [];

		if($thisStatusCode != 10){
			for ($i=0; $i < count($allMessages); $i++) {
				$allMessages[$i]["sendingAt"]	= $sendingAt->format("Y-m-d H:i:sP");
				$allMessages[$i]["updatedAt"]	= (new \DateTimeImmutable())->format("Y-m-d H:i:sP");
				$allMessages[$i]["smsType"]		= $type;
				$allMessages[$i]["timezone"]	= $timezone;
				$allMessages[$i]["sender"]		= $sender->getName();
				$allMessages[$i]["createBy"]	= $this->getUser()->getId();
			}

			if($campaignFile){
				$fileUrl = $campaignFile->getUrl();
				file_put_contents($fileUrl, json_encode($allMessages));
				$campaignFile->setUpdatedAt(new \DateTimeImmutable());
				$this->em->persist($campaignFile);
			}

			return $this->src->msg_success(
				$this->intl->trans("Campagne %1% Mise à jour.", ["%1%"=>$campaign->getUid()]),
				$this->intl->trans("Campagne modifiée avec succès."),
			);
		}
		else if(count($allMessages) > 0){
			foreach ($allMessages as $oneMessage) {
				$lastPhones[] = $oneMessage["contact"];
			}
		}

		$this->em->flush();

		$merge		= array_merge($lastPhones, $phones);
		$phones		= $merge;
		$data		= [];
		$position	= 0;
		$amountFull	= 0;
		$dataError	= [];
		foreach ($phones as $key => $phone){
			$errors = [];

			$phone_data = $this->brickPhone->getInfosCountryFromCode($phone["number"]);
			if(!$phone_data) $errors[] = $this->intl->trans("Le n° de téléphone %1% indiqué est incorrect.", ["%1%"=>$phone["number"]]);

			$dataMessage = $isParam ? $this->sMessage->setParameters($message, $phone) : $this->sMessage->trueLength($message);
			if(!$dataMessage[0]) $errors[] = $this->intl->trans("Le message contient %1% caractères pour %2% page(s).", ["%1%"=>$dataMessage[2],"%2%"=>$dataMessage[3]]);

			if(count($errors) == 0){
				$dataAmount = $this->sMessage->getAmountSMS($dataMessage[3], $user, $phone_data?$phone_data["code"]:"null");
				if(!$dataAmount[0]) $errors[] = $dataAmount[2];
			}

			if($phone_data) $phone_data["operator"] = $this->sMessage->phoneOperator($phone_data["code"], $phone["number"], $this->brickPhone);

			$data[] = [
				"sendingAt"			=> $sendingAt->format("Y-m-d H:i:sP"),
				"createdAt"			=> (new \DateTimeImmutable())->format("Y-m-d H:i:sP"),
				"updatedAt"			=> null,
				"message"			=> $dataMessage[1],
				"messageAmount"		=> $dataAmount[1],
				"smsType"			=> $type,
				"isParameterized"	=> $isParam,
				"timezone"			=> $timezone,
				"manager"			=> $user->getId(),
				"campaign"			=> $campaign->getId(),
				"status"			=> count($errors) == 0 ? $this->programming->getCode() : $this->suspend->getCode(),
				"sender"			=> $sender->getName(),
				"phone"				=> str_replace("+","",$phone["number"]),
				"phoneCountry"		=> $phone_data?$phone_data:$this->initDataPhone,
				"uid"				=> $this->src->getUniqid(),
				"originMessage"		=> $message,
				"createBy"			=> $this->getUser()->getId(),
				"errors"			=> implode("; ", $errors),
				"contact"			=> $phone,
				"pages"				=> $dataMessage[3]
			];

			$counting["persons"]++;
			$counting["pages"]	+= $dataMessage[3];

			if(count($errors) > 0){
				$dataError[] = [
					count($data),
					$phone["number"],
					implode("; ", $errors),
					[$phone,$phone_data?$phone_data:$this->initDataPhone],
					$dataMessage[1],
					[$position, count($data)-1, $campaign->getUid()],
				];
				$position++;
			}
			else $amountFull += $dataAmount[1];
		}

		if(!$campaignFile){
			$chemin = $this->pathRacine.$brand->getName()."/".$user->getEmail()."/".$sender->getName()."/";

			if(!is_dir($chemin)) mkdir($chemin, 0777, true);

			$fileName = (new \DateTimeImmutable())->format("YmdHis").".json";

			$handle = fopen($chemin.$fileName, "c+b");
			fclose($handle);

			$fileUrl = $chemin.$fileName;
		}
		else{
			$fileUrl = $campaignFile->getUrl();
		}

		file_put_contents($fileUrl, json_encode($data));

		$campaign->setCampaignAmount($amountFull)->setCounting($counting);
		$this->em->persist($campaign);

		if(!$campaignFile){
			$SMSMessageFile = new SMSMessageFile();
			$SMSMessageFile->setName($fileName)
				->setUrl($chemin.$fileName)
				->setCreatedAt(new \DateTimeImmutable())
				->setUpdatedAt(null)
				->setCampaign($campaign)
			;
			$this->em->persist($SMSMessageFile);
			// $this->em->flush();
		}
		//campaign/FASTERMESSAGE/support@fastermessage.com/FASTERMSG

		if($reqData->get("saveMode") != "live")
		{
			return $this->src->msg_success(
				$this->intl->trans("Enregistrement en brouillon de la campagne %1%.", ["%1%"=>$campaign->getUid()]),
				$this->intl->trans("Campagne enregistrée dans le brouillon."),
			);
		}

		$nbrError = count($dataError);

		if($nbrError > 0)
		{
			return $this->src->msg_warning(
				$this->intl->trans("Création de la campagne %1%. Des erreurs trouvées.", ["%1%"=>$campaign->getUid()]),
				$this->intl->trans("Campagne créée, mais SUSPENDUE. %1% ligne(s) d'erreur trouvée(s).", ["%1%"=>$nbrError]),
				$dataError,
			);
		}

		$this->em->flush();

		return $this->applyEnableCampaign($campaign, $amountFull);
	}

	private function campaignByGroupContacts($groups)
	{
		$phonesData = [];
		$i = 0;
		foreach ($groups as $group) {
			$contactsGroup = $this->em->getRepository(ContactGroup::class)->findOneBy(["uid"=>$group]);

			if($contactsGroup){
				foreach ($contactsGroup->getContacts() as $contact) {
					$phonesData[$i]["number"] = "+".str_replace("+","",$contact->getPhone());
					$phonesData[$i]["param1"] = $contact->getField1();
					$phonesData[$i]["param2"] = $contact->getField2();
					$phonesData[$i]["param3"] = $contact->getField3();
					$phonesData[$i]["param4"] = $contact->getField4();
					$phonesData[$i]["param5"] = $contact->getField5();
					$phonesData[$i]["param6"] = "";

					$i++;
				}
			}
		}
		return $phonesData;
	}

	private function campaignByWriteNumbers($phoneString)
	{
		$phonesData = [];
		$rows = ($phoneString == "" || $phoneString == null) ? [] : explode(";", $phoneString);
		for ($i=0; $i < count($rows); $i++) {
			$phonesData[$i]["number"] = "+".str_replace("+","",$rows[$i]);
			$phonesData[$i]["param1"] = "";
			$phonesData[$i]["param2"] = "";
			$phonesData[$i]["param3"] = "";
			$phonesData[$i]["param4"] = "";
			$phonesData[$i]["param5"] = "";
			$phonesData[$i]["param6"] = "";
		}

		return $phonesData;
	}

	private function campaignByImportation($fileUrl)
	{
		if(!is_file($fileUrl)) return [];

		$phonesData = [];

		/**  Identify the type of $fileUrl  **/
		$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($fileUrl);
		/**  Create a new Reader of the type that has been identified  **/
		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
		$reader->setReadDataOnly(true);
		/**  Load $inputFileType to a Spreadsheet Object  **/
		$spreadsheet = $reader->load($fileUrl);

		$rows = $spreadsheet->getActiveSheet()->toArray();

		$start = -1;

		$A1 = isset($rows[0][0]) ? $rows[0][0] : "";
		$A2 = isset($rows[1][0]) ? $rows[1][0] : "";
		if(is_numeric($A1)) $start = 0; else if(is_numeric($A2)) $start = 1;

		if($start == -1){
			if(is_file($fileUrl)) unlink($fileUrl);

			return [];
		}

		$key = 0;
		for ($i=$start; $i < count($rows); $i++) {
			$phone = isset($rows[$i][0]) ? $rows[$i][0] : "";
			// $phone_data = ($i == 0) ? $this->brickPhone->getInfosCountryFromCode($phone) : true;
			// //$phone_data = $this->brickPhone->getInfosCountryFromCode($phone);
			// if($phone_data)
			// {
				$phonesData[$key]["number"] = "+".str_replace("+","",$phone);
				$phonesData[$key]["param1"] = isset($rows[$i][1]) ? $rows[$i][1] : "";
				$phonesData[$key]["param2"] = isset($rows[$i][2]) ? $rows[$i][2] : "";
				$phonesData[$key]["param3"] = isset($rows[$i][3]) ? $rows[$i][3] : "";
				$phonesData[$key]["param4"] = isset($rows[$i][4]) ? $rows[$i][4] : "";
				$phonesData[$key]["param5"] = isset($rows[$i][5]) ? $rows[$i][5] : "";
				$phonesData[$key]["param6"] = isset($rows[$i][6]) ? $rows[$i][6] : "";

				$key++;
			// }
		}

		//$spreadsheet->getCell('A1')->getValue();
		if(is_file($fileUrl)) unlink($fileUrl);

		return $phonesData;
	}

	private function checkMessages($messages, $campaign)
	{
		$dataError = [];
		$position  = 0;
		$amountFull = 0;
		foreach ($messages as $key => $message) {
			if($message["status"] == $this->suspend->getCode())
			{
				$dataError[] = [
					$key+1,
					$message["contact"]["number"],
					$message["errors"],
					[$message["contact"], $message["phoneCountry"]],
					$message["message"],
					[$position, $key, $campaign->getUid()],
				];
				$position++;
			}
			$amountFull += $message["messageAmount"];
		}

		return [count($dataError), $dataError, $amountFull];
	}

	private function applyEnableCampaign($campaign, $amount)
	{
		$user = $campaign->getManager();

		$lastBalance = $user->getBalance();
		if($lastBalance >= $amount || $user->isPostPay()){
			$user->setBalance($lastBalance - $amount);
			$this->em->persist($user);
		}else{
			return $this->src->msg_error(
				$this->intl->trans("La campagne '%1%' échouée. Balance insuffisante.", ["%1%"=>$campaign->getUid()]),
				$this->intl->trans("Votre balance est insuffisante."),
			);
		}

		$sendingAt = $campaign->getSendingAt();

		$diff = (new \DateTimeImmutable())->diff($sendingAt);

		$status = $diff->invert == 1 ? $this->waiting : $this->programming;

		$campaign->setStatus($status)
			->setCampaignAmount($amount)
			->setUpdatedAt(new \DateTimeImmutable());
		$this->em->persist($campaign);

		$this->src->addBalanceChange($user, $lastBalance, -$amount, $this->intl->trans("Lancement de campagne SMS '%1%'", ["%1%"=>$campaign->getName()]), $campaign->getUid());

		return $this->src->msg_success(
			$this->intl->trans("La campagne '%1%' passe en %2%.", ["%1%"=>$campaign->getUid(), "%2%"=>$status->getName()]),
			$this->intl->trans("La campagne %1% dans la liste avec succès.", ["%1%"=>strtolower($status->getName())]),
		);
	}

	private function applyDisableCampaign($campaign, $status)
	{
		$user	= $campaign->getManager();
		$amount	= $campaign->getCampaignAmount();

		$lastBalance = $user->getBalance();
		$user->setBalance($lastBalance + $amount);
		$this->em->persist($user);

		$campaign->setStatus($status)
			->setUpdatedAt(new \DateTimeImmutable());
		$this->em->persist($campaign);

		$this->src->addBalanceChange($user, $lastBalance, $amount, $this->intl->trans("Suspenssion de campagne SMS '%1%'", ["%1%"=>$campaign->getName()]), $campaign->getUid());

		return $this->src->msg_success(
			$this->intl->trans("La campagne '%1%' passe en %2%.", ["%1%"=>$campaign->getUid(), "%2%"=>$status->getName()]),
			$this->intl->trans("La campagne %1% avec succès.", ["%1%"=>strtolower($status->getName())]),
		);
	}

	private function saveAsContacts($contacts, $user)
	{
		$group = new ContactGroup();
		$group->setManager($user)
			->setName((new \DateTime())->format("YmdHis"))
			->setUid($this->src->getUniqid())
			->setCreatedAt(new \DateTimeImmutable())
			->setUpdatedAt(null)
			->setField1("Champ1")
			->setField2("Champ2")
			->setField3("Champ3")
			->setField4("Champ4")
			->setField5("Champ5")
			->setAdmin($this->getUser())
			;
		$this->em->persist($group);
		$this->em->flush();

		foreach ($contacts as $contact) {
			$dataPhone = $this->brickPhone->getInfosCountryFromCode($contact["number"]);
			$phone = new Contact();
			$phone->setUid($this->src->getUniqid())
				->setPhone($contact["number"])
				->setIsImported(true)
				->setPhoneCountry($dataPhone?$dataPhone:$this->initDataPhone)
				->setCreatedAt(new \DateTimeImmutable())
				->setUpdatedAt(null)
				->setField1($contact["param1"])
				->setField2($contact["param2"])
				->setField3($contact["param3"])
				->setField4($contact["param4"])
				->setField5($contact["param5"])
				->setContactGroup($group)
			;
			$this->em->persist($phone);
		}
		$this->em->flush();

		return $group;
	}
}
