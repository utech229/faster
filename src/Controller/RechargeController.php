<?php

namespace App\Controller;

use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Entity\User;
use App\Entity\Transaction;
use App\Entity\Recharge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\TransactionRepository;
use App\Repository\BrandRepository;
use App\Repository\RechargeRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
class RechargeController extends AbstractController
{

    public function __construct(BaseUrl $baseUrl, Services $services, uBrand $brand, TranslatorInterface $translator, EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator, UserRepository $userRepository, RechargeRepository $rechargeRepository, BrandRepository $brandRepository,
        TransactionRepository $transactionRepository){
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->uRepository     = $userRepository;
        $this->tRepository     = $transactionRepository;
        $this->rRepository     = $rechargeRepository;
        $this->bRepository     = $brandRepository;
        $this->permission      = ["REC0", "REC1", "REC2", "REC3", "REC4", "REC5"];
        $this->pAccess         = $this->services->checkPermission($this->permission[0]);
        $this->pRecharge       = $this->services->checkPermission($this->permission[1]);
        $this->pAllAccess      = $this->services->checkPermission($this->permission[5]);
        $this->pRechargeUser   = $this->services->checkPermission($this->permission[2]);
        $this->pManager        = $this->services->checkPermission($this->permission[4]);
        //0--> Vérifier le statut; 1--> Annuler une recharge;
        $updateRecharge        = ['DPF3qslEgI46', 'cglN0BPfxX33'];

        $this->typeRecharge    = ['jbIEz1651764268', 'jbIEz1651764268_', 'cGkJD1651766620'];
        $this->minimumRecharge = 500;
    }
    //This function show index page for recharge
    #[Route('{_locale}/recharge', name: 'app_recharge')]
    public function index(): Response
    {
        // dd($this->pRechargeUser);
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisé pour accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

        $actions =[$this->pRechargeUser, $this->pManager, ];
        //$user    = $this->services->getUserByPermission();

        return $this->render('recharge/index.html.twig', [
            'controller_name'    => 'RechargeController',
            'brand'              => $this->brand->get(),
            'baseUrl'            => $this->baseUrl->init(),
            'title'              => $this->intl->trans('Rechargement'),
            'pageTitle'          => [
                                        [$this->intl->trans('Gestion des recharges')],
                                        [$this->intl->trans('Recharger compte')],
            ],
            'canRechargeUser'        => $this->pRechargeUser,
            'pAllAccess'             => $this->pAllAccess
        ]);
    }
    //We use this function to create a new recharge for user
    #[Route('{_locale}/createrecharge', name: 'create_recharge')]
    public function createRecharge(Request $request){
        // dd($this->getUser());
        if(!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) return $this->services->no_access($this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->getUser()->getEmail());
        if(!$this->pRecharge){
            return $this->services->no_access($this->intl->trans("Utilisateur non autorisé pour effectuer un rechargement.").': '.$this->getUser()->getEmail(), $this->intl->trans("Ooops... Vous n'êtes pas autorisé(e) à effectuer cette action."));
        }
        $typeRecharge   = (!$request->request->get('typeProcess')) ? $this->typeRecharge[0] : $request->request->get('typeProcess');
        if(!in_array($typeRecharge, $this->typeRecharge)) return $this->services->msg_error($this->intl->trans("Tentative de modification des méthodes de paiement.").': '.$this->getUser()->getEmail(), $this->intl->trans("Tentative de modification de méthode de paiement échouée."));
        $isSelect       = $request->request->get('uSelect');
        if(!$isSelect){
            $userTransaction = $user = $rechargeBy = $this->getUser();
        }else{
            if(!$this->pRechargeUser) return $this->services->no_access($this->intl->trans("Utilisateur non autorisé pour recharger un utilisateur.").': '.$this->getUser()->getEmail(), $this->intl->trans("Ooops... Vous n'êtes pas autorisé(e) à effectuer cette action."));

            $user= $this->uRepository->findOneBy(['uid'=> $isSelect, 'status'=> $this->services->status(3)]);
            //$extractBalance = false;
            if(!$user) return $this->services->msg_error($this->intl->trans("Utilisateur incorrect.").': '.$this->getUser()->getEmail(), $this->intl->trans("L'utilisateur sélectionné n'existe pas ou n'est pas actif."));
            if($this->pAllAccess){
                $canContinious  = true;
            }else{
                if($this->pManager){
                    if($this->getUser()->getId() != $user->getId()){
                        return $this->services->msg_error($this->intl->trans("Tentative de rechargement par.").': '.$this->getUser()->getEmail(), $this->intl->trans("Vous ne pouvez par recharger cet utilisateur car vous n'êtes pas son gestionnaire de compte."));
                    }
                }else{
                    if($user->getBrand()->getManager()->getUid() != $this->getUser()->getUid()){
                        return $this->services->msg_error($this->intl->trans("Tentative de rechargement échouée par.").': '.$this->getUser()->getEmail(), $this->intl->trans("Vous ne pouvez par recharger cet utilisateur. Vous n'êtes pas son administrateur."));
                    }
                }
                $canContinious = true;
            }
        }
        if(!$canContinious) return $this->services->msg_error($this->intl->trans("Tentative de rechargement échouée par.").': '.$this->getUser()->getEmail(), $this->intl->trans("Une erreur a été identifié. Si vous n'êtes pas l'administrateur, veuillez contacter +22952735555"));


        $amount         = $request->request->get('amount');
        if(!is_numeric($amount) || $amount < $this->minimumRecharge){
            return $this->services->msg_error($this->intl->trans("Recharge montant incorrect.").': '.$this->getUser()->getEmail(), $this->intl->trans("Le montant que vous avez renseigné est incorrect. Corrigez pour continuer."));
        }
        //Mode paiment:::: [0] => paiement mobile;  [1] => rechargement par balance;
        $linkRedirect   = $updateDate = '';
        $info           = $this->intl->trans('Rechargement effectué avec succès.');
        $idTransaction  = $reference = '';
        $creatDate      = new \DatetimeImmutable();
        $priceManager = $afterCommission = $beforeCommission = $commission = 0;
        // En attente de la bonne récupération $priceUser       = $user->getPrice()[0] ;
        $priceUser       = 15 ;
        if($typeRecharge == $this->typeRecharge[0]){
            if($this->pAllAccess || $this->pManager ){
                $canContinious=true;
            }else{
                if($user->getBrand()->getManager()->getId() == $this->getUser()->getId()){
                    $priceManager    = 10 ;
                    $commission      = $priceUser - $priceManager;
                    $afterCommission = 5000 ;
                    $beforeCommission= $user->getBrand()->getCommission();
                    $canContinious   = true;
                }else{
                    return $this->services->msg_error($this->intl->trans("Tentative de rechargement échoué.").': '.$this->getUser()->getEmail(), $this->intl->trans("Vous n'êtes pas autorisé(e) à effectuer cette action. S'il s'agit d'une erreur, veuillez contacter votre gestionnaire de compte."));
                }
            }
            if($canContinious){
                if ($this->getUser()->getBalance() < $amount) return $this->services->msg_error($this->intl->trans("Balance du revendeur incorrect.").': '.$this->getUser()->getEmail(), $this->intl->trans("Le montant à recharger est supérieur à votre balance. Veuillez approvisionner votre compte pour continuer."));
                $beforeBalance   = $this->getUser()->getBalance();
                $afterBalance    = $this->getUser()->getBalance() - $amount;
                $this->getUser()->setBalance($afterBalance);
                $this->uRepository->add($this->getUser());
                $user->setBalance(($user->getBalance()+$amount));
                $this->uRepository->add($user);
                $this->getUser()->getBrand()->setCommission(($this->getUser()->getBrand()->getCommission() + $commission));
                $this->bRepository->add($this->getUser()->getBrand());
                $status          = 'approved';
                $userTransaction = $rechargeBy = $this->getUser();
                $updateDate      = $creatDate;
            }
        }else{
            //Récupération API
            $beforeBalance   = $this->getUser()->getBalance();
            $afterBalance    = $this->getUser()->getBalance() - $amount;
            $status          = 'pending';
            $userTransaction = $rechargeBy = $this->getUser();
            $idTransaction   = '20123456';
            $reference       = '20108902';
            $linkRedirect    = 'recharge.test';
        }
        switch($status){
            //à completer si d'autres statut
            case 'pending':
                $status = $this->services->status(1);
                break;
            case 'approved':
                $status = $this->services->status(6);
                break;
            case 'canceled':
                $status = $this->services->status(7);
                break;
            default:
                $status = $this->services->status(1);
                break;
        }

        if(!$idTransaction){ $idTransaction = 'fas_id'.$this->services->getUniqid();};
        if(!$reference){ $reference = 'fas_ref'.$this->services->getUniqid();};
        $newTransaction =  new Transaction;
        $newTransaction -> setUser($userTransaction)
                        -> setStatus($status)
                        -> setTransactionId($idTransaction)
                        -> setReference($reference)
                        -> setAmount($amount)
                        -> setCreatedAt($creatDate)
                        -> setUpdatedAt($updateDate)
                        -> setBeforeBalance($beforeBalance)
                        -> setAfterBalance($afterBalance);
        $this->tRepository->add($newTransaction);

        $newRecharge    =  new Recharge;
        $newRecharge    -> setTransaction($newTransaction)
                        -> setUser($user)
                        -> SetRechargeBy($rechargeBy)
                        -> setStatus($status)
                        -> setUid('REC'.$this->services->getUniqid())
                        -> setCreatedAt($creatDate)
                        -> setUpdatedAt($updateDate)
                        -> setBeforeCommission($beforeCommission)
                        -> setCommission($commission)
                        -> setAfterCommission($afterCommission);
        $this->rRepository->add($newRecharge);

        return $this->services->msg_success(
            $this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->getUser()->getEmail(),
            $info,
            $linkRedirect,
        );
    }
    #[Route('{_locale}/setrecharge', name: 'set_recharge')]
    public function setRecharge(Request $request){
        if(!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) return $this->services->no_access($this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->getUser()->getEmail());
        if(!$this->pRecharge){
            return $this->services->no_access($this->intl->trans("Utilisateur non autorisé pour effectuer une actualisation rechargement.").': '.$this->getUser()->getEmail(), $this->intl->trans("Ooops... Vous n'êtes pas autorisé(e) à effectuer cette action."));
        }
        if(!$isSelect){
            $userTransaction = $user = $rechargeBy = $this->getUser();
        }else{
            if(!$this->pRechargeUser) return $this->services->no_access($this->intl->trans("Utilisateur non autorisé pour actualiser le rechargement d'un utilisateur.").': '.$this->getUser()->getEmail(), $this->intl->trans("Ooops... Vous n'êtes pas autorisé(e) à effectuer cette action."));
            $user= $this->uRepository->findOneBy(['uid'=> $isSelect, 'status'=> $this->services->status(3)]);
            //$extractBalance = false;
            if(!$user) return $this->services->msg_error($this->intl->trans("Utilisateur incorrect.").': '.$this->getUser()->getEmail(), $this->intl->trans("L'utilisateur sélectionné n'existe pas ou n'est pas actif."));
            if($this->pAllAccess){
                $canContinious  = true;
            }else{
                if($this->pManager){
                    if($this->getUser()->getId() != $user->getId()){
                        return $this->services->msg_error($this->intl->trans("Tentative d'actualisation de rechargement échouée par.").': '.$this->getUser()->getEmail(), $this->intl->trans("Vous ne pouvez par recharger cet utilisateur car vous n'êtes pas son gestionnaire de compte."));
                    }
                }else{
                    if($user->getBrand()->getManager()->getUid() != $this->getUser()->getUid()){
                        return $this->services->msg_error($this->intl->trans("Tentative d'actualisation de rechargement échouée par.").': '.$this->getUser()->getEmail(), $this->intl->trans("Vous ne pouvez par recharger cet utilisateur. Vous n'êtes pas son administrateur."));
                    }
                }
                $canContinious = true;
            }
        }
        if($canContinious){
            //Fonction pour vérifier l'état d'une transaction chez l'agrégateur
            switch($action){
                case $updateRecharge[0]:
                break;
                case $updateRecharge[1]:
                break;
                case $updateRecharge[2]:
                break;
                default:
                break;

            }
        }
    }

    #[Route('{_locale}/getrecharge', name: 'get_recharge')]
    public function getRecharge($allAcess = NULL, $isSelect = NULL, $user = NULL){
        if($allAcess){
            $allRecharges = ($isSelect) ? $this->rRepository->findBy(['user'=> $isSelect]) : $this->rRepository->findAll();
        }else{
            $allRecharges = ($isSelect) ? $this->rRepository->findBy(['user'=> $isSelect]) : $this->rRepository->findBy(['user'=> $user]);
        }
        return $allRecharges;
    }
    #[Route('{_locale}/loadrecharge', name: 'load_recharge')]
    public function loadRecharge(Request $request){
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
                    $allRecharges = $this->getRecharge($this->pAllAccess, $userSelect, $this->getUser());
                }else{
                    if(!$this->pAffiliate){
                        $allRecharges = $this->getRecharge($this->pAllAccess, $userSelect, $this->getUser()->getAffiliateManager());
                    }else{
                        // $allRecharges = ($userSelect->getId() == $this->getUser()->getAdmin()) ? $allRecharges = $this->getRecharge($this->pAllAccess, $userSelect, $this->getUser()) : [];
                        if($userSelect->getId() == $this->getUser()->getAdmin()){

                        }else{
                            $allRecharges = [];
                        }
                    }
                }
            }else{
                $this->getUser()->getBrand()->getUid();
                $inBrand      = $this->bRepository->findOneBy(['uid'=> $this->getUser()->getBrand()->getUid()]);
                if($inBrand){
                    // if()
                }
                $allRecharges = ($this->getUser()->getId() == $userSelect->getId()) ? $this->getRecharge($this->pAllAccess, $userSelect, $this->getUser()) : [];
            }
        }else{
            $allRecharges = $this->getRecharge($this->pAllAccess, $userSelect, $this->getUser());
        }
        // dd($this->pAllAccess, $userSelect, $this->getUser());
        $data = [];
        if($allRecharges){
            foreach($allRecharges as $getRecharge){
                $row                 = array();
                $row[]               = null;
                $row[]               = $getRecharge->getUid();
                $row[]               = $getRecharge->getTransaction()->getAmount();
                $row[]               = $getRecharge->getUser()->getEmail();
                $row[]               = $getRecharge->getRechargeBy()->getEmail();
                $row[]               = ($this->intl->trans($getRecharge->getStatus()->getName()));
                $row[]               = $getRecharge->getCreatedAt()->format("d-m-Y H:i");
                $row[]               = ($getRecharge->getStatus()->getName()== 'En attente' && $this->pAllAccess) ? '<a data-u="'.$getRecharge->getUid().'" href="#"><i class="fa fa-delete">Actualisé</i></a>' : 'download';
                $data[]              = $row;
            }
        }
        $output = array("data" => $data);
        return new JsonResponse($output);

    }
    //This function is used re
}
