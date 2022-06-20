<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Service\uBrand;
use App\Form\ContactType;
use App\Service\Services;
use App\Service\BrickPhone;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('/{_locale}/home/addressbooks')]
class ContactController extends AbstractController
{
    public function __construct(UrlGeneratorInterface $urlGenerator, Services $services, BrickPhone $brickPhone,  
    EntityManagerInterface $entityManager, TranslatorInterface $translator, UserRepository $userRepository, 
    StatusRepository $statusRepository,
    uBrand $brand,ValidatorInterface $validator)
    {
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brickPhone      = $brickPhone;
        $this->brand           = $brand;
        $this->em	             = $entityManager;
        $this->statusRepository  = $statusRepository;
        $this->userRepository    = $userRepository;
        $this->validator         = $validator;

        $this->permission      =    ["CNTS0", "CNTS1", "CNTS2", "CNTS3", "CNTS4","CNTG0", "CNTG1", "CNTG2", "CNTG3", "CNTG4"];
        $this->pAccess         =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate         =    $this->services->checkPermission($this->permission[1]);
        $this->pView           =    $this->services->checkPermission($this->permission[2]);
        $this->pUpdate         =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete         =    $this->services->checkPermission($this->permission[4]);
        $this->pGAccess         =    $this->services->checkPermission($this->permission[5]);
        $this->pGCreate         =    $this->services->checkPermission($this->permission[6]);
        $this->pGView           =    $this->services->checkPermission($this->permission[7]);
        $this->pGUpdate         =    $this->services->checkPermission($this->permission[8]);
        $this->pGDelete         =    $this->services->checkPermission($this->permission[9]);
    }

    #[Route('', name: 'app_contact_index', methods: ['GET'])]
    public function index(ContactRepository $contactRepository, Request $request, Contact $contact = null): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            return $this->redirectToRoute("app_home");
        }

         /*----------MANAGE CRUD -----------*/
        $isAdd        = (!$contact) ? true : false;
        $contact      = (!$contact) ? new Contact() : $contact;
       
        $form = $this->createForm(ContactType::class, $contact);
        if ($request->request->count() > 0)
        {
            $form->handleRequest($request);
            if ($isAdd == true) { //method calling
                if (!$this->pCreate) return $this->services->ajax_ressources_no_access($this->intl->trans("Demande de paiement"));
                //return $this->addContact($request, $form, $contact);
            }else {
                if (!$this->pUpdate)   return $this->services->ajax_ressources_no_access($this->intl->trans("Traitement de demande de paiement"));
                //return $this->updateContact($request, $form, $contact);
            }
        }
        return $this->render('contact/index.html.twig', [
            //'contacts' => $contactRepository->findAll(),
            'title'           => $this->intl->trans("Carnet d'adresse").' - '. $this->brand->get()['name'],
            'pageTitle'       => [
                [$this->intl->trans("Gestion des contacts")],
                [$this->intl->trans("Mes contacts")],
            ],
            'brand'           => $this->brand->get(),
            'users'           => $this->userRepository->findAll(),
            'contactform'     => $form->createView(),
            'pCreate'     => $this->pCreate,
            'pEdit'       => $this->pUpdate,
            'pDelete'     => $this->pDelete,
        ]);
    }

    #[Route('/contactd', name: 'app_contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ContactRepository $contactRepository): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactRepository->add($contact, true);

            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contact/new.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
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
