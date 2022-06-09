<?php

namespace App\Controller;

use App\Service\uBrand;
use App\Entity\Company;
use App\Service\BaseUrl;
use App\Form\CompanyType;
use App\Service\Services;
use App\Service\AddEntity;
use App\Service\BrickPhone;
use App\Service\sUpgradeForm;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('/{_locale}/home/companies')]
class CompanyController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, UrlGeneratorInterface $urlGenerator, Services $services, BrickPhone $brickPhone,  
    EntityManagerInterface $entityManager, TranslatorInterface $translator, CompanyRepository $companyRepository, ValidatorInterface $validator,
    AddEntity $addEntity, uBrand $brand)
    {
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brickPhone      = $brickPhone;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->validator         = $validator;
        $this->companyRepository = $companyRepository;

        $this->permission      =    ["CPNY0", "CPNY1", "CPNY2", "CPNY3", "CPNY4"];
        $this->pAccess         =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate         =    $this->services->checkPermission($this->permission[1]);
        $this->pView           =    $this->services->checkPermission($this->permission[2]);
        $this->pEdit           =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete         =    $this->services->checkPermission($this->permission[4]);
    }

    #[Route('/add', name: 'app_company_manage', methods: ['POST'])]
    public function index(Request $request): Response
    {
        dd($this->pCreate, $this->pEdit);
        $user = $this->getUser();
        //ajax method
        $company       = $this->getUser()->getCompany();
        $isCompanyAdd  = (!$company) ? true : false;
        $company       = (!$company) ?  new Company() : $company;
        $form = $this->createForm(CompanyType::class, $company);
        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            if ($isCompanyAdd == true) { //method calling
                if (!$this->pCreate) return $this->services->no_access($this->intl->trans("Enrégistrement d'une entreprise"));
                return $this->addCompany($request, $form, $company, $user);
            }else {
                if (!$this->pEdit) return $this->services->no_access($this->intl->trans("Mise à jour d'une entreprise"));
                return $this->updateCompany($request, $form, $company, $user);
            }
        }
        return $this->services->msg_info(
            $this->intl->trans("Enregistrement d'une entreprise"),
            $this->intl->trans("La configuration de l'entreprise n'est pas requis")
        );
    }

    public function addCompany($request, $form, $company, $user): JsonResponse
    {
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $phone           =  $form->get('phone')->getData();
            $address         =  $request->request->get('adress');

            $company->setUid($this->services->idgenerate(15));
            $company->setStatus($this->services->status(3));
            $company->setPhone($phone);
            $company->setManager($user);
            $company->setAddress($address);;
            $company->setCreatedAt(new \DatetimeImmutable());
            $this->companyRepository->add($company);
            return $this->services->msg_success(
                $this->intl->trans("Ajout d'un nouveau Company"),
                $this->intl->trans("Company ajouté avec succès")
            );
        }
        else 
        {
            return $this->services->formErrorsNotification($this->validator, $this->intl, $company);
        }
        return $this->services->failedcrud($this->intl->trans("Enregistrement d'une nouvelle entreprise"));
    }
 
    public function updateCompany($request, $form, $company, $user): JsonResponse
    {
        if ($form->isSubmitted() && $form->isValid()) 
        { 
            $phone           =  $form->get('phone')->getData();
            $address         =  $request->request->get('adress');

            $sectors  = [ 'sector_one' => $sector_one,'sector_two' => $sector_two ];
            $company->setPhone($phone);
            $company->setManager($user);
            $company->setAddress($address);;
            $company->setCreatedAt(new \DatetimeImmutable());;
            $company->setUpdatedAt(new \DatetimeImmutable());
            $this->companyRepository->add($company);
            return $this->services->msg_success(
                $this->intl->trans("Modification de l'entreprise")." : ".$company->getUid(),
                $this->intl->trans("Profil entreprise modifié avec succès")
            );
        }
        else 
        {
            return $this->services->formErrorsNotification($this->validator, $this->intl, $company);
        }
        return $this->services->failedcrud($this->intl->trans("Modification d'une entreprise").' : '.$company->getUid());
    }


   

    #[Route('/{uid}/get', name: 'get_this_company', methods: ['POST'])]
    public function get_this_company(Request $request,): Response
    {
        $company = $this->getUser()->getCompany();

        $row['orderId']      = $company->getUid();
        $row['name']         = $company->getName();
        $row['email']        = $company->getEmail();
        $row['phone']        = str_replace($company->getManager->getCountry()['dial_code'], '', $company->getPhone());
        $row['ifu']          = $company->getIfu();
        $row['rccm']         = $company->getRccm();
        $row['address']      = $company->getAddress();
        return new JsonResponse([
            'data' => $row, 
            'message' => $this->intl->trans('Vos données sont chargés avec succès.')]);
    }

}
