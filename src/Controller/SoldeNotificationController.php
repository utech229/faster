<?php

namespace App\Controller;

use App\Service\uBrand;
use App\Entity\SoldeNotification;
use App\Service\BaseUrl;
use App\Form\SoldeNotificationType;
use App\Service\Services;
use App\Service\AddEntity;
use App\Service\BrickPhone;
use App\Service\sUpgradeForm;
use App\Repository\SoldeNotificationRepository;
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
#[Route('/{_locale}/home/soldenotification')]
class SoldeNotificationController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, UrlGeneratorInterface $urlGenerator, Services $services, BrickPhone $brickPhone,  
    EntityManagerInterface $entityManager, TranslatorInterface $translator, SoldeNotificationRepository $SoldeNotificationRepository, ValidatorInterface $validator,
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
        $this->SoldeNotificationRepository = $SoldeNotificationRepository;

        $this->permission      =    ["CPNY0", "CPNY1", "CPNY2", "CPNY3", "CPNY4"];
        $this->pAccess         =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate         =    $this->services->checkPermission($this->permission[1]);
        $this->pView           =    $this->services->checkPermission($this->permission[2]);
        $this->pEdit           =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete         =    $this->services->checkPermission($this->permission[4]);
    }

    #[Route('/add', name: 'app_SoldeNotification_manage', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        //ajax method
        $SoldeNotification       = $this->getUser()->getSoldeNotification();
        $isSoldeNotificationAdd  = (!$SoldeNotification) ? true : false;
        $SoldeNotification       = (!$SoldeNotification) ?  new SoldeNotification() : $SoldeNotification;
        $form = $this->createForm(SoldeNotificationType::class, $SoldeNotification);
        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            if ($isSoldeNotificationAdd == true) { //method calling
                //if (!$this->pCreate) return $this->services->no_access($this->intl->trans("Enrégistrement d'une entreprise"));
                return $this->addSoldeNotification($request, $form, $SoldeNotification, $user);
            }else {
                //if (!$this->pEdit) return $this->services->no_access($this->intl->trans("Mise à jour d'une entreprise"));
                return $this->updateSoldeNotification($request, $form, $SoldeNotification, $user);
            }
        }
        return $this->services->msg_info(
            $this->intl->trans("Enregistrement d'une entreprise"),
            $this->intl->trans("La configuration de l'entreprise n'est pas requis")
        );
    }

    public function addSoldeNotification($request, $form, $SoldeNotification, $user): JsonResponse
    {
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $phone           =  $form->get('phone')->getData();
            $address         =  $request->request->get('adress');

            $SoldeNotification->setUid($this->services->idgenerate(15));
            $SoldeNotification->setStatus($this->services->status(3));
            $SoldeNotification->setPhone($phone);
            $SoldeNotification->setManager($user);
            $SoldeNotification->setAddress($address);;
            $SoldeNotification->setCreatedAt(new \DatetimeImmutable());
            $this->SoldeNotificationRepository->add($SoldeNotification);
            return $this->services->msg_success(
                $this->intl->trans("Ajout d'un nouveau SoldeNotification"),
                $this->intl->trans("SoldeNotification ajouté avec succès"), 
                ['name' => $SoldeNotification->getName(), 'phone' => $SoldeNotification->getPhone(), 'email' => $SoldeNotification->getEmail(), 
                'ifu' => $SoldeNotification->getIfu(), 'rccm' => $SoldeNotification->getRccm(), 'address' => $SoldeNotification->getAddress(), 'isAdd' => true]
            );
        }
        else 
        {
            return $this->services->formErrorsNotification($this->validator, $this->intl, $SoldeNotification);
        }
        return $this->services->failedcrud($this->intl->trans("Enregistrement d'une nouvelle entreprise"));
    }
 
    public function updateSoldeNotification($request, $form, $SoldeNotification, $user): JsonResponse
    {
        if ($form->isSubmitted() && $form->isValid()) 
        { 
            $phone           =  $form->get('phone')->getData();
            $address         =  $request->request->get('adress');
            $SoldeNotification->setPhone($phone);
            $SoldeNotification->setManager($user);
            $SoldeNotification->setAddress($address);;
            $SoldeNotification->setCreatedAt(new \DatetimeImmutable());
            $SoldeNotification->setUpdatedAt(new \DatetimeImmutable());
            $this->SoldeNotificationRepository->add($SoldeNotification);
            return $this->services->msg_success(
                $this->intl->trans("Modification de l'entreprise")." : ".$SoldeNotification->getUid(),
                $this->intl->trans("Profil entreprise modifié avec succès"), 
                ['name' => $SoldeNotification->getName(), 'phone' => $SoldeNotification->getPhone(), 'email' => $SoldeNotification->getEmail(), 
                'ifu' => $SoldeNotification->getIfu(), 'rccm' => $SoldeNotification->getRccm(), 'address' => $SoldeNotification->getAddress(), 'isAdd' => false]
            );
        }
        else 
        {
            return $this->services->formErrorsNotification($this->validator, $this->intl, $SoldeNotification);
        }
        return $this->services->failedcrud($this->intl->trans("Modification d'une entreprise").' : '.$SoldeNotification->getUid());
    }


   

    #[Route('/{uid}/get', name: 'get_this_SoldeNotification', methods: ['POST'])]
    public function get_this_SoldeNotification(Request $request,): Response
    {
        $SoldeNotification = $this->getUser()->getSoldeNotification();

        $row['orderId']      = $SoldeNotification->getUid();
        $row['name']         = $SoldeNotification->getName();
        $row['email']        = $SoldeNotification->getEmail();
        $row['phone']        = str_replace($SoldeNotification->getManager->getCountry()['dial_code'], '', $SoldeNotification->getPhone());
        $row['ifu']          = $SoldeNotification->getIfu();
        $row['rccm']         = $SoldeNotification->getRccm();
        $row['address']      = $SoldeNotification->getAddress();
        return new JsonResponse([
            'data' => $row, 
            'message' => $this->intl->trans('Vos données sont chargés avec succès.')]);
    }

}
