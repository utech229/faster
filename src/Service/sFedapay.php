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
	public function __construct(TranslatorInterface $intl, Services $services, EntityManagerInterface $entityManager,
    UrlGeneratorInterface $urlGenerator, BrickPhone $brickPhone)
	{
       $this->intl         = $intl;
       $this->em           = $entityManager;
       $this->brickPhone   = $brickPhone;
       $this->urlGenerator = $urlGenerator;
       $this->services = $services;
    }

    public function fedapayIniter()
    {
        include_once ('../vendor/autoload.php');
        \FedaPay\FedaPay::setApiKey("");
		\FedaPay\FedaPay::setEnvironment('live');
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
                                        "firstname"     => $receiveUser->getFirstName(),
                                        "lastname"      => $receiveUser->getLastName(),
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
                    dd($initedPayout);
    
        switch($getStatus['status']){
            case 'pending':
                $msg    = $this->messageStatus['pending'];
                $status = $getStatus['status'];
                break;
            case 'started':
                $msg    = $this->messageStatus['started'];
                $status = $getStatus['status'];
                break;
            case 'processing':
                $msg    = $this->messageStatus['processing'];
                $status = $getStatus['status'];
                break;
            case 'sent' :
                $msg    = $this->messageStatus['sent'];
                $status = $getStatus['status'];
                $thisPayment->setObservations($id)
                            ->setStatus(1);
                $manager->persist($thisPayment);
                $manager-> flush();

                break;
            case 'failed':
                $checkAccount2  = $this->automaticPay1($apiFaster, $receiver);
                $msg            = $checkAccount2['msg'];
                $status         = $checkAccount2['status'];
                $id             = $checkAccount2['id'];
                $root           = 2;

                break;
            default:
                $msg    = $this->translator->trans("Votre demande instantanée ne peut aboutir. Veuillez cliquer sur le bouton, 'demander un paiement' pour que nous traitons votre requête. Merci !");
                $status = 'error';
                break;
        }
        $thisPayment->setObservations($id);
        $manager->persist($thisPayment);
        $manager-> flush();
        return ['msg'=>$msg, 'status'=>$status, 'id'=> $id, 'root'=> $root];
    }

    public function processRouter($countrycode, $data)
    {
        $codeFedaCountry = array("BJ", "CI", "BF", "SN", "TG", "NE");
        if (in_array($countrycode, $codeFedaCountry)) 
        {
            //fedapay route
            $data['callback_url'] =  $this->baseUrl.''.$this->urlGenerator->generate('app_fedapay_callback_url');
            return $this->sFedapay->initPay($data);
        }else {
            $message = $this->intl->trans("Le paiement mobile n'est pas disponible dans votre pays pour l'instant, veuillez passer par le paiement par carte");
            return $this->services->ajax_success_crud(
                $this->intl->trans("Echec de paiement mobile : indisponibilité du pays sur le pays"),
                $message, false
            );
        }
    }

    

    
}
