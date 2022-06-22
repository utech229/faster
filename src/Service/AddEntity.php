<?php

namespace App\Service;

use App\Entity\Log;
use App\Entity\User;
use App\Entity\Brand;

use App\Entity\Company;
use App\Service\sBrand;
use App\Entity\Usetting;
use App\Service\Services;
use App\Repository\BrandRepository;
use App\Repository\StatusRepository;
use App\Repository\CompanyRepository;
use App\Repository\UsettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AddEntity extends AbstractController
{
    private $urlGenerator;
    private $router;
    private $translator;
    private $placeAvatar;
    private $filename;


    public function __construct(RouterInterface $router, UrlGeneratorInterface $urlGenerator,  TranslatorInterface $Translator,
    EntityManagerInterface $entityManager, UsettingRepository $usettingRepository, Services $services, sBrand $brand,
    BrandRepository $brandRepository, CompanyRepository $companyRepository, StatusRepository $statusRepository ){
        $this->router = $router;
        $this->urlGenerator = $urlGenerator;
        $this->intl = $Translator;
        $this->em = $entityManager;
        $this->services = $services;
        $this->usettingRepository = $usettingRepository;
        $this->brandRepository = $brandRepository;
        $this->statusRepository = $statusRepository;
        $this->companyRepository = $companyRepository;
        $this->brand   = $brand;
    }

    public function profilePhotoSetter($request , $user , $isUpdating = false)
	{
        $response = new Response();

        $placeAvatar  = $this->getParameter('avatar_directory');
        $filename     = $user->getUid().'_'.date('Y');
        $filepath     = $placeAvatar.$user->getProfilePhoto();

		$response->headers->set('Content-Type', 'application/json');
		$response->headers->set('Access-Control-Allow-Origin', '*');

		$image_remove	=	$request->request->get("avatar_remove");
		/** @var UploadedFile $SETTINGFILE */
        $SETTINGFILE    =	$request->files->get('avatar');
        $image  = ($image_remove == "1") ? "default_avatar_1.png" : (($isUpdating) ? $user->getProfilePhoto() : "default_avatar_1.png" );
        if(isset($SETTINGFILE) && $SETTINGFILE->getError() == 0){
            $return	=	$this->services->checkFile($SETTINGFILE, ["jpeg", "jpg", "png", "JPEG", "JPG", "PNG"], 200024);
            if($return['error'] == false) {
                if (file_exists($filepath)) {
                    if(strpos($user->getProfilePhoto(), 'default_') === false) $this->services->removeFile($placeAvatar, $user->getProfilePhoto());
                }
                return $this->services->renameFile($SETTINGFILE, $placeAvatar, true, $placeAvatar, $filename);
            }else
                return [
                    'error' => true,
                    'info'  => $return['info'],
                ];

        } else
        return $image;
	}

    //usetting datas for new user
    public function defaultUsetting($user, $firstname = null, $lastname = null)
    {
        $usetting = new Usetting();

        $language  = [ 'code' => 'fr', 'name' => 'French'];
        $currency  = [ 'code' => 'XOF', 'name' => "West African CFA Franc"];

        $usetting->setUid($this->services->idgenerate(11))
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setLanguage($language)
                ->setCurrency($currency)
                ->setTimezone('+01:00')
                ->setCreatedAt(new \DatetimeImmutable())
                ->setUser($user);

        $this->usettingRepository->add($usetting);
    }

    //usetting datas for existed user
    public function updateUsetting($data, $usetting)
    {
        $language  = ['code' => $data['lang_code'],  'name' =>$data['lang_name'] ];
        $currency  = [ 'code' => $data['currency_code'], 'name' => $data['currency_name']];

        $usetting->setLanguage($language)
                 ->setCurrency($currency)
                 ->setTimezone($data['timezone'])
                 ->setupdatedAt(new \DatetimeImmutable());

        $this->usettingRepository->add($usetting);
    }

    //brand datas for new user
    public function defaultBrand()
    {
        if (count($this->brandRepository->findAll()) > 0) return false;
        $defaultbrand  = $this->brand->get();
        $brand = new Brand();
        $brand->setUid($this->services->idgenerate(10))
                    ->setStatus($this->statusRepository->findOneByCode(3))
                    ->setName($defaultbrand['name'])
                    ->setSiteUrl($defaultbrand['base_url'])
                    ->setFavicon($defaultbrand['favicon_link'])
                    ->setEmail($defaultbrand['emails']['support'])
                    ->setLogo($defaultbrand['logo_link'])
                    ->setCommission(0)
                    ->setNoreplyEmail('noreply@'.$defaultbrand['base_url'])
                    ->setPhone($defaultbrand['phone']['bj'])
                    ->setIsDefault(true)
                    ->setCreatedAt(new \DatetimeImmutable());

        $this->brandRepository->add($brand);
    }

    //usetting datas for new user
    public function defaultCompany()
    {
        $defaultBrand = $this->brand->get();
        if (count($this->companyRepository->findAll()) > 0) return false;
        $company      = new Company();
        $company->setUid($this->services->idgenerate(11))
                    ->setStatus($this->statusRepository->findOneByCode(3))
                    ->setIfu($defaultBrand['identifier']['ifu'])
                    ->setRccm(($defaultBrand['identifier']['rccm']))
                    ->setAddress(($defaultBrand['author']['address']))
                    ->setName($defaultBrand['author']['name'])
                    ->setEmail($defaultBrand['emails']['support'])
                    ->setPhone($defaultBrand['phone']['bj'])
                    ->setCreatedAt(new \DatetimeImmutable());

        $this->companyRepository->add($company);
    }




}
