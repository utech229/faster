<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Contact;
use App\Service\uBrand;
use App\Form\ContactType;
use App\Service\Services;
use App\Service\BrickPhone;
use App\Entity\ContactGroup;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use App\Repository\ContactRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\Request;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
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
#[Route('/{_locale}/home/addressbooks')]
class ContactController extends AbstractController
{
    public function __construct(UrlGeneratorInterface $urlGenerator, Services $services, BrickPhone $brickPhone,  
    EntityManagerInterface $entityManager, TranslatorInterface $translator, UserRepository $userRepository, 
    StatusRepository $statusRepository,
    uBrand $brand,ValidatorInterface $validator, ContactRepository $contactRepository)
    {
        $this->urlGenerator             = $urlGenerator;
        $this->intl                     = $translator;
        $this->services                 = $services;
        $this->brickPhone               = $brickPhone;
        $this->brand                    = $brand;
        $this->em	                    = $entityManager;
        $this->statusRepository         = $statusRepository;
        $this->userRepository           = $userRepository;
        $this->contactRepository        = $contactRepository;
        $this->validator                = $validator;

        $this->permission           =    ["CNTS0", "CNTS1", "CNTS2","CNTS3" ,"CNTS4", "CNTS5","CNTG0", "CNTG1", "CNTG2", "CNTG3", "CNTG4","SMSC1"];
        $this->pAccess              =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate              =    $this->services->checkPermission($this->permission[1]);
        $this->pView                =    $this->services->checkPermission($this->permission[2]);
        $this->pUpdate              =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete              =    $this->services->checkPermission($this->permission[4]);
        $this->pAllView             =    $this->services->checkPermission($this->permission[5]);
        $this->pGAccess             =    $this->services->checkPermission($this->permission[6]);
        $this->pGCreate             =    $this->services->checkPermission($this->permission[7]);
        $this->pGView               =    $this->services->checkPermission($this->permission[8]);
        $this->pGUpdate             =    $this->services->checkPermission($this->permission[9]);
        $this->pGDelete             =    $this->services->checkPermission($this->permission[10]);
        $this->pCreateCampagne      =    $this->services->checkPermission($this->permission[11]);

    }

    #[Route('', name: 'app_contact_index', methods: ['GET'])]
    public function index(ContactRepository $contactRepository, Request $request, Contact $contact = null): Response
    {
        
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

        list($typeUser,$Id) =   $this->services->checkThisUser($this->pAllView);

        $users              =   [];
        $groups             =   [];


        if ($this->pView || $this->pAllView) {
            $users          =   $this->services->getUserByPermission($this->permission[1], $typeUser, $this->getUser(), 0);
            $groups         =   count($users) > 0 ? $groups : $this->getUser()->getContactGroups();
        }
        return $this->render('contact/index.html.twig', [
            'title'           => $this->intl->trans("Carnet d'adresse").' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans("Gestion des contacts")],
                [$this->intl->trans("Mes contacts")],
            ],
            'brand'             => $this->brand->get(),
            'users'             => $users,
            'groups'            => $groups,
            'pCreate'           => $this->pCreate,
            'pUpdate'           => $this->pUpdate,
            'pDelete'           => $this->pDelete,
            'pGCreate'          => $this->pGCreate,
            'pGUpdate'          => $this->pGUpdate,
            'pGDelete'          => $this->pGDelete,
            'pCreateCampagne'   => $this->pCreateCampagne

        ]);
    }

    #[Route('/contact/list', name: 'app_contact_list', methods: ['POST'])]
    public function getContactList(Request $request, ContactRepository $contactRepository): Response
    {
        //Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Récupération de la liste des contacts : token invalide'));

        $tabContact     =   [];
        $contact        =   [];
        $group          =   [];

        $data = [
            "data"              =>   $tabContact,
            "group"             =>   $group
        ];

        if (!$this->pView)   return new JsonResponse($data);
        
            $group = $this->em->getRepository(ContactGroup::class)->findOneByUid($request->request->get('_group'));

            if (!$group) return new JsonResponse($data);

            $contacts   =   $group->getContacts(); 

            foreach ($contacts as $key => $contact) {
                                
                $tabContact[$key][0][0]   =   $contact->getUid();
                $tabContact[$key][0][1]   =   $group->getUid();
                $tabContact[$key][0][2]   =   $group->getName();
                $tabContact[$key][1]      =   $contact->getPhone();
                $tabContact[$key][2]      =   $contact->getField1();
                $tabContact[$key][3]      =   $contact->getField2();
                $tabContact[$key][4]      =   $contact->getField3();
                $tabContact[$key][5]      =   $contact->getField4();
                $tabContact[$key][6]      =   $contact->getField5();
                $tabContact[$key][7]      =   $contact->getCreatedAt()->format("c");
                $tabContact[$key][8]      =   $contact->getUpdatedAt()?$contact->getUpdatedAt()->format("c"):$this->intl->trans('Pas de modification');
                $tabContact[$key][9]      =   $contact->getUid();
            }
            $data   =[
                "data"              =>   $tabContact,
                "group"             =>   [
                    "name"          =>   $group->getName(),
                    "field1"        =>   $group->getField1(),
                    "field2"        =>   $group->getField2(),
                    "field3"        =>   $group->getField3(),
                    "field4"        =>   $group->getField4(),
                    "field5"        =>   $group->getField5(),
                ]
            ];
        return new JsonResponse($data);
    }

    #[Route('/contactd', name: 'app_contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Création de contact : token invalide'));
        
        $group  = $this->em->getRepository(ContactGroup::class)->findOneByUid($request->request->get('groupe'));
        if ($group) {
            foreach ($request->get('kt_docs_repeater_basic') as $key => $value) {

                if($this->brickPhone->isValidNumber($value["full_number"]) != true )
                return $this->services->msg_error($this->intl->trans("Mauvais format de contact renseigné"),$this->intl->trans($value["full_number"]." est invalide. Veuillez renseigner un numéro valide"));

                $contact = new Contact();

                $contact->setUid(uniqid());
                $contact->setPhone($value["full_number"]);
                $contact->setIsImported(1);
                $contact->setField1($value["set1"]);
                $contact->setField2($value["set2"]);
                $contact->setField3($value["set3"]);
                $contact->setField4($value["set4"]);
                $contact->setField5($value["set5"]);
                $contact->setContactGroup($group);
                $contact->setPhoneCountry($this->brickPhone->getInfosCountryFromCode($value["full_number"]));
                $contact->setCreatedAt(new \DatetimeImmutable());
                $this->contactRepository->add($contact);
            }
        return $this->services->msg_success($this->intl->trans("Ajout d'un contact"),$this->intl->trans("Votre contact a été ajouté avec succès"));
        }
        return $this->services->msg_info($this->intl->trans("Groupe de contact non choisi lors de l'ajout de contact"),$this->intl->trans("Veuillez sélectionner un groupe de contacts"));
    }

    #[Route('/contact/import', name: 'app_contact_import', methods: ['GET', 'POST'])]
    public function import(Request $request): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) return $this->services->invalid_token_ajax_list($this->intl->trans('importation de contacts : token invalide'));
        
        $group  =   $this->em->getRepository(ContactGroup::class)->findOneByUid($request->request->get('group'));
        
        if(!$group) return $this->services->msg_info($this->intl->trans("Echec d'importation de contacts: Groupe manquant."),$this->intl->trans("Veuillez sélectionner un groupe de contact."));
        
        if($request->request->get('hidden_file') == "") return $this->services->msg_info($this->intl->trans("Echec d'importation de contacts: Fichier manquant."),$this->intl->trans("Veuillez ajouter votre fichier avant de soumettre."));
        
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($request->request->get('hidden_file'));
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($request->request->get('hidden_file'));
        } catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            die('Error loading file: '.$e->getMessage());
        }

        //File content getting in variable
        $worksheet = $spreadsheet->getActiveSheet()->toArray();
        $start = -1;

		$A1 = isset($worksheet[0][0]) ? $worksheet[0][0] : "";
		$A2 = isset($worksheet[1][0]) ? $worksheet[1][0] : "";
		if(is_numeric($A1)) $start = 0; else if(is_numeric($A2)) $start = 1;

        if($start == -1)    return $this->services->msg_error($this->intl->trans("Echec d'importation de contact:Mauvais formatage du fichier."),$this->intl->trans("Votre fichier a été mal formaté pour l'importation.Veuillez revoir le contenu du fichier et réessayez."));
        
        $tabCheck =   [];

        for($row = $start; $row < count($worksheet); $row++)
        {
            $check = $worksheet[$row][0].$worksheet[$row][1].$worksheet[$row][2].$worksheet[$row][3].$worksheet[$row][4].$worksheet[$row][5];
            if (!in_array($check,$tabCheck)) {

                $contact    = new Contact;
            
                $contact->setUid(uniqid())
                    ->setContactGroup($group)
                    ->setPhone("+".str_replace("+","",$worksheet[$row][0]))
                    ->setField1($worksheet[$row][1])
                    ->setField2($worksheet[$row][2])
                    ->setField3($worksheet[$row][3])
                    ->setField4($worksheet[$row][4])
                    ->setField5($worksheet[$row][5])
                    ->setIsImported(1)
                    ->setCreatedAt(new \DateTimeImmutable());
                    $this->contactRepository->add($contact);
            }
            $tabCheck[] = $check;
        } 

        return $this->services->msg_success($this->intl->trans("Importation de contacts effectuée avec succès"),$this->intl->trans("Importation de contacts effectué avec succès"));
    }

    #[Route('/{id}', name: 'app_contact_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render('contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/edit', name: 'app_contact_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContactRepository $contactRepository): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Modification de contact : token invalide'));
        
        $group  = $this->em->getRepository(ContactGroup::class)->findOneByUid($request->request->get('groupe'));
        if ($group) {

                $contact = $this->em->getRepository(Contact::class)->findOneByUid($request->request->get('_uid'));
        
                if (!$contact)  return $this->services->msg_error($this->intl->trans("Contact non retrouvé dans la base"),$this->intl->trans("Une erreur s'est produite, veuillez recommencer."));
                    
                $contact->setPhone($request->request->get('phone'));
                    $contact->setField1($request->request->get('set1'));
                    $contact->setField2($request->request->get('set2'));
                    $contact->setField3($request->request->get('set3'));
                    $contact->setField4($request->request->get('set4'));
                    $contact->setField5($request->request->get('set5'));
                    $contact->setContactGroup($group);
                    $contact->setPhoneCountry($this->brickPhone->getInfosCountryFromCode($request->request->get('phone')));
                    $contact->setUpdatedAt(new \DatetimeImmutable());
                    $this->contactRepository->add($contact);

        return $this->services->msg_success($this->intl->trans("Modification d'un contact"),$this->intl->trans("Votre contact a été ajouté avec succès"));
        }
        return $this->services->msg_info($this->intl->trans("Groupe de contact non choisi lors de la modification de contact"),$this->intl->trans("Veuillez sélectionner un groupe de contacts"));
    }

    #[Route('/delete', name: 'app_contact_delete', methods: ['POST'])]
    public function delete(Request $request, ContactRepository $contactRepository): Response
    {
       //Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Suppression de contact : token invalide'));
        foreach ($request->get('tabUid') as $key => $value) {
            $contact   =   $this->em->getRepository(Contact::class)->findOneByUid($value);
            $contactRepository->remove($contact);
        }
        return $this->services->msg_success($this->intl->trans("Suppression de contact"),$this->intl->trans("Votre contact a été supprimé avec succès"));
    }

}
