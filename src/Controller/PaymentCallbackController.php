<?php

namespace App\Controller;

use App\Service\sUpay;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\sFedapay;
use App\Service\sTransaction;
use App\Service\sUpdateTransaction;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('pay/callback')]
class PaymentCallbackController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, TranslatorInterface $intl, uBrand $brand,
    UrlGeneratorInterface $urlGenerator, Services $services,
    EntityManagerInterface $entityManager, sFedaPay $sFedapay, sTransaction $sTransaction,
    sUpdateTransaction $sUpdateTransaction, TransactionRepository $transactionRepository,
    StatusRepository $statusRepository, sUpay $sUpay)
    {
        $this->baseUrl       = $baseUrl->init();
        $this->urlGenerator  = $urlGenerator;
        $this->services      = $services;
        $this->em	         = $entityManager;
        $this->intl          = $intl;
        $this->brand         = $brand;
        $this->sUpay          = $sUpay;
        $this->sTransaction          = $sTransaction;
        $this->sUpdateTransaction    = $sUpdateTransaction;
        $this->transactionRepository = $transactionRepository;
        $this->statusRepository      = $statusRepository;
        $this->services = $services;
    }

    #[Route('/numverif', name: 'app_numverif')]
    public function app_numverif(Request $request): Response
    {
        
            $checkTransaction   = $this->transactionRepository->findOneByTransactionId($request->query->get('id'));
            if($checkTransaction)
            { 
                $user        = $checkTransaction->getUser();
                //$transaction = $this->sUpay->status($request->query->get('id'));
                //dd($transaction);
                if(!$user) return $this->redirectToRoute("app_home");
                switch ($checkTransaction->getStatus()->getCode()) {
                    case 6:
                        $message = $this->intl->trans("✅Félicitation").' ,'.$user->getUsetting()->getFirstname().
                        $this->intl->trans(" votre mise à jour à été éffectué avec succès, profitez en. Merci"); 
                        $this->addFlash('success', $message);
                        return $this->redirectToRoute("app_payment_index");
                        break;
                    case 7:
                        $message = $user->getUsetting()->getFirstname().
                        $this->intl->trans("vous avez annulé cette transaction. Veillez procéder à une validation de cette opération pour mieux profiter de nos services"); 
                        $this->addFlash('error', $message);
                        return $this->redirectToRoute("app_payment_index");
                        break;
                    case 2:
                        return $this->objectUpdater($checkTransaction, $request->query->get('status'));
                        break;
                    default:
                        $message = $user->getUsetting()->getFirstname().','.
                        $this->intl->trans("cette transaction est compromise, veuillez réessayer"); 
                        $this->addFlash('info', $message);
                        return $this->redirectToRoute("app_home");
                        break;
                }
               
            }else {
                $message = $this->intl->trans("Tentative frauduleuse de transaction, veuillez reprendre la procéduire."); 
                $this->addFlash('info', $message);
                return $this->redirectToRoute("app_home");
            }
       
    }

    public function objectUpdater($checkTransaction, $status)
    {
        return $this->sUpdateTransaction->numberValidationUpdater($checkTransaction, $status);
    }

    

    







}

