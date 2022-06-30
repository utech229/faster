<?php

namespace App\Controller;

use App\Service\Brand;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;;
use App\Entity\SMSCampaign;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("ROLE_USER")]
#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[Route('/{_locale}/home')]
class HomeController extends AbstractController
{
    public function __construct(TranslatorInterface $intl, uBrand $brand, BaseUrl $baseUrl, Services $services,
    StatusRepository $statusRepository, UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager, sMailer $sMailer)
	{
       $this->intl    = $intl;
       $this->brand   = $brand;
       $this->baseUrl = $baseUrl->init();
       $this->services = $services;
       $this->em = $entityManager;
       $this->statusRepository  = $statusRepository;
       $this->urlGenerator  = $urlGenerator;
       $this->sMailer      = $sMailer;
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
            'data'            => $this->statistics()
        ]);
    }

    public function statistics(){
        $user = $this->getUser();
        switch ($user->getRole()->getLevel()) {
            case [0, 2]:
                return [
                "e" => true
                ];
                break;
            
            default:
                return [
                    'campaign' => $this->em->getRepository(SMSCampaign::class)->findAll()
                ];
                break;
        }
    }

    
    #[Route('/dashboard/mail', name: 'app_home_mail')]
    public function sendmail():JsonResponse
    {
        $send = $this->sMailer->send();
        dd($send);
        
        return $this->services->msg_success(
            $this->intl->trans("Envoi d'email"),
            $this->intl->trans("Mail envoyé"));
    }
}
