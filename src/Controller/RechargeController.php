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
        // if(!$this->pAccess){
        //     $this->addFlash('error', $this->intl->trans("Tentative d'accès à la page marque échouée.")); return $this->redirectToRoute("app_home");
        // }

        // dd($this->pRechargeUser);
        $checkEtat = $this->services->checkThisUser($this->pAllAccess);
        $users = $brands = [];
        switch($checkEtat[0]){
            case 0: $users = $this->uRepository->findBy(['status'=> $this->services->status(3)]);
            $brands = $this->bRepository->findBy(['status'=> $this->services->status(3) ]); break;
            case 1: $users = $this->services->getUserByPermission('MANGR');
            // $brand = $this->bRepository->findBy(['status'=> $this->services->status(3), 'manager'=> ]);
             break;
            case 2: $users = $this->services->getUserByPermission('BRND1');
            $brands = $this->bRepository->findBy(['manager'=> $this->getUser(), 'status'=> $this->services->status(3) ]); break;
            case 3: $users = $this->services->getUserByPermission('BRND1');
            $brands = $this->bRepository->findBy(['manager'=> $this->getUser()->getAffiliateManager(), 'status'=> $this->services->status(3) ]);break;
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
            'users'              => $users,
            'brands'             => $brands,
            'pRechargeUser'=>$this->pRechargeUser
        ]);
    }
    //API function
    public function callApi($info){
        $url = "http://pay.zekin.app/api/v1/transactions/create";
        $headers = [
                    "Accept: application/json", "Authorization: Bearer p0196VDWX41X65vr45SrcpqyvA81", "Content-Type: application/json",
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

    //Build Commission
    public function buildCom($amount, $user, bool $self = false){

        $admin               = $user->getBrand()->getManager();
        $user                = $user;
        $bBalanceAdmin       = $admin->getBalance();
        if(!$self){
            $nBalanceAdmin   = $bBalanceAdmin - $amount;
            $admin->setBalance($nBalanceAdmin);
            $this->uRepository->add($admin);
        }
        $aBalanceAdmin       = $admin->getBalance();
        $bBalanceUser        = $user->getBalance();
        $aBalanceUser        = $nBalanceUser = ($bBalanceUser + $amount);
        $user->setBalance($nBalanceUser);
        $this->uRepository->add($user);

        $priceU              = $user->getPrice()['BJ']['price'];
        $priceA              = $admin->getPrice()['BJ']['price'];

        //Si l'utilisateur rechargé est un gestionnaire ou administrateur ne peut pas calculer la commission
        $diffAmount          = ($amount / $priceA) - ($amount / $priceU);
        $commission          = $diffAmount * $priceA;
        $beforeCommission    = $user->getBrand()->getCommission();
        $afterCommission     = $nCommission = ($beforeCommission + $commission);
        $user->getBrand()    ->setCommission($nCommission);
        $this->bRepository   ->add($user->getBrand());

       return $data = [
            // 'rechargeBy'     => $rechargeBy,
            'bBalanceAdmin'  => $bBalanceAdmin,
            'aBalanceAdmin'  => $aBalanceAdmin,
            'bBalanceUser'   => $bBalanceUser,
            'aBalanceUser'   => $aBalanceUser,
            'beforeCommission'=> $beforeCommission,
            'afterCommission'=> $afterCommission,
            'commission'     => $commission
        ];
    }
    //We use this function to create a new recharge for user
    #[Route('{_locale}/createrecharge', name: 'create_recharge')]
    public function createRecharge(Request $request){

        if(!$this->pRecharge){
            return $this->services->no_access($this->intl->trans("Utilisateur non autorisé pour effectuer un rechargement."), $this->intl->trans("Ooops... Vous n'êtes pas autorisé(e) à effectuer cette action."));
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
        $creatDate       = new \DatetimeImmutable();
        $idTransaction   = $reference = $updateDate = NULL;
        $beforeCommission= $afterCommission = $commission = 0;
        $checkEtat       = $this->services->checkThisUser($this->pAllAccess, $user);

        $processMobile   = true;
        // dd($checkEtat);
        if(in_array($checkEtat[0], [0, 1, 2, 3])){
            if($typeRecharge == $this->typeRecharge[0]){
                if($amount > $this->getUser()->getBalance()){return $this->services->msg_error($this->intl->trans("Balance du revendeur incorrecte.").': '.$this->getUser()->getEmail(), $this->intl->trans("Le montant à recharger est supérieur à votre balance. Veuillez approvisionner votre compte pour continuer."));}
                $buildCom        = $this->buildCom($amount, $user);
                $rechargeBy      = $userTransaction = $this->getUser();
                $userTransaction = $rechargeBy = $this->getUser();
                $bBalanceAdmin   = $buildCom['bBalanceAdmin'];
                $aBalanceAdmin   = $buildCom['aBalanceAdmin'];
                $bBalanceUser    = $buildCom['bBalanceUser'];
                $aBalanceUser    = $buildCom['aBalanceUser'];
                $beforeCommission= $buildCom['beforeCommission'];
                $afterCommission = $buildCom['afterCommission'];
                $commission      = $buildCom['commission'];
                $statusEntity    = $this->checkStatus("approved");
                $updateDate      = new \DatetimeImmutable();

                $info            = $this->intl->trans("Rechargement de l'utilisateur ").$user->getEmail().$this->intl->trans(" effectué avec succès.");
                $linkRedirect    = NULL;
                $processMobile   = false;
            }else{
                $processMobile = true;
            }
        }
        if($processMobile)
        {
            //Initialisation du paiement mobile
            $reference  = 'IFAS'.$this->services->getUniqid();
            $data = [
                "description"   => "Rechargement de compte", "amount"=> $amount, "firstname"    => 'Support', "lastname" => 'fastermessage',
                "email"         => "support@fastermessage.com", "phone_number"  => '+22952734444', "internal_ref"  => $reference,
                "process"       => "MOBILE","expired"      => date("Y-m-d H:i:s")
            ];

            $initPay             = $this->callApi($data);
            $idTransaction       = $initPay['id_transaction'];
            $admin               = $rechargeBy = $userTransaction = $this->getUser();
            $bBalanceAdmin       = $admin->getBalance();
            $aBalanceAdmin       = $bBalanceAdmin;
            $bBalanceUser        = $user->getBalance();
            $aBalanceUser        = NULL;
            $statusEntity        = $this->checkStatus("pending");
            $info                = $this->intl->trans("Votre rechargement est en cours. Cliquez sur le bouton ci-dessous pour finaliser ");
            $linkRedirect        = $initPay['link'];
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
                        -> setBeforeBalance($bBalanceAdmin)
                        -> setAfterBalance($aBalanceAdmin);
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
                        -> setAfterCommission($afterCommission)
                        -> setBeforeBalance($bBalanceUser)
                        -> setAfterBalance($aBalanceUser);
        $this->rRepository->add($newRecharge);

        return $this->services->msg_success(
            $this->intl->trans("Initialisation d'une recharge pour l'utiliateur "),
            $info,
            $linkRedirect,
        );
    }
    //Use this function to check Status
    public function checkStatus($status){
        switch($status){
            //à completer si d'autres statut
            case 'pending':
                $status = $this->services->status(2);
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
    //Use to reload status transaction
    #[Route('{_locale}/reloadrecharge', name: 'reload_recharge')]
    public function setRecharge(Request $request){
        $checkTr = $this->tRepository->findOneBy(['transactionId'=> $request->get('u'), 'status'=> $this->services->status(2)]);
        if(!$checkTr){
            $checkRecharge = $this->rRepository->findOneBy(['uid'=> $request->get('u'), 'status'=> $this->services->status(2)]);
        }else{
            $checkRecharge = $this->rRepository->findOneBy(['transaction'=> $checkTr, 'status'=> $this->services->status(2)]);
        }
        if(!$checkRecharge){
            return $this->services->msg_error($this->intl->trans("La recharge n'existe plus ou est déjà approuvée."), $this->intl->trans("La recharge n'existe plus ou est déjà approuvée. Si vous pensez à une erreur, contactez votre administrateur."));
        }
        //Appel à la fonction de vérification du statut de la transaction
        $status = ($checkRecharge->getTransaction()->getStatus()->getName() == 'En attente') ? 'pending' : $checkRecharge->getTransaction()->getStatus()->getName();

        //Simulation
        // $status = 'approved';
        switch($status){
            //à completer si d'autres statut
            case 'approved':
                $execute     = $this->buildCom($checkRecharge->getTransaction()->getAmount(), $checkRecharge->getUser(), true);
                $updateDate  = new \DatetimeImmutable();
                $checkRecharge->getTransaction()->setBeforeBalance($execute['bBalanceAdmin'])
                                                ->setAfterBalance($execute['aBalanceAdmin'])
                                                ->setUpdatedAt($updateDate)
                                                ->setStatus($this->services->status(6));
                $this->tRepository->add($checkRecharge->getTransaction());

                $checkRecharge-> setBeforeCommission($execute['beforeCommission'])
                              -> setCommission($execute['commission'])
                              -> setAfterCommission($execute['afterCommission'])
                              -> setBeforeBalance($execute['bBalanceUser'])
                              -> setAfterBalance($execute['aBalanceUser'])
                              -> setUpdatedAt($updateDate)
                              -> setStatus($this->services->status(6));
                $this->rRepository->add($checkRecharge);
                $info = $this->intl->trans("Le rechargement de ").$checkRecharge->getTransaction()->getAmount().$this->intl->trans(" a été effectuée avec succès.");
                return $this->services->msg_success($info, $info, 'info');
                break;
            case 'pending':
                $info = $this->intl->trans("Le rechargement de ").$checkRecharge->getTransaction()->getAmount().$this->intl->trans(" est toujours en attente. Veuillez confirmer si ce n'est pas encore fait.");
                return $this->services->msg_warning($info, $info, 'info');
                break;
            case 'canceled':
                $updateDate       = new \DatetimeImmutable();
                $checkRecharge->getTransaction()->setUpdatedAt($updateDate)
                                                ->setStatus($this->services->status(7));
                $this->tRepository->add($checkRecharge->getTransaction());

                $checkRecharge->setUpdatedAt($updateDate)
                              ->setStatus($this->services->status(7));
                $this->rRepository->add($checkRecharge);
                $info = $this->intl->trans("La transaction de cette recharge a été annulée. Si vous pensez qu'il s'agit d'une erreur, contactez votre adminitrateur.");
                        return $this->services->msg_success($info, $info, 'error');

                break;
            default:
                $info = $this->intl->trans("Le rechargement a changé de statut, vérifiez votre solde ou contactez votre administrateur de compte.");
                return $this->services->msg_info($info, $info, 'info');
                break;
        }
        //Envoyer un paramètre à la page de paiement
        if($request->get('r')){
            $this->addFlash('success', $this->intl->trans("Votre rechargement a été fait avec succès.")); return $this->redirectToRoute("app_recharge");
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
        $isSelect = $request->get('uSelect');
        $user     = '';
        if($isSelect){
            $user = $this->uRepository->findOneBy(['uid'=> $isSelect]);
            if(!$user) return $this->services->msg_error($this->intl->trans("Utilisateur inexistant."), $this->intl->trans("L'utilisateur n'existe pas."));
        }

        $checkEtat = $this->services->checkThisUser($this->pAllAccess);
        // dd($checkEtat[0]);
        switch($checkEtat[0]){
            case 0: $allRecharges  = $this->getRecharge($this->pAllAccess, $user); break;
            case 1: $allRecharges  = $this->rRepository->getRechargeByManager(1); break;
            case 2: $allRecharges  = $this->rRepository->getRechargeByReseller($this->getUser()->getUid()); break;
            case 3: $allRecharges  = $this->getRecharge($this->pAllAccess, $this->getUser()->getAffiliateManager()); break;
            case 4: $allRecharges  = $this->getRecharge($this->pAllAccess, $this->getUser()); break;
            default: $allRecharges = $this->getRecharge($this->pAllAccess, $this->getUser()->getAffiliateManager()); break;
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
                                        'uid'    =>  $getRecharge->getUid(),
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
