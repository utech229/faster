<?php

namespace App\Service;

use App\Service\Brand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\sFedapay;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransactionRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class sAgregatorRouter extends AbstractController
{
    protected $brand;
   
	public function __construct(TranslatorInterface $intl, EntityManagerInterface $entityManager, 
    TransactionRepository $transactionRepository, Services $services, sFedaPay $sFedapay,
    BaseUrl $baseUrl, uBrand $brand, UrlGeneratorInterface $urlGenerator,)
	{
        $this->baseUrl       = $baseUrl->init();
        $this->urlGenerator  = $urlGenerator;
        $this->brand         = $brand;
        $this->intl     = $intl;
        $this->em       = $entityManager;
        $this->services = $services;
        $this->sFedapay = $sFedapay;
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

    public function uPay(){
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pay.zekin.app/api/v1/transactions/create',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(),
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer l0899bzWX40trcpqxwC545'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;

        $description = $request->request->get('description', '');
        $amount = (float)$request->request->get('amount', 0);
        $firstname = $request->request->get('firstname', null);
        $lastname = $request->request->get('lastname', null);
        $email = $request->request->get('email', null);
        $phone_number = $request->request->get('phone_number', null);
        $internal_ref = $request->request->get('internal_ref', null);
        $process = $request->request->get('process', "");
        $expired = $request->request->get('expired', null);
    }
    
}
