<?php

namespace App\Service;

use FedaPay\FedaPay;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\BrickPhone;
use App\Service\sTransaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class sFedapay extends AbstractController
{  
	public function __construct(TranslatorInterface $intl, Services $services, EntityManagerInterface $entityManager, sTransaction $sTransaction,
    UrlGeneratorInterface $urlGenerator, BrickPhone $brickPhone)
	{
       $this->intl         = $intl;
       $this->em           = $entityManager;
       $this->sTransaction = $sTransaction;
       $this->brickPhone   = $brickPhone;
       $this->urlGenerator = $urlGenerator;
       $this->services = $services;
    }

    public function fedapayIniter()
    {
        include_once ('../vendor/autoload.php');
        \FedaPay\FedaPay::setApiKey("sk_live_66NqS0Euw8mp6axMo-GH-bi8");
		\FedaPay\FedaPay::setEnvironment('live');
    }

    public function initPay($data, $redirect = false)
    {
        $user = $this->getUser();
        //loading fedapay account api data
        $this->fedapayIniter();
        //transaction initiation
        $initPayment        = \FedaPay\Transaction::create([
            "description"   =>  $data['description'],
            "amount"        =>  $data['amount'],
            "currency"      =>  ["iso" => "XOF"],
            "callback_url"  =>  $data['callback_url'],
            "customer"      =>  [
                                    "firstname" => $user->getFirstName(),
                                    "lastname"  => $user->getLastName(),
                                    "email"     => $user->getEmail(),
                                    "phone_number" => [
                                        "number"  => $data['phone'],
                                        "country" => strtolower($user->getCountry()['code'])
                                    ]            
                                ]
        ]);
        if($initPayment)
        {
            //transaction datas
            $tDatas = [
                'idTransaction' => $initPayment->id,
                'reference'     => $initPayment->reference,
                'amount'        => $data['amount']
            ];
        
            $transaction = $this->sTransaction->create($tDatas); 
            if ($transaction) 
            {
                $message = $this->intl->trans("Opération initialisée, veuillez confirmer le paiement initialisé.");
                if ($redirect == false) {
                    
                    $token = $initPayment->generateToken();
                    return $this->services->ajax_success_crud(
                        $this->intl->trans("Paiement initialisé"),
                        $message, ["token" => $token]
                    );
                }
                else
                {
                    $phoneDataRetrived = $this->brickPhone->phoneDataRetriving($user->getPhone());
                    $token = $initPayment->generateToken()->token;
                    $mode  =  $phoneDataRetrived['GSM'];
                    $initPayment->sendNowWithToken($mode, $token);
                    return $this->services->ajax_success_crud(
                        $this->intl->trans("Paiement initialisé"),
                        $message
                    );
                }
            }
            else 
            {
                $message = $this->intl->trans("La création de la transaction à échoué, veuillez réessayer");
                return $this->services->msg_error(
                    $this->intl->trans("Création de transaction"),
                    $this->intl->trans("Echec de la création de la transaction")
                );
            }
        }
        else
        {
            $message = $this->intl->trans("Votre numéro de téléphone est invalide. Veuillez le changer avant de poursuivre l'opération.");
            return $this->services->msg_error(
                $this->intl->trans("Création de transaction"),
                $this->intl->trans("Echec de la création de la transaction")
            );
        }   
    }

     //Cette fonction permet de prélever l'argent sur Le compte fedapay
     public function automaticPay($receiver)
     {
        //loading fedapay account api data
        $this->fedapayIniter();
        $thisPayment = $receiver['payment'];
        $receiveUser = $receiver['payment']->getUser();

        $dataSend    = [
                        "amount"    => $thisPayment->getAmount(),
                        "currency"  => ["iso" => "XOF"],
                        "mode"      => "mtn",
                        "customer"  => [
                                        "firstname"     => $receiveUser->getUsetting()->getFirstName(),
                                        "lastname"      => $receiveUser->getUsetting()->getLastName(),
                                        "email"         => $receiveUser->getEmail(),
                                        "phone_number"  => [ "number"    => $thisPayment->getReceptionPhone(),
                                                            "country"   => "bj" ]
                        ]
        ];
        
        $payout = \FedaPay\Payout::create($dataSend);
        //$payout->sendNow();

        $initedPayout = \FedaPay\Payout::retrieve($payout->id);
        $thisPayment->setObservation($this->intl->trans("Paiement automatique initialisé avec l'id")." : ".$payout->id)
                    ->setUpdatedAt(new \DatetimeImmutable())
                    ->setValidator($receiver['validator'])
                    ->setTransactionId($payout->id)
                    ->setType($receiver['paymentType']);

        $getStatus['status'] = $payout->status;
    
        switch($getStatus['status']){
            case 'pending':
                $msg    = $this->intl->trans("Paiement en attente d'envoi");
                $status = $getStatus['status'];
                break;
            case 'started':
                $msg    = $this->intl->trans("Envoi du paiement démarré");
                $status = $getStatus['status'];
                break;
            case 'processing':
                $msg    = $this->intl->trans("Paiement en en cours d'envoi");
                $status = $getStatus['status'];
                break;
            case 'sent' :
                $msg    = $this->intl->trans("Paiement en envoyé avec succès");
                $status = $getStatus['status'];
                $thisPayment->setObservation($id)
                            ->setStatus(1);
                $this->em->persist($thisPayment);
                $this->em-> flush();
                return $this->services->msg_success(
                    $this->intl->trans("Paiement éffectué"), $msg
                );
                break;
            default:
                $msg    = $this->translator->trans("Votre demande instantanée ne peut aboutir. Veuillez cliquer sur le bouton, 'demander un paiement' pour que nous traitons votre requête. Merci !");
                $status = 'error';
                break;
        }
        $thisPayment->setObservation($payout->id);
        $this->em->persist($thisPayment);
        $this->em-> flush();
        return $this->services->msg_info(
            $this->intl->trans("Paiement de commission"), $msg
        );
    }


    

    
}
