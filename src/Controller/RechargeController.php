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
        $this->permission      = ["REC0", "REC1", "REC2", "REC3"];
        $this->pAccess         = $this->services->checkPermission($this->permission[0]);
        $this->pRecharge       = $this->services->checkPermission($this->permission[1]);
        $this->pRechargeUser   = $this->services->checkPermission($this->permission[2]);
        $this->pAllAccess      = $this->services->checkPermission($this->permission[3]);

        //0--> Vérifier le statut; 1--> Annuler une recharge;
        $updateRecharge        = ['DPF3qslEgI46', 'cglN0BPfxX33'];
        $this->typeRecharge    = ['jbIEz1651764268', 'jbIEz1651764268_', 'cGkJD1651766620'];
        $this->minimumRecharge = 500;
    }
    //This function show index page for recharge
    #[Route('{_locale}/recharge', name: 'app_recharge')]
    public function index(): Response
    {
        if(!$this->pAccess){
            $this->addFlash('error', $this->intl->trans("Tentative d'accès à la page marque échouée.")); return $this->redirectToRoute("app_home");
        }

        $checkEtat = $this->services->checkThisUser($this->pAllAccess);
        $users = [];
        switch($checkEtat[0]){
            case 0: $users = $this->uRepository->findBy(['status'=> $this->services->status(3)]); break;
            case 1: $users = $this->services->getUserByPermission('MANGR'); break;
            case 2: $users = $this->services->getUserByPermission('BRND1'); break;
            case 3: $users = $this->services->getUserByPermission('BRND1'); break;
            default: break;
        }
        return $this->render('recharge/index.html.twig', [
            'controller_name'    => 'RechargeController',
            'brand'              => $this->brand->get(),
            'baseUrl'            => $this->baseUrl->init(),
            'title'              => $this->intl->trans('Rechargement'),
            'pageTitle'          => [
                                        [$this->intl->trans('Gestion des recharges')],
                                        [$this->intl->trans('Recharger compte')],
            ],
            'canRechargeUser'    => $this->pRechargeUser,
            'pAllAccess'         => $this->pAllAccess,
            'users'              => $users
        ]);
    }
    //API function
    public function callApi($info){
        $url = "http://pay.zekin.app/api/v1/transactions/create";
        $headers = [
                    "Accept: application/json", "Authorization: Bearer l0899bzWX40trcpqxwC545", "Content-Type: application/json",
                    "Environment: prod"
        ];
        // dd($info);
        $data = [
            "description"   => $info['description'], "amount"          => $info['amount'], "firstname"    => $info['firstname'], "lastname" => $info['lastname'],
            "email"         => $info['email'], "phone_number"  => $info['phone_number'], "internal_ref"  => $info['internal_ref'],
            "process"       => $info['process'],"expired"      => $info['expired']
        ];
        $url = $url."?".http_build_query($data); $curl = curl_init(); curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url); curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_HTTP_CODE); curl_close($curl); $dataDecode = json_decode($resp);
        $return = [
                    'link'          => $dataDecode->response->api->url, 'status'        => $dataDecode->status,
                    'id_transaction'=> $dataDecode->id_transaction, 'external_ref'      => $dataDecode->external_ref
        ];
        // dd($return);
        return $return;
    }
    //We use this function to create a new recharge for user
    #[Route('{_locale}/createrecharge', name: 'create_recharge')]
    public function createRecharge(Request $request){

        if(!$this->pRecharge){
            return $this->services->no_access($this->intl->trans("Utilisateur non autorisé pour effectuer un rechargement.").': '.$this->getUser()->getEmail(), $this->intl->trans("Ooops... Vous n'êtes pas autorisé(e) à effectuer cette action."));
        }
        if(!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) return $this->services->no_access($this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->getUser()->getEmail());
        $typeRecharge   = (!$request->request->get('typeProcess')) ? $this->typeRecharge[0] : $request->request->get('typeProcess');
        if(!in_array($typeRecharge, $this->typeRecharge)) return $this->services->msg_error($this->intl->trans("Tentative de modification des méthodes de paiement."), $this->intl->trans("Tentative de modification de méthode de paiement échouée."));
        $isSelect       = $request->request->get('uSelect');
        if(!$isSelect){$userTransaction = $user = $rechargeBy = $this->getUser();
        }else{
            $user = $this->uRepository->findOneBy(['uid' => $isSelect, 'status'=> $this->services->status(3)]);
            if(!$user){ return $this->services->msg_error($this->intl->trans("Utilisateur non trouvé."), $this->intl->trans("L'utilisateur sélectionné est introuvable."));}
        }
        $amount         = $request->request->get('amount');
        if(!is_numeric($amount) || $amount < $this->minimumRecharge){
            return $this->services->msg_error($this->intl->trans("Recharge montant incorrect.").': '.$this->getUser()->getEmail(), $this->intl->trans("Le montant que vous avez renseigné est incorrect. Corrigez pour continuer."));
        }
        //Mode paiment:::: [0] => paiement mobile;  [1] => rechargement par balance;
        $creatDate      = new \DatetimeImmutable();
        $idTransaction = $reference = NULL;
        $beforeCommission= $afterCommission = $commission = 0;
        $checkEtat = $this->services->checkThisUser($this->pAllAccess, $user);

        // $data = [
        //     "description"   => "Rechargement de compte", "amount"=> 100, "firstname"    => "Support", "lastname" => 'fastermessage',
        //     "email"         => "support@fastermessage.com", "phone_number"  => '+22952734444', "internal_ref"  => 'ji89oiji31',
        //     "process"       => "MOBILE","expired"      => date("Y-m-d H:i:s")
        // ];

        // $initPay = $this->callApi($data);
        // return $this->services->msg_success(
        //     $this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->getUser()->getEmail(),
        //     $initPay,
        //     $initPay['link'],
        // );
        //Vérifier si montant supérieur
        if(in_array($checkEtat[0], [0, 1, 2, 3])){
            if($typeRecharge == $this->typeRecharge[0]){
                if($amount > $this->getUser()->getBalance()){return $this->services->msg_error($this->intl->trans("Balance du revendeur incorrect.").': '.$this->getUser()->getEmail(), $this->intl->trans("Le montant à recharger est supérieur à votre balance. Veuillez approvisionner votre compte pour continuer."));}

                $userTransaction     = $this->getUser();
                $statusEntity        = $this->checkStatus("approved");
                $updateDate          = new \DatetimeImmutable();
                $rechargeBy          = $this->getUser();
                $beforeBalance       = $this->getUser()->getBalance();
                $afterBalance        = $nBalance = $beforeBalance - $amount;

                // dd($user->getPrice()['BJ']['price'], $user->getBrand()->getManager()->getPrice()['BJ']['price']);
                $this->getUser()->setBalance(($nBalance));
                $this->uRepository->add($user);
                $user->setBalance($user->getBalance() + $amount );
                $this->uRepository->add($this->getUser());

                $diffPrice       = ($user->getPrice()['BJ']['price']) - ($user->getBrand()->getManager()->getPrice()['BJ']['price']);
                $diffAmount      = ($amount / $user->getPrice()['BJ']['price']) - ($amount / $user->getBrand()->getManager()->getPrice()['BJ']['price']);
                $commission      = $diffAmount * $diffPrice;
                $beforeCommission= $user->getBrand()->getCommission();
                $afterCommission = $nCommission = $beforeCommission + $commission;
                $user->getBrand()->setCommission($nCommission);
                $this->bRepository->add($user->getBrand());

                // if($checkEtat[0]==0 || $checkEtat[0]==1 ){
                    // if($user->getBrand()->getName() == "FASTERMESSAGE"){
                    //     $brand           = $this->bRepository->findOneBy(['manager'=> $user]);
                    //     // if($brand){$beforeCommission= $afterCommission = $commission = $brand->getCommission();}
                    // }else{
                        // $diffPrice       = $user->getPrice() - $user->getBrand()->getManager()->getPrice();
                        // $diffAmount      = ($amount / $user->getPrice()) - ($amount / $user->getBrand()->getManager()->getPrice());
                        // $commission      = $diffAmount * $diffPrice;
                        // $beforeCommission= $brand->getCommission();
                        // $afterCommission = $nCommission = $beforeCommission + $commission;
                        // $user->getBrand()->setCommission($nCommission);
                        // $this->bRepository->add($user->getBrand());
                    // }
                // }
                $info            = $this->intl->trans("Rechargement de l'utilisateur ").$user->getEmail().$this->intl->trans(" effectué avec succès.");
                $linkRedirect    = NULL;
            }else{
                $processMobile = true;
            }
        }else{
            // //Initialisation du paiement mobile
            // $data = [
            //     "description"   => "Rechargement de compte", "amount"=> 100, "firstname"    => 'Support', "lastname" => 'fastermessage',
            //     "email"         => "support@fastermessage.com", "phone_number"  => '+22952734444', "internal_ref"  => 'ji89oiji31',
            //     "process"       => "MOBILE","expired"      => date("Y-m-d H:i:s")
            // ];

            // $initPay = $this->callApi($data);
            // return $this->services->msg_success(
            //     $this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->getUser()->getEmail(),
            //     $initPay,
            //     $initPay->link,
            // );
        }

        if(!$idTransaction){ $idTransaction = 'IFAS'.$this->services->getUniqid();};
        if(!$reference){ $reference = 'IFAS'.$this->services->getUniqid();};

        $newTransaction =  new Transaction;
        $newTransaction -> setUser($userTransaction)
                        -> setStatus($statusEntity)
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
                        -> setStatus($statusEntity)
                        -> setUid('RFAS'.$this->services->getUniqid())
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

    //Fonction de calcul de commission
    public function sumCommission(){

        return $commission;
    }

    //Use this function to check Status
    public function checkStatus($status){
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
        return $status;
    }
    #[Route('{_locale}/reloadrecharge', name: 'reload_recharge')]
    public function setRecharge(Request $request){
        // if(!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) return $this->services->no_access($this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->getUser()->getEmail());
        // if(!$this->pRecharge){
        //     return $this->services->no_access($this->intl->trans("Utilisateur non autorisé pour effectuer une actualisation rechargement.").': '.$this->getUser()->getEmail(), $this->intl->trans("Ooops... Vous n'êtes pas autorisé(e) à effectuer cette action."));
        // }
        $executeReq = $this->services->checkThisUser($this->pAllAccess, $user = null, $pManager = null, $pBrand = null);
        switch($executeReq[0]){
            case '2':

        }
        if($request->request->get('ref')){
            return $this->services->msg_error($this->intl->trans("La recharge n'existe pas.").': '.$this->getUser()->getEmail(), $this->intl->trans("Recharge inexistante."));
        }
        $checkTransaction = $this->tRepository->findOneBy(['reference'=> $request->get('ref'), 'status'=> $this->services->status(2)]);
        //Appel de la vérification de l'état de la transaction
        //$statusEntity = $this->checkStatus($checkRecharge->status);
        //$checkRecharge->getTransaction()->getStatus()->getName();
        //checkTransaction
        // dd($checkTransaction);
        //Simulation $checkTransaction->status = En attente
        $status = ($checkTransaction->getStatus()->getName() == 'En attente') ? 'pending' : $checkTransaction->getStatus()->getName();


        $status = 'approved';
        switch($status){
            //à completer si d'autres statut
            case 'pending':
                return $this->services->msg_error($this->intl->trans("Votre recharge est toujours en attente. Si vous pensez à une erreur, veuillez patienter un peu ou contacter le support.").': '.$this->getUser()->getEmail(), $this->intl->trans("Vérification de statut de transaction."));
                break;

            case 'approved':
                $beforeCommission = $afterCommission = $commission = $comRecharge = 0;
                $updateDate       = new \DatetimeImmutable();
                $nBalance = $checkTransaction->getUser()->getBalance() + $checkTransaction->getAmount();

                $checkTransaction ->setStatus($this->services->status(6))
                                  ->setBeforeBalance($checkTransaction->getUser()->getBalance())
                                  ->setAfterBalance($nBalance)
                                  ->setUpdatedAt($updateDate);
                $this->tRepository->add($checkTransaction);


                $checkRecharge = $this->rRepository->findOneBy(['transaction'=> $checkTransaction]);
                $checkCommision  = $this->bRepository->findOneBy(['manager'=> $admin]);
                if(!$checkCommision){
                    $checkCommision  = $this->bRepository->findOneBy(['manager'=> $admin]);
                }
                // dd($checkRecharge->getCommission());
                if($checkCommision){
                    $comRecharge      = $checkRecharge->getCommission();
                    $beforeCommission = $checkCommision->getCommission();
                    $afterCommission  = ($checkCommision->getCommission() + $comRecharge);
                    $checkCommision->setCommission($afterCommission);
                    $this->bRepository->add($checkCommision);
                }

                $checkRecharge -> setStatus($this->services->status(6))
                               -> setUpdatedAt($updateDate)
                               -> setBeforeCommission($beforeCommission)
                               -> setAfterCommission($afterCommission);
                $this->rRepository->add($checkRecharge);

                $checkTransaction->getUser()->setBalance($nBalance);
                $this->uRepository->add($checkTransaction->getUser());

                break;

            case 'canceled':
                $checkTransaction->setStatus($this->services->status(7));
                $this->rRepository->add($checkRecharge);
                break;
            default:
                $status = $this->services->status(1);
                break;
        }
        return $this->services->msg_success(
            $this->intl->trans("Votre rechargement a été effectuée avec succès.").': '.$this->getUser()->getEmail(),
            'patienter',
            'fgggf',
        );


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
                $row              = array();
                $row['ref']       = $getRecharge->getUid();
                $row['amount']    = $getRecharge->getTransaction()->getAmount();
                $row['emailS']    = $getRecharge->getUser()->getEmail();
                $row['emailR']    = $getRecharge->getRechargeBy()->getEmail();
                $row['status']    = $getRecharge->getStatus()->getCode();
                $row['date']      = $getRecharge->getCreatedAt()->format("d-m-Y H:i");
                $row['action' ]   = [
                                        'uid'    =>  $getRecharge->getTransaction()->getReference(),
                                        'status' => $getRecharge->getStatus()->getCode()
                ];
                $data[]           = $row;
            }
        }
        $this->services->addLog($this->intl->trans('Lecture de la liste des recharges'));
        $output = array("data" => $data);
        return new JsonResponse($output);

    }
    //This function is used re
}
