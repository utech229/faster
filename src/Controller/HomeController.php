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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/{_locale}/home')]
#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
class HomeController extends AbstractController
{
    public function __construct(TranslatorInterface $intl, uBrand $brand, BaseUrl $baseUrl, Services $services,
    StatusRepository $statusRepository)
	{
       $this->intl    = $intl;
       $this->brand   = $brand;
       $this->baseUrl = $baseUrl->init();
       $this->services = $services;
       $this->statusRepository  = $statusRepository;
    }

    #[Route('/dashboard', name: 'app_home')]
    public function index(): Response
    {
        $this->services->addLog($this->intl->trans('Accès au tableau de bord'));
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'title'           => $this->intl->trans('Tableau de bord') .' - '. $this->brand->index()['name'],
            'pageTitle'       => [
                'one'   => $this->brand->index()['name'],
                'two'   => $this->brand->index()['name'],
                'none'  => $this->intl->trans('Analyse'),
            ],
            'menu_text'       => $this->intl->trans('Tableau de bord') .' - '. $this->brand->index()['name'],
            'brand'           => $this->brand->index(),
            'baseUrl'         => $this->baseUrl,
        ]);
    }
}
