<?php

namespace App\Service;

use App\Service\BaseUrl;
use App\Service\Services;
use App\Repository\BrandRepository;
use App\Repository\StatusRepository;
use App\Repository\CompanyRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class uBrand extends AbstractController
{
    protected $brand;
   
	public function __construct(TranslatorInterface $intl, BaseUrl $baseUrl, BrandRepository $brandRepository,
    CompanyRepository $companyRepository, StatusRepository $statusRepository, Services $services)
	{
       $this->intl    = $intl;
       $this->baseUrl = $baseUrl;
       $this->services = $services;
       $this->brandRepository = $brandRepository;
       $this->companyRepository = $companyRepository;
       $this->statusRepository = $statusRepository;
    }

    public function get(){
        $search_brand = $this->brandRepository->findOneBy(['siteUrl' => 'https://'.$_SERVER['HTTP_HOST'], "status" => $this->services->status(3)]);
        $brand        = ($search_brand) ? $search_brand : $this->brandRepository->findOneBy(['siteUrl' => 'http://'.$_SERVER['HTTP_HOST'], "status" => $this->services->status(3)]);
        $brandAdmin   = $brand->getManager();
        $company      = ($brandAdmin) ? $brandAdmin->getCompany() : $this->companyRepository->findOneBy(['id' => 1]);
        return[
           'name'               => $brand->getName(),
           'site_url'           => $brand->getSiteUrl(),
           'logo'               => $brand->getLogo(),
           'white_logo'         => $brand->getLogo(),
           'favicon'            => $brand->getFavicon(),
           'apple_touch_icon'   => $brand->getLogo(),
           'phone'              => $brand->getName(),
            'emails'           => [
                'noreply'     => $brand->getNoreplyEmail(),
                'support'     => $brand->getEmail(),
            ],
           'author'             => [
               'name'           => $company->getName(),
               'ifu'            => $company->getIfu(),
               'rccm'           => $company->getRccm(),
           ],
           'year'               => date('Y'),
           'brand'              =>  $brand,
           'formview'           => ($brand->getName() == "FASTERMESSAGE") ? "login.html.twig" : "blanck.login.html.twig",

           
       ];
    }
  

    
}
