<?php

namespace App\Controller;

use App\Entity\Status;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('{_locale}/home/transactions')]
class TransactionController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, UrlGeneratorInterface $urlGenerator, Services $services,  
    EntityManagerInterface $entityManager, TranslatorInterface $translator, uBrand $brand)
    {
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->baseUrl         = $baseUrl;
        $this->em	           = $entityManager;

        $this->permission      =    ["TRAN0", "TRAN1", "TRAN2", "TRAN3", "TRAN4"];
        $this->pAccess         =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate         =    $this->services->checkPermission($this->permission[1]);
        $this->pView           =    $this->services->checkPermission($this->permission[2]);
        $this->pUpdate         =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete         =    $this->services->checkPermission($this->permission[4]);

        $this->placeAvatar	   = "public/app/uploads/avatars/"; //profile image file path

    }
    
    #[Route('/', name: 'app_transaction_index', methods: ['GET'])]
    public function index(): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

        return $this->render('transaction/index.html.twig', [
            'controller_name' => 'TransactionController',
            'title'           => $this->intl->trans('Transactions').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans('Transactions')] 
            ],
            'brand'       => $this->brand->get(),
            'baseUrl'     => $this->baseUrl->init(),
            'pAccess'     => $this->pAccess,
        ]);
    }

    #[Route('/list', name: 'app_transaction_list', methods: ['POST'])]
    public function getTransaction(Request $request, EntityManagerInterface $manager) : Response
    {
        //Vérification du tokken
        //dd($request->request->get('_token'));
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Récupération de la liste des transactions : token invalide'));

        $data           =   [];
        $tabTransaction =   [];
        $transactions   =   [];
        $pending        =   [];
        $validated      =   [];
        $canceled       =   [];

        if (!$this->pView) {
            $transactions   =   [];
            
        }
        else{
            $transactions           =   $this->em->getRepository(Transaction::class)->findAll();
            
            $pending    =   $this->em->getRepository(Transaction::class)->findByStatus($this->em->getRepository(Status::class)->findByCode(2));
            $validated  =   $this->em->getRepository(Transaction::class)->findByStatus($this->em->getRepository(Status::class)->findByCode(6));
            $canceled   =   $this->em->getRepository(Transaction::class)->findByStatus($this->em->getRepository(Status::class)->findByCode(7));
            
            $status= $this->em->getRepository(Status::class)->findByCode(2);
            $sumAmountPending   =   $this->em->getRepository(Transaction::class)->sumAmountForTransaction($status);

            dd($sumAmountPending);
        }
        
        foreach ($transactions as $key => $transaction) {

            $tabTransaction[$key][0][0] = $transaction->getUser()->getUsetting()->getFirstName();
            $tabTransaction[$key][0][1] = $transaction->getUser()->getUsetting()->getLastName();
            $tabTransaction[$key][0][2] = $transaction->getUser()->getPhone();

            $tabTransaction[$key][1]    = $transaction->getTransactionId();
            $tabTransaction[$key][2]    = $transaction->getReference();
            $tabTransaction[$key][3]    = $transaction->getBeforeBalance();
            $tabTransaction[$key][4]    = $transaction->getAmount();
            $tabTransaction[$key][5]    = $transaction->getAfterBalance();
            $tabTransaction[$key][6][0] = $transaction->getStatus()->getCode();
            $tabTransaction[$key][6][1] = $this->intl->trans($transaction->getStatus()->getName());
            $tabTransaction[$key][6][2] = $this->intl->trans($transaction->getStatus()->getDescription());
            $tabTransaction[$key][7]    = $transaction->getCreatedAt()->format("c");
            $tabTransaction[$key][8]    = $transaction->getUpdatedAt()?$transaction->getUpdatedAt()->format("c"):$this->intl->trans('Pas de modification');

        }
       
        $this->services->addLog($this->intl->trans('Lecture de la liste des transactions'));
        $data = [
                    "data"      =>   $tabTransaction,
                    "all"       =>   count($transactions),         
                    "pending"   =>   count($pending),      
                    "validated" =>   count($validated),  
                    "canceled"  =>   count($canceled)  
                ];
        return new JsonResponse($data);
    }

    public function statisticsData()
    {
        // $all     = $this->transactionRepository->countAllTransactions()[0][1];
        // $pending = $this->transactionRepository->countAllTransactionsByStatus(0)[0][1];
        // $validated  = $this->transactionRepository->countAllTransactionsByStatus(1)[0][1];
        // $canceled = $this->transactionRepository->countAllTransactionsByStatus(2)[0][1];
        // $rejected = $this->transactionRepository->countAllTransactionsByStatus(3)[0][1];
        // $deleted = $this->transactionRepository->countAllTransactionsByStatus(4)[0][1];

        return [
            'all'          => [],
            'pending'      => 1,
            'validated'    => 1,
            'canceled'     => 1,
            'rejected'     => 1,
            'deleted'      => 1,
        ];
    }
}
