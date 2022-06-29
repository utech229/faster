<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Service\uBrand;
use App\Form\ContactType;
use App\Service\Services;
use App\Service\BrickPhone;
use App\Entity\ContactGroup;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use App\Repository\ContactRepository;
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

        $this->permission           =    ["CNTS0", "CNTS1", "CNTS2","CNTS3" ,"CNTS4", "CNTS5","CNTG0", "CNTG1", "CNTG2", "CNTG3", "CNTG4"];
        $this->pAccess              =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate              =    $this->services->checkPermission($this->permission[1]);
        $this->pView                =    $this->services->checkPermission($this->permission[2]);
        $this->pAllView             =    $this->services->checkPermission($this->permission[3]);
        $this->pUpdate              =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete              =    $this->services->checkPermission($this->permission[4]);
        $this->pGAccess             =    $this->services->checkPermission($this->permission[5]);
        $this->pGCreate             =    $this->services->checkPermission($this->permission[6]);
        $this->pGView               =    $this->services->checkPermission($this->permission[7]);
        $this->pGUpdate             =    $this->services->checkPermission($this->permission[8]);
        $this->pGDelete             =    $this->services->checkPermission($this->permission[9]);
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


        if ($this->pView) {
            $users          =   $this->services->getUserByPermission($this->pCreate, null, null, 1);
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
            'pEdit'             => $this->pUpdate,
            'pDelete'           => $this->pDelete,
        ]);
    }

    #[Route('/contact/list', name: 'app_contact_list', methods: ['POST'])]
    public function getContactList(Request $request, ContactRepository $contactRepository): Response
    {
        $data   =[
            "data"              =>   []
        ];
        return new JsonResponse($data);
    }

    #[Route('/contactd', name: 'app_contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Création de contact : token invalide'));
        
        $contact = new Contact();
        // $contactIndex = new ContactIndex();

        $group  = $this->em->getRepository(ContactGroup::class)->findOneByUid($request->request->get('group'));
        dd($request->get('kt_docs_repeater_basic'));

        foreach ($request->get('kt_docs_repeater_basic') as $key => $value) {

            $contact->setUid(uniqid());
            $contact->setPhone($value["full_number"]);
            $contact->setIsImported(0);
            $contact->setPhoneCountry($this->brickPhone->getInfosCountryFromCode($value["full_number"]));
            $contact->setCreatedAt(new \DatetimeImmutable());
            // $contact->addContactIndex($contactIndex);
            $this->contactRepository->add($contact);

            // $contactIndex->setUid(uniqid());
            // $contactIndex->setContactGroup($group);
            // $contactIndex->setField1($value["set1"]);
            // $contactIndex->setField2($value["set2"]);
            // $contactIndex->setField3($value["set3"]);
            // $contactIndex->setField4($value["set4"]);
            // $contactIndex->setField5($value["set5"]);
            // $contactIndex->setCreatedAt(new \DatetimeImmutable());
            // $contactIndex->setContact($contact);

            // $this->contactIndexRepository->add($contactIndex);

        }
        dd($contact);

        return $this->services->msg_success($this->intl->trans("Ajout d'un contact"),$this->intl->trans("Votre contact a été ajouté avec succès"));

    }

    #[Route('/{id}', name: 'app_contact_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render('contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contact_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contact $contact, ContactRepository $contactRepository): Response
    {
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactRepository->add($contact, true);

            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contact/edit.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact, ContactRepository $contactRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->get('_token'))) {
            $contactRepository->remove($contact, true);
        }

        return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
    }
}
