<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\PriceType;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\AddEntity;
use App\Service\BrickPhone;
use App\Service\DbInitData;
use App\Repository\RoleRepository;
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
#[Route('/{_locale}/home/user')]
class PriceController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, UrlGeneratorInterface $urlGenerator, Services $services, BrickPhone $brickPhone,  
    EntityManagerInterface $entityManager, TranslatorInterface $translator,
    RoleRepository $roleRepository, UserRepository $userRepository, uBrand $brand,ValidatorInterface $validator,
    DbInitData $dbInitData, AddEntity $addEntity)
    {
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brickPhone      = $brickPhone;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->addEntity	   = $addEntity;
        $this->userRepository  = $userRepository;
        $this->validator         = $validator;
        $this->DbInitData        = $dbInitData;
    }

    #[Route('/price/{uid}', name: 'app_user_price')]
    public function index(Request $request, User $user): Response
    {
        $form = $this->createForm(PriceType::class, $user);

        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            $code =  $request->request->get('country');

            if(($form->isSubmitted() && $form->isValid()))
            {
                $price = $form->get('price')->getData();
                $countryCode   = strtoupper($request->request->get('country'));
             
                $countryDatas  = $this->brickPhone->getCountryByCode($countryCode);
                if ($countryDatas) {
                    $priceDatas = [ 'dial_code' => $countryDatas['dial_code'], 'code' => $countryCode,'name' => $countryDatas['name'], 'price' => $price];
                }else
                return $this->services->msg_error(
                    $this->intl->trans("Insertion du tableau de données pays"),
                    $this->intl->trans("La recherche du nom du pays à échoué : BrickPhone"),
                );

                $array = $user->getPrice();
                $array[$countryCode] = $priceDatas;
                $user->setPrice($array);
                $this->userRepository->add($user);

                //return msg
                $configuredCountry = array();
                $prices            = $user->getPrice();
                foreach ($prices  as $price) 
                {         
                    array_push($configuredCountry, strtoupper($price['code']));            
                }
                if (!in_array($code, $configuredCountry)) {
                    
                    return $this->services->msg_success(
                        $this->intl->trans("Ajout du prix du %1% pour l'utilisateur
                        %2%", ["%1%"=> $countryDatas['name'], "%2%"=> $user->getEmail()]),
                        $this->intl->trans("Prix ajouté avec succès")
                    );
                }else{
                    return $this->services->msg_success(
                        $this->intl->trans("Modification du prix du %1% pour l'utilisateur
                        %2%", ["%1%"=> $countryDatas['name'], "%2%"=> $user->getEmail()]),
                        $this->intl->trans("Prix modifié avec succès")
                    );
                }
            }
            else 
            {
                return $this->services->formErrorsNotification($this->validator, $this->intl, $user);
            }
            return $this->services->failedcrud($this->intl->trans("Mise à jour des prix"));
        }
        return $this->render('price/index.html.twig', [
            'controller_name' => 'PriceController',
            'title'           => $this->intl->trans('Prix').' - '. $user->getEmail() .' - '.$this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans('Gestion utlisateur'), $this->urlGenerator->generate('app_user_index')],
                [$this->intl->trans('Prix')],
                [$user->getEmail()],
            ],
            'user_uid'        => $user->getUid(),
            'user'            => $user,
            'brand'           => $this->brand->get(),
            'baseUrl'         => $this->baseUrl->init(),
            'priceform'        => $form->createView(),
        ]);
    }

    #[Route('/{uid}/get', name: 'app_price_get', methods: ['POST'])]
    public function get_this_user(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) 
        return $this->services->no_access($this->intl->trans("Récupération de du prix"));

        $code =  $request->request->get('code');
        $price = $user->getPrice()[$code];
        $row['name']         = $price['name'];
        $row['dial']         = $price['dial_code'];
        $row['code']         = strtoupper($price['code']);
        $row['price']        = $price['price'];
        return new JsonResponse([
            'data' => $row, 
            'message' => $this->intl->trans('Vos données sont chargés avec succès.')]);
    } 

    #[Route('/list/{uid}', name: 'app_price_list', methods: ['POST','GET'])]
    public function getUsers(Request $request, EntityManagerInterface $manager, User $user) : JsonResponse
    {
        //Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
            return $this->services->invalid_token_ajax_list($this->intl->trans('Récupération de la liste des prix de utilisateurs : token invalide'));

        $data = [];
        $prices  = $user->getPrice();
        foreach ($prices  as $price) 
		{          
            $row                 = array();
           
            $row['orderId']      = strtoupper($price['code']);
            $row['name']         = strtolower(str_replace(" ", "-", $price['name']));////$price['name']; 
            $row['dial']         = $price['dial_code'];
            $row['code']         = strtoupper($price['code']);
            $row['price']        = $price['price'];
            $row['action']       = strtoupper($price['code']);
            $data []             = $row;
		}
        $this->services->addLog($this->intl->trans('Lecture de la liste des prix utilisateurs'));
        $output = array("data" => $data);
        return new JsonResponse($output);
    }

  
}
