<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
class RechargeController extends AbstractController
{
    public function __construct(){
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->baseUrl         = $baseUrl;
        $this->em	           = $entityManager;
        $this->user            = $this->getUser();
        $this->tRepository     = $transactionRepository;

        $this->permission      = ["REC0", "REC1", "REC2", "REC3", "REC4"];
        $this->pAccess         = $this->services->checkPermission($this->permission[0]);
        $this->pAllAccess      = $this->services->checkPermission($this->permission[0]);
        $this->pCreate         = $this->services->checkPermission($this->permission[1]);
        $this->pView           = $this->services->checkPermission($this->permission[2]);
        $this->pUpdate         = $this->services->checkPermission($this->permission[3]);
        $this->pDelete         = $this->services->checkPermission($this->permission[4]);
        $this->pManager        = $this->services->checkPermission($this->permission[4]);

    }
    //This function show index page for recharge
    #[Route('{_locale}/home/recharge', name: 'app_recharge')]
    public function index(): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisé pour accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }
        return $this->render('recharge/index.html.twig', [
            'controller_name' => 'RechargeController',
        ]);
    }
    //We use this function to create a new recharge for user
    #[Route('/createrecharge', name: 'create_recharge')]
    public function createRecharge(){
        if (!$this->isCsrfTokenValid($user->getUid(), $request->request->get('_token'))) return $this->services->no_access($this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->user->getEmail());
        $isSelect       = $request->request->get('uSelect');
        if($isSelect){
            $user= $this->em->getRepositoty(User::class)->findOneBy(['uid'=> $isSelect, 'status'=> $this->services->status(3)]);
            if($this->pAllAccess){

            }else{
                if($this->pManager){
                    if($user->getId() != $this->user->getId()){

                    }else{

                    }
                    // return $this->services->msg_error($this->intl->trans("Echec de rechargement utilisateur par.").': '.$this->user->getEmail(), $this->intl->trans("Vous ne pouvez par recharger cet utilisateur car vous n'êtes pas son administrateur."));
                }else{
                    if($user->getBrand()->getId() != $this->user->getId()){
                        return $this->services->msg_error($this->intl->trans("Tentative de rechargement par.").': '.$this->user->getEmail(), $this->intl->trans("Vous ne pouvez par recharger cet utilisateur car vous n'êtes pas son administrateur."));
                    }
                }
                $user= $this->em->getRepositoty(User::class)->findOneBy(['uid'=> $isSelect, 'status'=> $this->services->status(3)]);
            }

            if(!$user) return $this->services->msg_error($this->intl->trans("Utilisateur incorrect.").': '.$this->user->getEmail(), $this->intl->trans("L'utilisateu"), $user->getProfilePhoto());
            $rechargeBy = $user->getAdmin();
        }else{
            $user       = $this->user;
            $rechargeBy = $user;
        }
        if(!is_numeric($request->request->get('amount'))){
            return $this->services->msg_error($this->intl->trans("Recharge montant incorrect.").': '.$this->user->getEmail(), $this->intl->trans("Vous serez redirigé pour payer"), $user->getProfilePhoto());
        }

        if($this->pRechargeUser){
            if(in_array($methodPay, [1, 2])){
                switch($methodPay){
                    case 2:
                        if($balanceAdmin < $request->request->get('amount')) return $this->services->msg_error($this->intl->trans("Balance admin insuffisant .").': '.$this->user->getEmail(), $this->intl->trans("Votre balance est insuffisante."));
                        $balanceAdmin = $rechargeBy->getBalance();

                        break;
                    default:
                        break;
                }
            }
        }




        $newTransaction =  new Transaction;
        $newTransaction -> setUser()
                        -> setStatus()
                        -> setTransaction()
                        -> setReference()
                        -> setAmount()
                        -> setCreatedAt(new \DatetimeImmutable())
                        -> setUpdateAt()
                        -> setBeforeBalance()
                        -> setAfterBalance();
        $this->tRepository->add($newTransaction);

        $newRecharge    =  new Recharge;
        $newRecharge    -> setTransaction($newTransaction)
                        -> setUser()
                        -> SetRechargeBy()
                        -> setStatus()
                        -> setUid()
                        -> setCreateAt(new \DatetimeImmutable())
                        -> setUpdateAt()
                        -> setBeforeCommission()
                        -> setCommission()
                        -> setAfterCommission();
        $this->rRepository->add($newRecharge);

        return $this->services->msg_success(
            $this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->user->getEmail(),
            $this->intl->trans("Vous serez redirigé pour payer"),
            $user->getProfilePhoto(),
        );
    }
    //This function is used re
}
