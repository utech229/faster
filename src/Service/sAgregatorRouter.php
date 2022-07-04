<?php

namespace App\Service;

use App\Service\Brand;
use App\Service\sUpay;
use App\Service\BaseUrl;
use App\Service\Services;
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
    TransactionRepository $transactionRepository, Services $services, sUpay $sUpay,
    BaseUrl $baseUrl, uBrand $brand, UrlGeneratorInterface $urlGenerator,)
	{
        $this->baseUrl       = $baseUrl->init();
        $this->urlGenerator  = $urlGenerator;
        $this->brand         = $brand;
        $this->intl     = $intl;
        $this->em       = $entityManager;
        $this->services = $services;
        $this->sUpay    = $sUpay;
    }

    public function processRouter($countrycode, $data)
    {
        $codeCountry = array("BJ", "CI", "BF", "SN", "TG", "NE");
        if (in_array($countrycode, $codeCountry)) 
        {
            return  $this->sUpay->upay_init($data);
        }else {
            $message = $this->intl->trans("Le paiement mobile n'est pas disponible dans votre pays pour l'instant, veuillez passer par le paiement par carte");
            return $this->services->msg_success(
                $this->intl->trans("Echec de paiement mobile : indisponibilité du pays sur le pays"),
                $message, false
            );
        }
    }

   
    
}
