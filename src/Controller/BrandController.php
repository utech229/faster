<?php

namespace App\Controller;

use App\Service\uBrand;
use App\Entity\Brand;

use App\Service\BaseUrl;
use App\Service\Services;
use App\Entity\User;
use App\Entity\Status;
use App\Service\AddEntity;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\BrandRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
class BrandController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, Services $services, uBrand $brand, TranslatorInterface $translator, EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator, UserRepository $userRepository, BrandRepository $brandRepository, AddEntity $addEntity){
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->addEntity	   = $addEntity;
        $this->uRepository     = $userRepository;
        $this->bRepository     = $brandRepository;
        $this->permission      = ["REC0", "REC1", "REC2", "REC3", "REC4", "REC5"];
        $this->pAccess         = $this->services->checkPermission($this->permission[0]);
        $this->pRecharge       = $this->services->checkPermission($this->permission[1]);
        $this->pAllAccess      = $this->services->checkPermission($this->permission[5]);
        $this->pRechargeUser   = $this->services->checkPermission($this->permission[2]);
        $this->pManager        = $this->services->checkPermission($this->permission[4]);
        //0--> Vérifier le statut; 1--> Annuler une recharge;
        $updateRecharge        = ['DPF3qslEgI46', 'cglN0BPfxX33'];
    }

    #[Route('{_locale}/brand', name: 'brand')]
    public function index(): Response
    {
        return $this->render('brand/index.html.twig', [
            'controller_name' => 'BrandController',
            'brand'              => $this->brand->get(),
            'sStatus'     => $this->em->getRepository(Status::class)->findByCode([6,2,7]),
            'pAccess'     => $this->pAccess,
            'sBrand'      => $this->em->getRepository(Brand::class)->findByStatus($this->services->status(3)),
            'baseUrl'            => $this->baseUrl->init(),
            'title'              => $this->intl->trans('Mes marques'),
            'pageTitle'          => [
                                        [$this->intl->trans('Gestion des marques')],
            ],
        ]);
    }

    #[Route('{_locale}/create_brand', name: 'create_brand')]
    public function createBrand(Request $request): Response
    {
        if(!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) return $this->services->no_access($this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->getUser()->getEmail());
        // if(!$this->pRecharge){
        //     return $this->services->no_access($this->intl->trans("Utilisateur non autorisé pour effectuer un rechargement.").': '.$this->getUser()->getEmail(), $this->intl->trans("Ooops... Vous n'êtes pas autorisé(e) à effectuer cette action."));
        // }
        $userAdd    = $this->getUser();

        $linkLogo   = 'loadPicture';
        $buildImg = $this->addEntity->profilePhotoSetter($request , $userAdd, $name, 'brand');
        // dd($buildImg);
            if (isset($avatarProcess['error']) && $avatarProcess['error'] == true){
            return $this->services->ajax_error_crud($this->intl->trans('Traitement du fichier image de profile'), $avatarProcess['info']);
            }
        $newBrand   = new Brand;
        $newBrand-> setManager($userAdd)
                    -> setStatus($this->services->status(1))
                    -> setUid($this->services->getUniqid())
                    -> setName($request->request->get('_name_brand'))
                    -> setSiteUrl($request->request->get('_url_brand'))
                    -> setLogo($buildImg)
                    -> setFavicon($buildImg)
                    -> setEmail($request->request->get('_mail_support'))
                    -> setNoreplyEmail($request->request->get('_mail_noreply'))
                    -> setIsDefault(1)
                    -> setPhone($request->request->get('_phone_support'))
                    -> setCreatedAt(new \DatetimeImmutable())
                    -> setCommission(0);
        $this->bRepository->add($newBrand);

        return $this->services->msg_success(
            $this->intl->trans("Création de marque ").': '.$this->getUser()->getEmail(),
            $this->intl->trans("Votre dossier de création de marque a été soumis avec succès.")
        );

    }


    public function getBrand($allAcess = NULL, $isSelect = NULL, $user = NULL){
        if($allAcess){
            $allBrand = ($isSelect) ? $this->bRepository->findBy(['manager'=> $isSelect]) : $this->bRepository->findAll();
        }else{
            $allBrand = ($isSelect) ? $this->bRepository->findBy(['manager'=> $isSelect]) : $this->bRepository->findBy(['user'=> $user]);
        }
        return $allBrand;
    }
    #[Route('{_locale}/loadbrand', name: 'load_brand')]
    public function loadbrand(Request $request){
        // if(!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) return $this->services->no_access($this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->getUser()->getEmail());
        $isSelect       = $request->get('uSelect');
        $userSelect     = '';
        if($isSelect){
            $userSelect = $this->uRepository->findOneBy(['uid'=> $isSelect]);
            if(!$userSelect) return $this->services->msg_error($this->intl->trans("Utilisateur inexistant.").': '.$this->getUser()->getEmail(), $this->intl->trans("L'utilisateur n'existe pas."));
        }


        if(!$this->pAllAccess){
            if($this->pManager){
                if($userSelect->getBrand()->getManager()->getUid() == $this->getUser()->getUid()){
                    $allBrand = $this->getBrand($this->pAllAccess, $userSelect, $this->getUser());
                }else{
                    if(!$this->pAffiliate){
                        $allBrand = $this->getBrand($this->pAllAccess, $userSelect, $this->getUser()->getAffiliateManager());
                    }else{
                        // $allBrand = ($userSelect->getId() == $this->getUser()->getAdmin()) ? $allBrand = $this->getBrand($this->pAllAccess, $userSelect, $this->getUser()) : [];
                        if($userSelect->getId() == $this->getUser()->getAdmin()){

                        }else{
                            $allBrand = [];
                        }
                    }
                }
            }else{
                $this->getUser()->getBrand()->getUid();
                $inBrand      = $this->bRepository->findOneBy(['uid'=> $this->getUser()->getBrand()->getUid()]);
                if($inBrand){
                    // if()
                }
                $allBrand = ($this->getUser()->getId() == $userSelect->getId()) ? $this->getBrand($this->pAllAccess, $userSelect, $this->getUser()) : [];
            }
        }else{
            $allBrand = $this->getBrand($this->pAllAccess, $userSelect, $this->getUser());
        }
        // dd($this->pAllAccess, $userSelect, $this->getUser());
        $data = [];
        if($allBrand){
            foreach($allBrand as $getBrand){
                $row                 = array();
                $row[]               = null;
                $row[]               = $getBrand->getName();
                $row[]               = $getBrand->getManager()->getEmail();
                $row[]               = $getBrand->getSiteUrl();
                $row[]               = $getBrand->getEmail();
                $row[]               = ($this->intl->trans($getBrand->getStatus()->getName()));
                $row[]               = $getBrand->getCreatedAt()->format("d-m-Y H:i");
                $row[]               = ($getBrand->getStatus()->getName()== 'En attente' && $this->pAllAccess) ? '<a data-u="'.$getBrand->getUid().'" href="#"><i class="fa fa-delete">Actualisé</i></a>' : 'null';
                $data[]              = $row;
            }
        }
        $output = array("data" => $data);
        return new JsonResponse($output);

    }
}
