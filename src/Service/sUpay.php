<?php

namespace App\Service;

use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\BrickPhone;
use App\Service\sTransaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class sUpay extends AbstractController
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

    public function upay_init($trans_param)
    {
        

        $sendtransacurl_prefix  = "http://pay.zekin.app/api/v1/transactions/create";
        $apikey                 = "l0899bzWX40t65JE45SrcpqxwZ42";
        $headers                = [
                                        "Accept: application/json", 
                                        'Authorization: Bearer '.$apikey, 
                                        "Content-Type: application/json",
                                        "Environment: prod"
                                ];

        $curl = curl_init();
        $transacurl_params = array(
            'description'      => $trans_param['description'],
            'amount'           => $trans_param['amount'],
            'firstname'        => $trans_param['firstname'],
            'lastname'         => $trans_param['lastname'],
            'email'            => $trans_param['email'],
            'phone_number'     => $trans_param['phone_number'],
            'internal_ref'     => $trans_param['internal_ref'],
            'process'          => $trans_param['process'],
            'expired'          => $trans_param['expired'],
        );
        $sendsmsurl = $sendtransacurl_prefix."?".http_build_query($transacurl_params);
     
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $sendsmsurl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT ,0);
        curl_setopt($curl, CURLOPT_HTTP_VERSION,  CURL_HTTP_VERSION_1_1);
       
        $resp   = curl_exec($curl); 
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
        curl_close($curl); 
        $dataDecode = json_decode($resp);

        if(isset($dataDecode->response))
        {
            switch ($dataDecode->response->status) 
            {
                case 200:
                    $return = [ 
                        'response' => [
                            "status"  => true,
                            "message" => $this->intl->trans("Veuillez confirmer le paiement initialisé sur votre numéro pour valider l'opération")
                        ], 
                        'status'        => $dataDecode->status,
                        'id_transaction'=> $dataDecode->id_transaction, 
                        'external_ref'  => $dataDecode->external_ref,
                        'message'       => $dataDecode->response->api->message,
                    ];
                    break;
                case 300:
                    $return = [ 
                        'response' => [
                            "status"  => true,
                            "message" => $this->intl->trans("Paiement initialisé, redirection ...")
                        ],
                        'link'          => $dataDecode->response->api->url, 
                        'status'        => $dataDecode->status,
                        'id_transaction'=> $dataDecode->id_transaction, 
                        'external_ref'  => $dataDecode->external_ref,
                        'message'       => $dataDecode->response->api->message,
                    ];
                    break;
                case 301:
                    $return = [ 
                        'response' => [
                            "status"  => true,
                            "message" => $this->intl->trans("Paiement initialisé, redirection ...")
                        ],
                        'link'          => $dataDecode->response->api->url, 
                        'status'        => $dataDecode->status,
                        'id_transaction'=> $dataDecode->id_transaction, 
                        'external_ref'  => $dataDecode->external_ref,
                        'message'          => $dataDecode->response->api->message,
                    ];
                    break;
                default:
                $return = [ 
                    'response' => [
                        "status"  => false,
                        "message" => $dataDecode->response->api->message
                    ],
                    'message'          => $dataDecode->response->api->message,
                ];
                    break;
            }
        }
        else 
        {
            $return = [ 
                'response' => [
                                "status"  => false,
                                "message" => $this->intl->trans("Error or bad response")
                            ]
            ];
        }
        return $return;
    }

 
    public function callApi($info){
        $url = "http://pay.zekin.app/api/v1/transactions/create";
        $headers = [
                    "Accept: application/json", 
                    "Authorization: Bearer l0899bzWX40trcpqxwC545", 
                    "Content-Type: application/json",
                    "Environment: prod"
        ];
        // dd($info);
        $data = [
            "description"   => $info['description'], "amount"          => $info['amount'], "firstname"    => $info['firstname'], "lastname" => $info['lastname'],
            "email"         => $info['email'], "phone_number"  => $info['phone_number'], "internal_ref"  => $info['internal_ref'],
            "process"       => $info['process'],"expired"      => $info['expired']
        ];
        $url = $url."?".http_build_query($data); 
        $curl = curl_init(); 
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url); 
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($curl); 
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
        curl_close($curl); 
        $dataDecode = json_decode($resp);
        $return = [
                        'link'          => $dataDecode->response->api->url, 'status'        => $dataDecode->status,
                        'id_transaction'=> $dataDecode->id_transaction, 'external_ref'      => $dataDecode->external_ref
        ];
        // dd($return);
        return $return;
    }

    

    
}
