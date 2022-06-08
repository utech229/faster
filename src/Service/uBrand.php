<?php

namespace App\Service;

use App\Service\BaseUrl;
use App\Repository\BrandRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class uBrand extends AbstractController
{
    protected $brand;
   
	public function __construct(TranslatorInterface $intl, BaseUrl $baseUrl, BrandRepository $brandRepository)
	{
       $this->intl    = $intl;
       $this->baseUrl = $baseUrl;
       $this->brandRepository = $brandRepository;
    }

    public function index(){
        $search_brand     = $this->brandRepository->findOneBy(['siteUrl' => 'https://'.$_SERVER['SERVER_NAME']]);
        $brand     = ($search_brand) ? $search_brand : $this->brandRepository->findOneBy(['siteUrl' => 'https://'.$_SERVER['SERVER_NAME']]);;
        $brandAdmin   = $brand->getManager();
        $company      = ($brandAdmin) ? $brandAdmin->getCompany() : $this->companyRepository->findOneBy(['id' => 1]);
        //dd($brandAdmin->getIsDlr(), $brandAdmin->getCompany(), $company);
        
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
           ],
           'year'               => date('Y')
           
       ];
    }

    
}
