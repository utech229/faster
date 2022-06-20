<?php

namespace App\Controller;

use App\Form\UserType;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\BrickPhone;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
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
#[Route('/{_locale}/developer')]
class DeveloperController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, Services $services, EntityManagerInterface $entityManager, 
    TranslatorInterface $translator,UserRepository $userRepository, StatusRepository $statusRepository,
    AuthorizationRepository $authorizationRepository, uBrand $brand, ValidatorInterface $validator, ){
		$this->baseUrl         = $baseUrl;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->userRepository   = $userRepository;
        $this->statusRepository = $statusRepository;
        $this->validator       = $validator;

        $this->permission = [
            "PFIL0", "PFIL1",  "PFIL2", "PFIL3", "PFIL4"
        ];
		$this->pAccess  =	$this->services->checkPermission($this->permission[0]);
		$this->pCreate  =	$this->services->checkPermission($this->permission[1]);
		$this->pList    =	$this->services->checkPermission($this->permission[2]);
		$this->pEdit    =	$this->services->checkPermission($this->permission[3]);
		$this->pDelete  =	$this->services->checkPermission($this->permission[4]);
    }

    #[Route('', name: 'app_developer')]
    public function index(): Response
    {
        return $this->render('developer/index.html.twig', [
            'controller_name' => 'DeveloperController',
        ]);
    }

    #[Route('/apikey', name: 'app_user_apikey')]
    public function apikey(): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

        $this->services->addLog($this->intl->trans('Accès au menu clé api'));
        return $this->render('developer/apikey.html.twig', [
            'controller_name' => 'DeveloperController',
            'title'           => $this->intl->trans('Clé api').' - '. $this->brand->get()['name'],
            'pageTitle'          => [ [$this->intl->trans('Développeur & Api')], [$this->intl->trans('A')] ],
            'brand'           => $this->brand->get(),
            'baseUrl'         => $this->baseUrl->init(),
            'pCreateUser'     => $this->pCreate,
            'pEditUser'       => $this->pEdit,
            'pDeleteUser'     => $this->pDelete,
        ]);
    }

    #[Route('/apikey_regenerate', name: 'app_user_regenerate_apikey')]
    public function apikey_regenerate(Request $request): Response
    {
        $a    = null; 
        $user = $this->getUser();
        if ($request->request->count() > 0) 
        {
            if ((!$this->isCsrfTokenValid($user->getUid(), $request->request->get('_token'))) or ($user->getRole()->getCode() == 'AFF')) 
                return $this->services->no_access($this->intl->trans("Régénération de la clé api"));

            $newapikey = $this->services->idgenerate(30);           
            $user->setApiKey($newapikey);
            $user->setUpdatedAt(new \DatetimeImmutable());
            $this->userRepository->add($user);
            $affiliates = $this->userRepository->findBy(['admin' => $user, 'isAffiliate' => true]);
            foreach ($affiliates as $affiliate) {
                $affiliate->setApiKey($newapikey);
                $affiliate->setUpdatedAt(new \DatetimeImmutable());
                $this->userRepository->add($affiliate);
            }
            return $this->services->msg_success(
                $this->intl->trans("Régénération de la clé api"),
                $this->intl->trans("Clé api regénéré avec succès!"),
                $newapikey
            );
        }
        return $this->services->failedcrud(
            $this->intl->trans("Régénération de la clé api"),
        );
    }
}
