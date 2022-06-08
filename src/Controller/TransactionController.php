<?php

namespace App\Controller;

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
    EntityManagerInterface $entityManager, TranslatorInterface $translator, UserRepository $userRepository, 
    TransactionRepository $transactionRepository, uBrand $brand, RoleRepository $roleRepository
    , StatusRepository $statusRepository)
    {
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->baseUrl         = $baseUrl;
        $this->em	           = $entityManager;
        $this->userRepository  = $userRepository;
        $this->transactionRepository  = $transactionRepository;
        $this->roleRepository    = $roleRepository;
        $this->statusRepository  = $statusRepository;

        $this->permission      =    ["TRAN0", "TRAN1", "TRAN2", "TRAN3", "TRAN4"];
        $this->pAccess         =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate         =    $this->services->checkPermission($this->permission[1]);
        $this->pView           =    $this->services->checkPermission($this->permission[2]);
        $this->pUpdate         =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete         =    $this->services->checkPermission($this->permission[4]);

        $this->placeAvatar	= "public/app/uploads/avatars/"; //profile image file path
    }
    
    #[Route('/', name: 'app_transaction_index', methods: ['GET'])]
    public function index(): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

        $statistics = $this->statisticsData();
        return $this->render('transaction/index.html.twig', [
            'controller_name' => 'TransactionController',
            'role'            => $this->roleRepository->findAll(),
            'title'           => $this->intl->trans('Transactions').' - '. $this->brand->index()['name'],
            'pageTitle'       => [
                'one'   => $this->intl->trans('Transactions'),
                'two'   => $this->intl->trans('Mes Transactions'),
                'none'  => $this->intl->trans('Gestion transaction'),
            ],
            'brand'       => $this->brand->index(),
            'baseUrl'     => $this->baseUrl->init(),
            'transactions'=> $this->transactionRepository->findAll(),
            'pAccess'     => $this->pCreate,
            'pCreate'     => $this->pCreate,
            'pEdit'       => $this->pUpdate,
            'pDelete'     => $this->pDelete,
            'stats'       => $statistics,
        ]);
    }

    #[Route('/list', name: 'app_transaction_list', methods: ['POST', 'GET'])]
    public function getUsers(Request $request, EntityManagerInterface $manager) : Response
    {
        //Vérification du tokken
        //dd($request->request->get('_token'));
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Récupération de la liste des transactions : token invalide'));

        $data = [];
        $transactions = (!$this->pView) ? [] : $this->getTransactionByRoles();
        foreach ($transactions  as $transaction) 
		{          
            $row                 = array();

            $transactionCreator  = $transaction->getUser();
            $row['orderId']      = $transaction->getReference();
            $row['user']         =  ['name'  => $transactionCreator->getFirstName().' '.$transactionCreator->getLastName(),
                                        'phone' => $transaction->getPhone(),
                                        'photo' => $transactionCreator->getProfilePhoto()
                                    ];
            $row['transactionId']   = $transaction->getTransactionId();
            $row['reference']       = $transaction->getReference();
            $row['method']          = strtolower($transaction->getMethod());
            $row['agregator']       = $transaction->getAgregator();
            $row['canal']           = $transaction->getCanal();
            $row['email']           = $transaction->getEmail();
            $row['amount']          = $transaction->getAmount();
            $row['country']         = $transaction->getUser()->getCountry()['name'];
            $row['status']          = $transaction->getStatus();
            $row['updatedAt']       = ($transaction->getUpdatedAt()) ? $transaction->getUpdatedAt()->format("c") : null;
            $row['createdAt']       = $transaction->getCreatedAt()->format("c");
            $data []                = $row;
		}
        $this->services->addLog($this->intl->trans('Lecture de la liste des transactions'));
        $output = array("data" => $data);
        return new JsonResponse($output);
    }

    public function getTransactionByRoles() 
    {
        $cuser     = $this->getUser();
        $userRole  = $cuser->getRole();
        $roleLevel = $userRole->getLevel();
        switch ($roleLevel) {
            case 1 :
                $data = [];
                break;
            case 2 :
                $data = [];
                break;
            case 3 :
                $data = $this->transactionRepository->findAll();//$this->transactionRepository->findTransactionByCountryCode($cuser->getCountry()['code']);
                break;
            case 4 :
                $data = $this->transactionRepository->findAll();//$this->transactionRepository->findTransactionByCountryCode($cuser->getCountry()['code']);
                break;
            case 5 :
                $data = $this->transactionRepository->findAll();
                break;
            case 6 :
                $data = $this->transactionRepository->findAll();
                break;
            default:
                $data = [];
                break;
        }
        return $data;
    }

    public function statisticsData()
    {
        $all     = $this->transactionRepository->countAllTransactions()[0][1];
        $pending = $this->transactionRepository->countAllTransactionsByStatus(0)[0][1];
        $validated  = $this->transactionRepository->countAllTransactionsByStatus(1)[0][1];
        $canceled = $this->transactionRepository->countAllTransactionsByStatus(2)[0][1];
        $rejected = $this->transactionRepository->countAllTransactionsByStatus(3)[0][1];
        $deleted = $this->transactionRepository->countAllTransactionsByStatus(4)[0][1];

        return [
            'all'          => $all,
            'pending'      => $pending,
            'validated'    => $validated,
            'canceled'     => $canceled,
            'rejected'     => $rejected,
            'deleted'      => $deleted,
        ];
    }
}
