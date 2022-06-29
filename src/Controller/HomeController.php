<?php

namespace App\Controller;

use App\Service\Brand;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;;
use App\Repository\StatusRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/{_locale}/home')]
#[IsGranted("ROLE_USER")]

class HomeController extends AbstractController
{
    public function __construct(TranslatorInterface $intl, uBrand $brand, BaseUrl $baseUrl, Services $services,
    StatusRepository $statusRepository, UrlGeneratorInterface $urlGenerator)
	{
       $this->intl    = $intl;
       $this->brand   = $brand;
       $this->baseUrl = $baseUrl->init();
       $this->services = $services;
       $this->statusRepository  = $statusRepository;
       $this->urlGenerator  = $urlGenerator;
    }

    #[Route('/dashboard', name: 'app_home')]
    public function index(): Response
    {
        $this->services->addLog($this->intl->trans('Accès au tableau de bord'));
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'title'           => $this->intl->trans('Tableau de bord') .' - '. $this->brand->get()['name'],
            'pageTitle'     => [ ],
            'menu_text'       => $this->intl->trans('Tableau de bord') .' - '. $this->brand->get()['name'],
            'brand'           => $this->brand->get(),
        ]);
    }
}
