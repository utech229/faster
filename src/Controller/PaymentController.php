<?php

namespace App\Controller;

use App\Service\Brand;
use App\Entity\Payment;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Form\PaymentType;
use App\Service\Services;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('{_locale}/home/payment')]
class PaymentController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, UrlGeneratorInterface $urlGenerator, Services $services, 
    EntityManagerInterface $entityManager, TranslatorInterface $translator, PaymentRepository $paymentRepository,
    RoleRepository $roleRepository,UserRepository $userRepository, uBrand $brand)
    {
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->baseUrl         = $baseUrl;
        $this->paymentRepository  = $paymentRepository;
        $this->roleRepository     = $roleRepository;
        $this->userRepository     = $userRepository;
        $this->em	              = $entityManager;

        $this->permission      =    ["PAY0", "PAY1", "PAY2", "PAY3", "PAY4"];
        $this->pAccess         =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate         =    $this->services->checkPermission($this->permission[1]);
        $this->pView           =    $this->services->checkPermission($this->permission[2]);
        $this->pUpdate         =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete         =    $this->services->checkPermission($this->permission[4]);
    }

    #[Route('', name: 'app_payment_index', methods: ['GET'])]
    #[Route('/new', name: 'app_payment_add', methods: ['POST'])]
    #[Route('/{uid}/edit', name: 'app_payment_edit', methods: ['POST'])]
    public function index(Request $request,PaymentRepository $paymentRepository, Payment $payment = null, 
    ValidatorInterface $validator): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

         /*----------MANAGE user CRU BEGIN -----------*/
        //define if method is user add 
        $isAdd        = (!$payment) ? true : false;
        $payment      = (!$payment) ? new Payment() : $payment;
       
        $form = $this->createForm(PaymentType::class, $payment);
        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            if ($isAdd == true) { //method calling
                if (!$this->pCreate) return $this->services->ajax_ressources_no_access($this->intl->trans("Demande de paiement"));
                return $this->addPayment($request, $form, $payment);
            }else {
                if (!$this->pUpdate)   return $this->services->ajax_ressources_no_access($this->intl->trans("Traitement de demande de paiement"));
                return $this->updatePayment($request, $form, $payment);
            }
        }

        $this->services->addLog($this->intl->trans('Accès au menu commissions & paiements'));
        return $this->render('payment/index.html.twig', [
            'controller_name' => 'ReferralController',
            'role'            => $this->roleRepository->findAll(),
            'title'           => $this->intl->trans('Commissions & paiements').' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans("Gestion paiements")],
                [$this->intl->trans("Paiements")],
            ],
            'brand'       => $this->brand->get(),
            'baseUrl'     => $this->baseUrl->init(),
            'paymentform' => $form->createView(),
            'pCreate'	  =>	$this->pCreate,
            'pEdit'		  =>	$this->pUpdate,
            'pDelete'	  =>	$this->pDelete,
        ]);
    }

    //Add function
    public function addPayment($request, $form, $payment): Response
    {
        if(($form->isSubmitted() && $form->isValid()))
        {
            $user = $this->getUser();
            $amount =  $form->get('amount')->getData();

            if($user->getBalance() < $amount) 
            return $this->services->msg_info(
                $this->intl->trans("Demande de retrait"),
                $this->intl->trans("Vous ne disposez pas d'assez de fond pour retirer").'10000'.$user->getUsetting()->getCurrency()['code'].' '.$this->intl->trans("de votre compte")
            );

            if($user->getBalance() < 10000) 
            return $this->services->msg_info(
                $this->intl->trans("Demande de retrait"),
                $this->intl->trans("Vous devez avoir au moins ").'10000'.$this->intl->trans(" avant de procéder à un retrait de fond")
            );

            //data setting
            $payment->setUid($this->services->idgenerate(15));
            $payment->setReference("Mob_".$this->services->numeric_generate(10));
            $payment->setCode($this->services->idgenerate(6));
            $payment->setUser($user);
            $payment->setStatus(0);
            $payment->setReceptionPhone($user->getPaymentAccount()[0]['Phone']);
            $payment->setMethod('Mobile Money');
            $payment->setLastBalance($user->getBalance());
            $payment->setCreatedAt(new \DatetimeImmutable());
            $this->paymentRepository->add($payment);

            $user->setBalance($user->getBalance() - $amount);
            $this->userRepository->add($user);

            return $this->services->msg_success(
                $this->intl->trans("Création d'une demande de payment"),
                $this->intl->trans("Demande de paiement crée avec succès")
            );
        }
        else 
        {
            return $this->services->formErrorsNotification($this->validator, $this->intl, $payment);
        }
        return $this->services->failedcrud($this->intl->trans("Création d'une demande de paiement"));
    }
 
    //update function
    public function updatePayment($request, $form, $payment): Response
    {
        if ($form->isSubmitted() && $form->isValid()) {
        
           
        }
        else 
        {
            //return $this->services->invalidForm($form, $this->intl);
            return $this->services->formErrorsNotification($this->validator, $this->intl, $user);
        }
        return $this->services->failedcrud($this->intl->trans("Modification de l'utilisateur : " .$request->request->get('user_name')));
    }


    #[Route('/{uid}/get', name: 'app_payment_get', methods: ['POST'])]
    public function getOne(Request $request,Payment $payment): JsonResponse
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
        return $this->services->ajax_ressources_no_access($this->intl->trans("Récupération d'une permission"));

        $cuser = $payment->getUser();
        $row['uid']             = $payment->getUid();
        $row['reference']       = $payment->getReference();
        $row['transactionId']   = $payment->getTransactionId();
        $row['user']            = [$cuser->getUsetting()->getFirstName().' '.$cuser->getUsetting()->getLastname(), $cuser->getEmail()];
        $row['owner']           = $cuser->getPaymentAccount()[0]['Owner'];
        $row['operator']        = $cuser->getPaymentAccount()[0]['Operator'];
        $row['treatedby']       = ($payment->getValidator()) ? $payment->getValidator()->getEmail() : '...';
        $row['reference']       = $payment->getReference();
        $row['type']            = ($cuser->getPaymentAccount()[0]['Operator'] == 'MTN BENIN') ? 1 : 0;//($payment->IsType()) ? $payment->IsType() : 0;
        $row['method']          = $payment->getMethod();
        $row['amount']          = $payment->getAmount();
        $row['status']          = $payment->getStatus();
        $row['updatedAt']       = ($payment->getUpdatedAt()) ? $payment->getUpdatedAt()->format("c") : null;
        $row['createdAt']       = $payment->getCreatedAt()->format("c");
        $row['phone']           = $payment->getReceptionPhone();
        return new JsonResponse(['data' => $row]);
    }

    #[Route('/list', name: 'app_payment_list', methods: ['POST', 'GET'])]
    public function getPayment(Request $request, EntityManagerInterface $manager) : Response
    {
        $user = $this->getUser();
        //Vérification du tokken
		if (!$this->isCsrfTokenValid($user->getUid(), $request->request->get('_token')))
            return $this->services->invalid_token_ajax_list($this->intl->trans('Récupération de la liste des payments : token invalide'));

        $data = [];
        $payments = $this->getPaymentsByRoles($user); //$this->paymentRepository->findAll(); //(!$this->pView) ? [] : $this->getpaymentByRoles();
        foreach ($payments  as $payment) 
		{          
            $row                 = array();
            $paymentCreator      = $payment->getUser();

            $row['orderId']      = $payment->getUid();
            $row['user']         =  ['name'  => $paymentCreator->getUsetting()->getFirstname().' '.$paymentCreator->getUsetting()->getLastname(),
                                        'phone' => $paymentCreator->getPhone(),
                                        'photo' => $paymentCreator->getProfilePhoto()
                                    ];
            $row['paymentId']       = $payment->getUid();
            $row['reference']       = $payment->getReference();
            $row['transactionId']   = $payment->getTransactionId();
            $row['treatedby']       = ($payment->getValidator()) ? $payment->getValidator()->getEmail() : '';
            $row['reference']       = $payment->getReference();
            $row['method']          = strtolower($payment->getMethod());
            $row['amount']          = $payment->getAmount();
            $row['status']          = $payment->getStatus();
            $row['updatedAt']       = ($payment->getUpdatedAt()) ? $payment->getUpdatedAt()->format("c") : null;
            $row['createdAt']       = $payment->getCreatedAt()->format("c");
            $row['action']          = [$payment->getUid(), $payment->getStatus()];
            $data []                = $row;
		}
        $this->services->addLog($this->intl->trans('Lecture de la liste des payments'));
        $output = array("data" => $data);
        return new JsonResponse($output);
    }

    #[Route('/{uid}/delete', name: 'app_payment_delete', methods: ['POST'])]
    public function delete(Request $request, Payment $payment): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
            return $this->services->ajax_ressources_no_access($this->intl->trans("Suppression d'une demande de paiement").': '.$payment->getCode());
        if ($payment->getStatus() != 0) 
        return $this->services->ajax_error_crud(
            $this->intl->trans("Suppression de la demande de paiement"),$this->intl->trans("Vous ne pouvez pas supprimer une demande de paiement déjà traité"));

        if($this->paymentRepository->remove($payment));
        return $this->services->msg_success(
            $this->intl->trans("Suppression de la demande de paiement"), $this->intl->trans("Demande supprimé avec succès")
        );
    }

    #[Route('/{uid}/reject', name: 'app_payment_reject', methods: ['POST'])]
    public function reject(Request $request, Payment $payment): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')) OR !$this->pUpdate) 
        return $this->services->ajax_ressources_no_access($this->intl->trans("Rejet de la demande").' : '.$payment->getReference());
            
        if ($payment->getStatus() != 0) 
        return $this->services->ajax_error_crud(
            $this->intl->trans("Rejet de la demande de paiement"),$this->intl->trans("Vous ne pouvez pas rejeter une demande de paiement déjà traité"));

        //update payment
        $payment->setStatus(2)->setUpdatedAt(new \DatetimeImmutable())->setValidator($this->getUser());
        $creator = $payment->getUser();
        //update payment creator balance
        $creator->setBalance($creator->getBalance() + $payment->getAmount());
        return $this->services->msg_success(
            $this->intl->trans("Rejet de la demande de paiement"), $this->intl->trans("Demande rejeté avec succès")
        );
    }

    #[Route('/{uid}/validate', name: 'app_payment_validate', methods: ['POST'])]
    public function validate(Request $request, Payment $payment): Response
    {
        $user = $this->getUser();
        if (!$this->isCsrfTokenValid($user->getUid(), $request->request->get('_token')) OR !$this->pUpdate) 
        return $this->services->ajax_ressources_no_access($this->intl->trans("Validation de la demande").' : '.$payment->getReference());

        if ($request->request->get('type') == 0 && !$request->request->get('trid'))
        return $this->services->msg_info(
        $this->intl->trans("Validation de la demande de paiement"),$this->intl->trans("Veuillez renseigner l'ID TRANSACTION"));

        if ($payment->getStatus() != 0) 
        return $this->services->ajax_error_crud(
        $this->intl->trans("Validation de la demande de paiement"),$this->intl->trans("Vous ne pouvez pas valider une demande de paiement déjà traité"));

        if ($request->request->get('type') == 0) {
            $payment->setStatus(1)->setUpdatedAt(new \DatetimeImmutable())->setValidator($user)->setTransactionId($request->request->get('trid'))
            ->setType($request->request->get('type'))->setObservation("Payment manuellement approuvé et mise à jour");
        }else {
            $receiver = [
                "paymentType"   => $request->request->get('type'),
                "validator"     => $user,
                "payment"       => $payment,
            ];
            return $this->sFedapay->automaticPay($receiver);
        }
        return $this->services->msg_success(
        $this->intl->trans("Validation de la demande de paiement"), $this->intl->trans("Demande validé avec succès"));
    }

    

    public function getPaymentsByRoles($user) 
    {
        $userRole  = $user->getRole();
        $roleLevel = $userRole->getLevel();
        switch ($roleLevel) {
            case 1 :
                $data = $user->getPayments();
                break;
            case 2 :
                $data = $user->getPayments();
                break;
            case 3 :
                $data = $user->getPayments();
                break;
            case 4 :
                $data = $user->getPayments();
                break;
            case 5 :
                $data = $user->getPayments();
                break;
            case 6 :
                $data = $this->paymentRepository->findAll();
                break;
            default:
                $data = $user->getPayments();
                break;
        }
        return $data;
    }
}
