<?php

namespace App\Controller;




use App\Entity\User;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\AddEntity;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InitController extends AbstractController
{

    public function __construct(BaseUrl $baseUrl, Services $services, EntityManagerInterface $entityManager, TranslatorInterface $translator,
    UserRepository $userRepository, UrlGeneratorInterface $urlGenerator, uBrand $brand,  AddEntity $addEntity, StatusRepository $statusRepository){
		$this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->userRepository  = $userRepository;
        $this->statusRepository  = $statusRepository;
        $this->addEntity         = $addEntity;
	}
    #[Route('/')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_login');
    }

    #[Route('/{_locale}')]
    public function indexi(): Response
    {
        return $this->redirectToRoute('app_login');
    }
}
