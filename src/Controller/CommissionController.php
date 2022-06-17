<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\Status;
use App\Entity\User;
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

        $this->permission      =    ["COMM0", "COMM1", "COMM2","BRND1"];
        $this->pAccess         =    $this->services->checkPermission($this->permission[0]); //Accéder au menu commission par branche
        $this->pView           =    $this->services->checkPermission($this->permission[1]); //Voir sa commission par branche
        $this->pAllView        =    $this->services->checkPermission($this->permission[2]); //Voir toutes commissions par branche
        $this->pSeller         =    $this->services->checkPermission($this->permission[3]); //Revendeur


        $this->placeAvatar	   =    "public/app/uploads/avatars/"; //profile image file path

    }
    
    #[Route('/', name: 'app_commission_index', methods: ['GET'])]
    public function index(): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

        list($typeUser,$Id) =   $this->services->checkThisUser($this->pAllView);

        $users              =   [];

        if ($this->pView) {
            $users   =   $this->services->getUserByPermission($this->permission[3], null, null, 1);
        }

        return $this->render('commission/index.html.twig', [
            'title'           => $this->intl->trans('Commissions').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans('Commissions')] 
            ],
            'brand'       => $this->brand->get(),
            'users'       => $users,
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

        list($typeUser,$Id) =   $this->services->checkThisUser($this->pAllView);

        switch ($request->request->get('_uid')) {
            case 'all':
                if ($typeUser == 0) {
                    $brands   =    $this->em->getRepository(Brand::class)->findAll();
                }
                else
                {
                    $users   =   $this->services->getUserByPermission($this->pSeller,$typeUser,$Id,1);
                
                    foreach ($users as $key => $user) {
                        foreach ($user->getBrands() as $key => $brand) {

                            $tabBrand[$key][0]      =   $brand->getName();
                            $tabBrand[$key][1]      =   $brand->getCommission();
                            $tabBrand[$key][2][0]   =   $brand->getStatus()->getCode();
                            $tabBrand[$key][2][1]   =   $brand->getStatus()->getName();
                            $tabBrand[$key][2][2]   =   $brand->getStatus()->getDescription();
                            $tabBrand[$key][3]      =   $brand->getCreatedAt()->format("c");
                            $tabBrand[$key][4]      =   $brand->getUpdatedAt()?$brand->getUpdatedAt()->format("c"):$this->intl->trans('Pas de modification');
                
                        }
                    }
                    $data   =[
                                "data"              =>   $tabBrand,
                            ];
                    return new JsonResponse($data);
                }
                break;
            case '':
                    $brands   = [];
                    break;
            
            default:
                    $brands   =    $this->em->getRepository(Brand::class)->findByManager($this->em->getRepository(User::class)->findByUid($request->request->get('_uid')));

                break;
        }

        if (!$this->pView) {
            $brands   =    [];
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
