<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Sender;
use App\Entity\Status;
use App\Entity\Brand;
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
            $this->src->addLog($this->intl->trans("Tentative de récupération des utilisateurs. Token CSRF invalide."));
            return new JsonResponse($data);
        }

        $level = (int)$request->request->get("brand", "1");

        $brand = $this->em->getRepository(Brand::class)->findOneByUid($request->request->get("brand", ""));

        if(!$brand) return new JsonResponse($data);

        $users = $this->em->getRepository(User::class)->getUsersByPermission("", 2, $brand->getManager()->getId(), 1);

        foreach ($users as $key => $user) {
            $data["results"][] = [
				"id"=>$user->getUid(),
				"text"=>$user->getEmail(),
			];
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
            $this->src->addLog($this->intl->trans("Clé CSRF invalide. Rechargez votre page."));
            return new JsonResponse($data);
        }

        $user = $this->em->getRepository(User::class)->findOneByUid($request->request->get("user"));

        if(!$user) return new JsonResponse($data);

        $senders = $this->em->getRepository(Sender::class)->findByManager($user);

        foreach ($senders as $key => $sender) {
            $data["results"][] = [
				"id"=>$sender->getUid(),
				"text"=>$sender->getName(),
			];
        }

        return new JsonResponse($data);
    }
}
