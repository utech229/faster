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
    
}
