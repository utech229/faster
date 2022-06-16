<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\Status;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('{_locale}/home/commission')]
class CommissionController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, UrlGeneratorInterface $urlGenerator, Services $services,  
    EntityManagerInterface $entityManager, TranslatorInterface $translator, uBrand $brand)
    {
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->baseUrl         = $baseUrl;
        $this->em	           = $entityManager;

        $this->permission      =    ["COMM0", "COMM1", "COMM2", "COMM3", "COMM4"];
        $this->pAccess         =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate         =    $this->services->checkPermission($this->permission[1]);
        $this->pView           =    $this->services->checkPermission($this->permission[2]);
        $this->pUpdate         =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete         =    $this->services->checkPermission($this->permission[4]);

        $this->placeAvatar	   = "public/app/uploads/avatars/"; //profile image file path

    }
    
    #[Route('/', name: 'app_commission_index', methods: ['GET'])]
    public function index(): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

        return $this->render('commission/index.html.twig', [
            'title'           => $this->intl->trans('Commissions').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans('Commissions')] 
            ],
            'brand'       => $this->brand->get(),
            'baseUrl'     => $this->baseUrl->init(),
            'pAccess'     => $this->pAccess,
        ]);
    }

    #[Route('/list', name: 'app_commissions_branch_list', methods: ['POST'])]
    public function getCommissionBranch(Request $request, EntityManagerInterface $manager) : Response
    {
        //Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Récupération de la liste des transactions : token invalide'));

        $data           =   [];
        $tabBrand       =   [];

        if (!$this->pView) {

            $brands   =    $this->em->getRepository(Brand::class)->findByStatus($this->services->status(3));

        }
        else
        {
            $brands   =    $this->em->getRepository(Brand::class)->findByStatus($this->services->status(3));
        }
        
        foreach ($brands as $key => $brand) {

            $tabBrand[$key][0]      =   $brand->getName();
            $tabBrand[$key][1]      =   $brand->getCommission();
            $tabBrand[$key][2][0]   =   $brand->getStatus()->getCode();
            $tabBrand[$key][2][1]   =   $brand->getStatus()->getName();
            $tabBrand[$key][2][2]   =   $brand->getStatus()->getDescription();
            $tabBrand[$key][3]      =   $brand->getCreatedAt()->format("c");
            $tabBrand[$key][4]      =   $brand->getUpdatedAt()?$brand->getUpdatedAt()->format("c"):$this->intl->trans('Pas de modification');

        }
       
        $this->services->addLog($this->intl->trans('Lecture de la liste des transactions'));
        $data = [
                    "data"              =>   $tabBrand,
                ];
        return new JsonResponse($data);
    }

}
