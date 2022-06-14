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

        $this->permission      =    ["PFIL0", "PFIL1", "PFIL2", "PFIL3", "PFIL4"];
        $this->pAccess         =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate         =    $this->services->checkPermission($this->permission[1]);
        $this->pView           =    $this->services->checkPermission($this->permission[2]);
        $this->pEdit           =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete         =    $this->services->checkPermission($this->permission[4]);
    }

    #[Route('/add', name: 'app_notification_manage', methods: ['POST'])]
    #[Route('/{uid}/update', name: 'app_notification_update', methods: ['POST'])]
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
                if (!$this->pCreate) return $this->services->no_access($this->intl->trans("Enrégistrement d'un solde d'alerte"));
                return $this->addSoldeNotification($request, $form, $SoldeNotification, $user);
            }else {
                if (!$this->pEdit) return $this->services->no_access($this->intl->trans("Mise à jour d'un solde d'alerte"));
                return $this->updateSoldeNotification($request, $form, $SoldeNotification, $user);
            }
        }
        return $this->services->msg_info(
            $this->intl->trans("Enregistrement d'un solde d'alerte"),
            $this->intl->trans("La configuration du un solde d'alerte n'est pas requis")
        );
    }

    public function addSoldeNotification($request, $form, $SoldeNotification, $user): JsonResponse
    {
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $SoldeNotification->setUid($this->services->idgenerate(15));
            $SoldeNotification->setUser($user);
            $SoldeNotification->setStatus($this->services->status(3));
            $SoldeNotification->setCreatedAt(new \DatetimeImmutable());
            $this->SoldeNotificationRepository->add($SoldeNotification);
            return $this->services->msg_success(
                $this->intl->trans("Création de solde de notification"),
                $this->intl->trans("Solde de notification ajouté avec succès"), 
                ['amount' => $SoldeNotification->getMinSolde(),'email1' => $SoldeNotification->getEmail1(),  
                'email2' => $SoldeNotification->getEmail2(), 'email3' => $SoldeNotification->getEmail3(), 'isAdd' => true]
            );
        }
        else 
        {
            return $this->services->formErrorsNotification($this->validator, $this->intl, $SoldeNotification);
        }
        return $this->services->failedcrud($this->intl->trans("Enregistrement du solde de notification"));
    }
 
    public function updateSoldeNotification($request, $form, $SoldeNotification, $user): JsonResponse
    {
        if ($form->isSubmitted() && $form->isValid()) 
        { 
            $SoldeNotification->setUpdatedAt(new \DatetimeImmutable());
            $this->SoldeNotificationRepository->add($SoldeNotification);
            return $this->services->msg_success(
                $this->intl->trans("Modification du solde de notification"),
                $this->intl->trans("Solde de notification  modifié avec succès"), 
                ['amount' => $SoldeNotification->getMinSolde(),'email1' => $SoldeNotification->getEmail1(),  
                'email2' => $SoldeNotification->getEmail2(), 'email3' => $SoldeNotification->getEmail3(), 'isAdd' => false]
            );
        }
        else 
        {
            return $this->services->formErrorsNotification($this->validator, $this->intl, $SoldeNotification);
        }
        return $this->services->failedcrud($this->intl->trans("Modification du solde de notification"));
    }


   

    #[Route('get', name: 'app_get_this_soldeNotification', methods: ['POST'])]
    public function get_this_SoldeNotification(Request $request,): Response
    {
        $SoldeNotification = $this->getUser()->getSoldeNotification();
        if ($SoldeNotification) {
            $row['orderId']       = $SoldeNotification->getUid();
            $row['amount']        = $SoldeNotification->getMinSolde();
            $row['email1']        = $SoldeNotification->getEmail1();
            $row['email2']        = $SoldeNotification->getEmail2();
            $row['email3']        = $SoldeNotification->getEmail3();
        }
        $row['is']            = ($SoldeNotification) ? true : false;
        return new JsonResponse([
            'data' => $row, 
            'message' => $this->intl->trans('Vos données sont chargés avec succès.')]);
    }

}
