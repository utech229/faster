<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Sender;
use App\Entity\Status;
use App\Entity\Brand;
use App\Entity\SMSCampaign;
use App\Service\Services;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('/{_locale}/filter')]
class FilterController extends AbstractController
{
	public function __construct(TranslatorInterface $intl, Services $src, EntityManagerInterface $em)
	{
	   $this->intl          = $intl;
	   $this->src           = $src;
	   $this->em            = $em;
	   $this->status		= [
		   $src->status(3)
	   ];
	}

	#[Route('/user/get', name: 'filter_user', methods: ['POST'])]
	public function user(Request $request)
	{
		$data = [
			"results"=>[
				["id"=>"", "text"=>""],
			]
		];

		if (!$this->isCsrfTokenValid('', $request->request->get('token'))){
			$this->src->addLog($this->intl->trans("Echec de la récupération des utilisateurs. Clé CSRF invalide."));
			return new JsonResponse($data);
		}

 		list($ut, $mId, $rq) = $this->src->checkThisUser($this->src->checkPermission("UTI2"));

		$users = $ut == 5 ? [$this->getUser()->getAffiliateManager()] : [$this->getUser()];

		if($ut < 4){
			$level = (int)$request->request->get("level", "1");
			$brand = $this->em->getRepository(Brand::class)->findOneByUid($request->request->get("brand", ""));

			if(!$brand) return new JsonResponse($data);

			foreach ($users as $key => $user) {
				if(($level == 1 && $user->getAffiliateManager() == null) || ($level == 2 && $user->getAffiliateManager() != null) || ($level != 1 && $level != 2))
				{
					$data["results"][] = [
						"id"=>$user->getUid(),
						"text"=>$user->getEmail(),
					];
				}
			}
		}
		
		return new JsonResponse($data);
	}

	#[Route('/sender/get', name: 'filter_sender', methods: ['POST'])]
	public function sender(Request $request)
	{
		$data = [
			"results"=>[
				["id"=>"", "text"=>""],
			]
		];
		if (!$this->isCsrfTokenValid('', $request->request->get('token'))){
			$this->src->addLog($this->intl->trans("Echec de la récupération des senders. Clé CSRF invalide."));
			return new JsonResponse($data);
		}

		$user = $this->em->getRepository(User::class)->findOneByUid($request->request->get("user"));

		if(!$user) return new JsonResponse($data);

		//$senders = $this->em->getRepository(Sender::class)->findBy(["manager"=>$user, "status"=>$this->status]);

		foreach ($user->getSenders() as $key => $sender) {
			if($sender->getStatus() == $this->src->status(3)){
				$data["results"][] = [
					"id"=>$sender->getUid(),
					"text"=>$sender->getName(),
				];
			}
		}

		return new JsonResponse($data);
	}

	#[Route('/sender/get_names', name: 'filter_sender_names', methods: ['POST'])]
	public function sender_names(Request $request)
	{
		$data = [
			"results"=>[
				["id"=>"", "text"=>""],
			]
		];
		if (!$this->isCsrfTokenValid('', $request->request->get('token'))){
			$this->src->addLog($this->intl->trans("Echec de la récupération des senders. Clé CSRF invalide."));
			return new JsonResponse($data);
		}

		$user = $this->em->getRepository(User::class)->findOneByUid($request->request->get("user"));

		if(!$user) return new JsonResponse($data);

		//$senders = $this->em->getRepository(Sender::class)->findBy(["manager"=>$user, "status"=>$this->status]);

		foreach ($user->getSenders() as $key => $sender) {
			if($sender->getStatus() == $this->src->status(3)){
				$data["results"][] = [
					"id"=>$sender->getName(),
					"text"=>$sender->getName(),
				];
			}
		}

		return new JsonResponse($data);
	}

	#[Route('/template/get', name: 'filter_template', methods: ['POST'])]
	public function filter_template(Request $request)
	{
		$data = [
			"results"=>[
				["id"=>"", "text"=>""],
			]
		];
		if (!$this->isCsrfTokenValid('', $request->request->get('token'))){
			$this->src->addLog($this->intl->trans("Echec de la récupération des senders. Clé CSRF invalide."));
			return new JsonResponse($data);
		}

		$user = $this->em->getRepository(User::class)->findOneByUid($request->request->get("user"));

		if(!$user) return new JsonResponse($data);

		$campaigns = $this->em->getRepository(SMSCampaign::class)->findByManager($user);

		foreach ($campaigns as $key => $campaign) {
			$data["results"][] = [
				"id"=>$campaign->getMessage(),
				"text"=>$campaign->getName(),
			];
		}

		return new JsonResponse($data);
	}

	#[Route('/groups/contacts', name: 'filter_groups', methods: ['POST'])]
	public function filter_groups(Request $request)
	{
		$data = [
			"results"=>[
				//["id"=>"", "text"=>""],
			]
		];
		if (!$this->isCsrfTokenValid('', $request->request->get('token'))){
			$this->src->addLog($this->intl->trans("Echec de la récupération des senders. Clé CSRF invalide."));
			return new JsonResponse($data);
		}

		$user = $this->em->getRepository(User::class)->findOneByUid($request->request->get("user"));

		if(!$user) return new JsonResponse($data);

		$groups = $user->getContactGroups();

		foreach ($groups as $key => $group) {
			$data["results"][] = [
				"id"=>$group->getUid(),
				"text"=>$group->getName(),
			];
		}

		return new JsonResponse($data);
	}
}
