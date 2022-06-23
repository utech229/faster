<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Brand;
use App\Form\User1Type;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\BrickPhone;
use App\Service\sAgregatorRouter;
use App\Repository\UserRepository;
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
#[Route('/{_locale}/home/user/payment_methods')]
class PaymentMethodController extends AbstractController
{
    private $validateAmount = 1;

    public function __construct(Services $services, EntityManagerInterface $entityManager, TranslatorInterface $translator,
    UrlGeneratorInterface $urlGenerator, uBrand $brand, ValidatorInterface $validator, UserRepository $userRepository,
    BrickPhone $brickPhone, BaseUrl $baseUrl, /*sAgregatorRouter $sAgregatorRouter*/){
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->userRepository  = $userRepository;
        $this->validator       = $validator;
        $this->brickPhone      = $brickPhone;
        $this->baseUrl         = $baseUrl;
        //$this->sAgregatorRouter  = $sAgregatorRouter;

        $this->permission = [
            "MET0", "MET1",  "MET2", "MET3", "MET4"
        ];
		$this->pAccess  =	$this->services->checkPermission($this->permission[0]);
		$this->pCreate  =	$this->services->checkPermission($this->permission[1]);
		$this->pList    =	$this->services->checkPermission($this->permission[2]);
		$this->pEdit    =	$this->services->checkPermission($this->permission[3]);
		$this->pDelete  =	$this->services->checkPermission($this->permission[4]);

        $this->comptes = [
			['Owner' =>'','Operator'=>'','Phone'=>'','TransactionId'=>'','Country'=>'', 'Status'=>''],
			['Banque'=>'','Country'=>'','NAccount'=>'','Swift'=>'','DocID'=>'','DocRIB'=>''],
			['Owner' =>'','NBIN'=>'','CVV2'=>'','NAccount'=>'']
		];
	}

    #[Route('/payment/mobile', name: 'app_mobile_paiement')]
	public function payment_mobile(Request $request, sAgregatorRouter $sAgregatorRouter, BrickPhone $brickPhone): JsonResponse
	{
		if (!$this->pCreate) return $this->services->ajax_ressources_no_access($this->intl->trans('Mise à jour de la méthode de paiement mobile'));
		//Vérification du tokken
		$token		=	$request->request->get("_token");
		if(!$this->isCsrfTokenValid($this->getUser()->getUid(), $token))
        return $this->services->ajax_ressources_no_access($this->intl->trans("Mise à jour de la méthode de paiement mobile"));

		//user entity
		$user = $this->getUser();
		if($user->getPaymentAccount() != []) $this->comptes = $user->getPaymentAccount();

        $phone     = $request->request->get("phone");
        $ownerName = $request->request->get("owner_name");
        $operator  = $request->request->get("operator");

		// si l'utilisateur change de n° de téléphone, nous vérifions si le n° est opérationnel
		if($this->comptes[0]["Phone"] != $phone || ( $this->comptes[0]["Phone"] == $phone && $this->comptes[0]["Status"] != "approved"))
        {
			$this->comptes[0]["newOwner"]	    =	$ownerName;
			$this->comptes[0]["newOperator"]	=	$operator;
			$this->comptes[0]["newPhone"]	    =   $phone;

			$countrycode                        =   $brickPhone->getRegionCode($phone);
			$this->comptes[0]["newCountry"]	    =   $brickPhone->getCountryByCode($countrycode)['name'];
			try{
                $data = [
                    'phone'         => $phone,
                    'amount'        => $this->validateAmount,
                    'description'   => $this->intl->trans('Verification du N° de Téléphone'),
                    'canal'         => "online",
                    'method'        => 'mobile',
                    'object'        => '#MET0_number_validation',
                    'object_uid'    => 'mobile_'.$user->getUid(),
                ];
                //$initPay = $this->sAgregatorRouter->processRouter($countrycode, $data);
				$user->setPaymentAccount($this->comptes);
				$this->em->persist($user);
				$this->em->flush();

				return $this->services->msg_success(
					$this->intl->trans("Mise à jour de données de la méthode de paiement Mobile"),
					$this->intl->trans("Enrégistrés avec succès")
				);

				//return $initPay;
			}
			catch(Exception $e)
			{
				return $this->services->msg_error(
					$this->intl->trans("Mise à jour de données de la méthode de paiement Mobile"),
					$this->intl->trans($this->translator->trans("Une erreur inattendue s'est produite. Reéssayer plus tard."))
				);
			}

		}else{
			$this->comptes[0]["Owner"]	    =	$ownerName;
			$this->comptes[0]["Operator"]		=	$operator;
			$user->setPaymentAccount($this->comptes);
			$this->em->persist($user);
			$this->em->flush();
			return $this->services->msg_success(
                $this->intl->trans("Mise à jour de données de la méthode de paiement Mobile"),
                $this->intl->trans("Enrégistrés avec succès")
            );
		}
	}

	//Reinitialise les données de Paiement
	#[Route('/payment/delete', name: 'paymentDataDelete')]
	public function payment_delete(Request $request)
	{
		//Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
		return $this->services->ajax_ressources_no_access($this->intl->trans("Suppression d'une méthode de paiement"));

		if(!$this->pDelete){
			return $this->services->msg_error(
				$this->intl->trans("Suppression d'une méthode de paiement"),
				$this->intl->trans("Vous n'avez pas la permission requise pour éffectuer cette à action"));
		}

		$user = $this->getUser();
		$method = $request->request->get('method');

		$nameMethod = "";
		$comptes = $user->getPaymentAccount();

		switch ($method) {
			case 'momo':
				$this->comptes[0]["newOwner"] = null;
				$this->comptes[0]["newOperator"] = null;
				$comptes[0] = $this->comptes[0];
				$nameMethod = "Mobile Money";
				break;
			case 'visa':
				$comptes[2] = $this->comptes[2];
				$nameMethod = "Visa/Mastercard";
				break;
			case 'bank':
				$comptes[1] = $this->comptes[1];
				$nameMethod = "Compte Bancaire";
				break;
			default:
				// code...
				break;
		}
		$user->setPaymentAccount($comptes)->setUpdatedAt(new \DateTimeImmutable('now'));
		$this->em->persist($user);
		$this->em->flush();
		return $this->services->msg_success(
			$this->intl->trans("Suppression d'une méthode de paiement"),
			$this->intl->trans("Méthode de paiement supprimé avec succès"));
	}

	#[Route('/get_methods', name: 'app_payment_methods_get', methods: ['POST'])]
    public function get_this_user(Request $request): Response
    {
		$user = $this->getUser();
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
        return $this->services->ajax_ressources_no_access($this->intl->trans("Récupération de des méthods de paiements").': '.$user->getEmail());

		$momo = $user->getPaymentAccount()[0];
        $row['momo'] =  [
						'owner' => $momo['Owner'],
						'operator' => $momo['Operator']
						];

        return new JsonResponse([
            'data' => $row, 
            'message' => $this->intl->trans('Vos données sont chargés avec succès.')]);
    }  
}
