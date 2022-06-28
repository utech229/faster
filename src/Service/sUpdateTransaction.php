<?php

namespace App\Service;

use App\Service\BaseUrl;
use App\Service\Litesms;
use App\Service\Services;
use App\Service\sFedapay;
use App\Entity\Transaction;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransactionRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class sUpdateTransaction extends AbstractController
{
    protected $brand;
   
	public function __construct(TranslatorInterface $intl, EntityManagerInterface $entityManager, 
    TransactionRepository $transactionRepository, sFedaPay $sFedapay, StatusRepository $statusRepository,
    UserRepository $userRepository, Services $services,RoleRepository $roleRepository)
	{
       $this->intl                  = $intl;
       $this->em                    = $entityManager;
       $this->transactionRepository = $transactionRepository;
       $this->userRepository        = $userRepository;
       $this->statusRepository        = $statusRepository;
       $this->roleRepository        = $roleRepository;
       $this->services              = $services;

       $this->comptes = [
        ['Owner' =>'','Operator'=>'','Phone'=>'','TransactionId'=>'','Country'=>'', 'Status'=>''],
        ['Banque'=>'','Country'=>'','NAccount'=>'','Swift'=>'','DocID'=>'','DocRIB'=>''],
        ['Owner' =>'','NBIN'=>'','CVV2'=>'','NAccount'=>'']
    ];
    }

    public function numberValidationUpdater($checkTransaction, $status)
    {
        $user   = $checkTransaction->getUser();
        $mode =  "mobile" ;//: "card";

        $this->comptes = $user->getPaymentAccount();
        $this->comptes[0]["Reference"] = $checkTransaction->getReference();
        
        switch ($status) {
            case 'approved':
                //update user data
                if ($mode == "mobile")
                {
                    $this->comptes[0]["Owner"]     = $this->comptes[0]["newOwner"];
                    $this->comptes[0]["Operator"]  = $this->comptes[0]["newOperator"];
                    $this->comptes[0]["Phone"]     = $this->comptes[0]["newPhone"];
                    $this->comptes[0]["Country"]   = $this->comptes[0]["newCountry"];
                    $this->comptes[0]["TransactionId"] = $checkTransaction->getTransactionId();
                    $this->comptes[0]["Status"]        = $status;
                    $this->comptes[0]["Method"]        = "Mobile Money";

                    $checkTransaction->setStatus($this->services->status(6));
                    $checkTransaction->setUpdatedAt(new \DatetimeImmutable());
                    $this->transactionRepository->add($checkTransaction);
                    $this->services->addLog($this->intl->trans("Mise à jour de la transaction").' : '.$checkTransaction->getReference());


                    $message = $this->intl->trans("✅ Félicitation").' ,'. $user->getUsetting()->getFirstname().', '.
                    $this->intl->trans("Votre numéro de téléphone est bien validé!"); 
                    $flashType = "success";
                }
                else 
                {
                    $this->comptes[0]["Method"] = "Carte Bancaire";
                    $message  = $this->intl->trans("✅ Félicitation").' ,'. $user->getUsetting()->getFirstname().', '.
                    $this->intl->trans("Transaction approuvée, mais le numéro n'est pas validé !"); 
                    $flashType = "warning";
                }
                break;
            case 'pending':
                $message = $user->getUsetting()->getFirstname().' '.$this->intl->trans("Votre transaction est en attente de validation, veillez procéder à sa validation de cette opération pour valider le compte de paiement");
                $flashType = "warning";
                break;
            case 'canceled':
                $message = $user->getUsetting()->getFirstname().$this->intl->trans("vous avez annulé cette transaction. 
                Veillez procéder à une validation de cette opération pour valider le compte de paiement");
                $flashType = "error";
                break;
            default:
                $message = $user->getUsetting()->getFirstname().','.
                $this->intl->trans("Cette transaction est compromise, veuillez réessayer");
                $flashType = "info";
                break;
        }

        $user->setPaymentAccount($this->comptes);
        $this->em->persist($user);
        $this->em->flush();
        $this->addFlash($flashType, $message);
        return $this->redirectToRoute("app_payment_index");
    }

    
}
