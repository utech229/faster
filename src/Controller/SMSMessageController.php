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
#[Route('/{_locale}/sms/messages')]
class SMSMessageController extends AbstractController
{

	private $initDataPhone	= [
		"dial_code"=>"null",
		"code"=>"null",
		"name"=>"null",
		"operator"=>"null",
	];

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
			"SMSS0", "SMSS1", "SMSS2", "SMSS3", "SMSS4",
		];
		$this->pAccess	=	$this->src->checkPermission($this->permission[0]);
		$this->pCreate	=	$this->src->checkPermission($this->permission[1]);
		$this->pList	=	$this->src->checkPermission($this->permission[2]);
		$this->pEdit	=	$this->src->checkPermission($this->permission[3]);
		$this->pDelete	=	$this->src->checkPermission($this->permission[4]);

		$this->status	=   [
			"0"=>$this->src->status(0),
			"1"=>$this->src->status(1),
			"8"=>$this->src->status(8),
			"9"=>$this->src->status(9),
			"5"=>$this->src->status(5),
		];

		$this->programming	= $this->src->status(0);
		$this->progressing	= $this->src->status(1);
		$this->suspend		= $this->src->status(5);
		$this->waiting		= $this->src->status(2);
	}

	#[Route('', name: 'message_sms', methods: ['GET'])]
	public function index(Request $request): Response
	{
		if(!$this->pAccess)
		{
			$this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
			$this->src->addLog($this->intl->trans("Acces refusé aux messages"), 417);
			return $this->redirectToRoute("app_home");
		}

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		$from = $request->get("from", null);
		$of   = $request->get("of", null);

		if($from == "campaign" && $of == null) return $this->redirectToRoute("campaign_sms");

		$brands = $this->em->getRepository(Brand::class)->findBrandBy($userType, $userRequest);

		switch ($userType) {
			case 4: $users = [$this->getUser()]; break;
			case 5: $users = [$this->getUser()->getAffiliateManager()]; break;
			default: $users = []; break;
		}

		switch ($from) {
			case 'api':
				$pageTitle = [
					[$this->intl->trans('Messages SMS API')]
				];
				break;
			case 'campaign':
				$pageTitle = [
					[$this->intl->trans('Campagnes SMS'), $this->urlg->generate("campaign_sms")],
					[$this->intl->trans('Messages')]
				];
				break;

			default:
				$pageTitle = [
					[$this->intl->trans('Messages SMS')]
				];
				break;
		}

		return $this->renderForm('smsmessage/index.html.twig', [
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
			'pageTitle' => $pageTitle,
			'lfrom'     => $from,
			'lof'       => $of
		]);
	}

	#[Route('/get', name: 'message_sms_listen', methods: ['GET', 'POST'])]
	public function listen(Request $request)
	{
		$session = $this->getUser();

		if(!$this->pAccess) return $this->src->no_access($this->intl->trans("Acces refusé à la récupération des messages"));

		if (!$this->isCsrfTokenValid('message', $request->request->get('_token'))) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$uidBrand   = trim($request->request->get("brand"));
		$uidManager = trim($request->request->get("manager"));
		$sender     = trim($request->request->get("sender"));
		$uidStatus  = trim($request->request->get("status"));
		$periode    = trim($request->request->get("periode"));
		$from       = trim($request->request->get("lfrom", null));
		$of         = trim($request->request->get("lof", null));

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
					$sendingAt = (new \DateTime($message["sendingAt"]))->setTimezone(new \DateTimeZone($message["timezone"]));
					$data[$key][] = $message["phone"];
					$data[$key][] = $message["sender"];
					$data[$key][] = $sendingAt->format("Y-m-d H:i:sP");
					$data[$key][] = [
						"code"=>$myStatus->getCode(),
						"label"=>$myStatus->getLabel(),
						"name"=>$myStatus->getName(),
						"uid"=>$myStatus->getUid(),
					];
					$data[$key][] = $message["pages"];
					$data[$key][] = $message["phoneCountry"];
					$data[$key][] = $message["message"];
					$data[$key][] = $message["createdAt"];
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
			$data[$key][] = $message->getCreatedAt()->format("Y-m-d H:i:sP");
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

	#[Route('/create', name: 'message_sms_create', methods: ['GET', 'POST'])]
	public function create(Request $request)
	{
		if(!$this->pCreate) return $this->src->no_access(
			$this->intl->trans("Acces refusé à la création de message."),
		);

		$uidBrand = trim($request->request->get('brand', ''));
		$uidUser = trim($request->request->get('user', ''));
		$uidSender = trim($request->request->get('sender', ''));
		$type = trim($request->request->get('type', '1'));
		$datetime = trim($request->request->get('datetime', (new \DateTime())->format("Y-m-d H:i")));
		$timezone = trim($request->request->get('timezone', (new \DateTime())->format("P")));
		$phone = trim($request->request->get('full_number', ''));
		$message = trim($request->request->get('message', ''));
		$token = trim($request->request->get('_token', ''));

		if (!$this->isCsrfTokenValid('messageCreate', $token)) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$brand = $this->em->getRepository(Brand::class)->findOneByUid($uidBrand);

		if(!$brand) return $this->src->msg_error(
			$this->intl->trans("Echec lors de la création du SMS. Erreur dans requête."),
			$this->intl->trans("La marque n'est pas indiquée."),
		);

		$user = $this->em->getRepository(User::class)->findOneBy([
			"uid"=>$uidUser,
			"brand"=>$brand,
		]);

		if(!$user) return $this->src->msg_error(
			$this->intl->trans("Echec lors de la création du SMS. Utilisateur %1% inconnu sous la marque %2%.", ["%1%"=>$uidUser, "%2%"=>$brand->getName()]),
			$this->intl->trans("Impossible de retrouver cet utilisateur."),
		);

		if($uidSender == "" || $uidSender == null){
			$sender = $brand->getDefaultSender();
		}
		else{
			$sender = $this->em->getRepository(Sender::class)->findOneBy([
				"uid"=>$uidSender,
				"manager"=>$user,
			]);
		}

		if(!$sender) return $this->src->msg_error(
			$this->intl->trans("Echec lors de la création du SMS. L'identifiant %1% inconnu pour l'utilisateur %2%.", ["%1%"=>$uidSender, "%2%"=>$user->getEmail()]),
			$this->intl->trans("Impossible de retrouver cet expéditeur."),
		);

		try {
			if($datetime == "" || $datetime == null)
			{
				$sendingAt = new \DateTimeImmutable("now");
			}else{
				$datetime   .= ":00";

				$sendingAt = new \DateTimeImmutable($datetime." ".$timezone);
				$sendingAt = $sendingAt->setTimezone(new \DateTimeZone(date_default_timezone_get()));
			}
		} catch (\Exception $e) {
			return $this->src->msg_error(
				$this->intl->trans("Echec lors de la création du SMS. Le format de la date est incorrect."),
				$this->intl->trans("Veuillez renseigner une date correcte."),
				[],
			);
		}

		$pattern = "/({param1}|{param2}|{param3}|{param4}|{param5}|{param6})/i";
		$isParam = preg_match($pattern, $message) ? true : false;

		$errors = [];

		$phone_data = $this->brickPhone->getInfosCountryFromCode($phone);
		if(!$phone_data) $errors[] = $this->intl->trans("Le n° de téléphone %1% indiqué est incorrect.", ["%1%"=>$phone]);

		$dataMessage = $this->sMessage->trueLength($message);
		if(!$dataMessage[0]) $errors[] = $this->intl->trans("Le message contient %1% caractères pour %2% page(s).", ["%1%"=>$dataMessage[2],"%2%"=>$dataMessage[3]]);

		$dataAmount = $this->sMessage->getAmountSMS($dataMessage[3], $user, $phone_data?$phone_data["code"]:"null");
		if(!$dataAmount[0]) $errors[] = $dataAmount[2];

		if($phone_data) $phone_data["operator"] = $this->sMessage->phoneOperator($phone_data["code"], $phone, $this->brickPhone);

		if(count($errors) > 0){
			return $this->src->msg_error(
				$this->intl->trans("Echec création de SMS. Des erreurs trouvées."),
				implode("; ", $errors),
			);
		}

		$smsMessage = new SMSMessage();
		$smsMessage->setSendingAt($sendingAt)
			->setCreatedAt(new \DateTimeImmutable())
			->setUpdatedAt(null)
			->setMessage($dataMessage[1])
			->setMessageAmount($dataAmount[1])
			->setSmsType($type)
			->setIsParameterized($isParam)
			->setTimezone($timezone)
			->setManager($user)
			->setCampaign(null)
			->setStatus($this->suspend)
			->setSender($sender->getName())
			->setPhone(str_replace("+","",$phone))
			->setPhoneCountry($phone_data?$phone_data:$this->initDataPhone)
			->setUid($this->src->getUniqid())
			->setOriginMessage($message)
			->setCreateBy($this->getUser()->getEmail())
			->setCreateFrom(null)
			->setErrors(implode("; ", $errors))
			->setContact([
				"number"=>$phone,
				"param1"=>"",
				"param2"=>"",
				"param3"=>"",
				"param4"=>"",
				"param5"=>"",
				"param6"=>"",
			])
			->setPages($dataMessage[3])
		;

		$this->em->persist($smsMessage);
		$this->em->flush();

		return $this->applyEnableMessage($smsMessage);
	}

	#[Route('/disable', name: 'message_sms_disable', methods: ['GET','POST'])]
	public function disableCampaign(Request $request)
	{
		if(!$this->pEdit) return $this->src->no_access(
			$this->intl->trans("Acces refusé à la suspension de l'envoi d'un message'."),
		);

		if (!$this->isCsrfTokenValid('message', $request->request->get('_token'))) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$message = $this->em->getRepository(SMSMessage::class)->findOneByUid($request->request->get('message'));

		if(!$message || $message->getStatus()->getCode() != 0) return $this->src->msg_error(
			$this->intl->trans("Suspension du message rejetée. Erreur dans la requête."),
			$this->intl->trans("La requête n'est pas traitée. Veuillez reprendre."),
		);

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		switch ($userType) {
			case 1:
				if($masterId != $message->getManager()->getAccountManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suspension de la campagne."),
				);
				break;
			case 2:
				if($masterId != $message->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suspension de la campagne."),
				);
				break;
			case 3:
				if($masterId != $message->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suspension de la campagne."),
				);
				break;
			case 4:
				if($masterId != $message->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suspension de la campagne."),
				);
				break;
			case 5:
				if($masterId != $message->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suspension de la campagne."),
				);
				break;
			default:
				// code...
				break;
		}

		return $this->applyDisableMessage($message, $this->suspend);
	}

	#[Route('/delete', name: 'message_sms_delete', methods: ['GET','POST'])]
	public function deleteCampaign(Request $request)
	{
		if(!$this->pDelete) return $this->src->no_access(
			$this->intl->trans("Acces refusé à la suppression d'un message."),
		);

		if (!$this->isCsrfTokenValid('message', $request->request->get('_token'))) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$message = $this->em->getRepository(SMSMessage::class)->findOneByUid($request->request->get('message'));

		if(!$message) return $this->src->msg_error(
			$this->intl->trans("Suppression du message rejetée. Erreur dans la requête."),
			$this->intl->trans("La requête n'est pas traitée. Veuillez reprendre."),
		);

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		switch ($userType) {
			case 1:
				if($masterId != $message->getManager()->getAccountManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suppression de message."),
				);
				break;
			case 2:
				if($masterId != $message->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suppression de message."),
				);
				break;
			case 3:
				if($masterId != $message->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suppression de message."),
				);
				break;
			case 4:
				if($masterId != $message->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suppression message."),
				);
				break;
			case 5:
				if($masterId != $message->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la suppression message."),
				);
				break;
			default:
				// code...
				break;
		}

		if($message->getStatus()->getCode() == 0)
		{
			$manager	= $message->getManager();

			$myBalance	= $manager->getBalance();

			$amount		= $message->getMessageAmount();

			$manager->setBalance($myBalance + $amount);

			$this->em->persist($manager);

			$this->src->addBalanceChange($manager, $myBalance, $amount, $this->intl->trans("Suppression du message SMS au '%1%'", ["%1%"=>$message->getPhone()]), $message->getUid());
		}

		$this->em->remove($message);

		return $this->src->msg_success(
			$this->intl->trans("Suppression du message %1%.", ["%1%"=>$message->getUid()]),
			$this->intl->trans("Le message vient d'être supprimée avec succès."),
		);
	}

	#[Route('/enable', name: 'message_sms_enable', methods: ['GET','POST'])]
	public function enableCampaign(Request $request)
	{
		if(!$this->pEdit) return $this->src->no_access(
			$this->intl->trans("Acces refusé à l'activation d'envoi de SMS."),
		);

		if (!$this->isCsrfTokenValid('message', $request->request->get('_token'))) return $this->src->msg_warning(
			$this->intl->trans("Clé CSRF invalide."),
			$this->intl->trans("Clé CSRF invalide. Rechargez la page."),
		);

		$message = $this->em->getRepository(SMSMessage::class)->findOneBy([
			"uid"=>$request->request->get('message'),
			"status"=>$this->suspend
		]);

		if(!$message) return $this->src->msg_error(
			$this->intl->trans("Relance du message rejetée. Erreur dans la requête."),
			$this->intl->trans("La requête n'est pas traitée. Veuillez reprendre."),
		);

		list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

		switch ($userType) {
			case 1:
				if($masterId != $message->getManager()->getAccountManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la relance de la campagne."),
				);
				break;
			case 2:
				if($masterId != $message->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la relance de la campagne."),
				);
				break;
			case 3:
				if($masterId != $message->getManager()->getBrand()->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la relance de la campagne."),
				);
				break;
			case 4:
				if($masterId != $message->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la relance de la campagne."),
				);
				break;
			case 5:
				if($masterId != $message->getManager()->getId()) return $this->src->no_access(
					$this->intl->trans("Acces refusé à la relance de la campagne."),
				);
				break;
			default:
				// code...
				break;
		}

		return $this->applyEnableMessage($message);
	}

	private function applyEnableMessage($message)
	{
		$user	= $message->getManager();
		$amount	= $message->getMessageAmount();

		$lastBalance = $user->getBalance();
		if($lastBalance >= $amount || $user->isPostPay()){
			$user->setBalance($lastBalance - $amount);
			$this->em->persist($user);
		}else{
			return $this->src->msg_error(
				$this->intl->trans("Le message '%1%' échouée. Balance insuffisante.", ["%1%"=>$message->getUid()]),
				$this->intl->trans("Votre balance est insuffisante."),
			);
		}

		$sendingAt = $message->getSendingAt();

		$diff = (new \DateTimeImmutable())->diff($sendingAt);

		$status = $diff->invert == 1 ? $this->waiting : $this->programming;

		$message->setStatus($status)
			->setMessageAmount($amount)
			->setUpdatedAt(new \DateTimeImmutable());
		$this->em->persist($message);

		$this->src->addBalanceChange($user, $lastBalance, -$amount, $this->intl->trans("Lancement du message SMS au '%1%'", ["%1%"=>$message->getPhone()]), $message->getUid());

		return $this->src->msg_success(
			$this->intl->trans("Le message '%1%' passe en %2%.", ["%1%"=>$message->getUid(), "%2%"=>$status->getName()]),
			$this->intl->trans("Message %1% dans la liste avec succès.", ["%1%"=>$status->getName()]),
		);
	}

	private function applyDisableMessage($message, $status)
	{
		$user	= $message->getManager();
		$amount	= $message->getMessageAmount();

		$lastBalance = $user->getBalance();
		$user->setBalance($lastBalance + $amount);
		$this->em->persist($user);

		$message->setStatus($status)
			->setUpdatedAt(new \DateTimeImmutable());
		$this->em->persist($message);

		$this->src->addBalanceChange($user, $lastBalance, $amount, $this->intl->trans("Suspension du message SMS au '%1%'", ["%1%"=>$message->getPhone()]), $message->getUid());

		return $this->src->msg_success(
			$this->intl->trans("Le message '%1%' passe en %2%.", ["%1%"=>$message->getUid(), "%2%"=>$status->getName()]),
			$this->intl->trans("Le message %1% avec succès.", ["%1%"=>$status->getName()]),
		);
	}
}
