<?php

namespace App\Controller;

use App\Service\Services;
use App\Entity\ContactGroup;
use App\Form\ContactGroupType;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ContactGroupRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/contact/group')]
class ContactGroupController extends AbstractController
{
    public function __construct(UrlGeneratorInterface $urlGenerator, Services $services,  
    EntityManagerInterface $entityManager, TranslatorInterface $translator, UserRepository $userRepository, 
    StatusRepository $statusRepository)
    {
        $this->urlGenerator         = $urlGenerator;
        $this->intl                 = $translator;
        $this->services             = $services;
        $this->em	                = $entityManager;
        $this->statusRepository     = $statusRepository;
        $this->userRepository       = $userRepository;

        $this->permission           =    ["CNTS0", "CNTS1", "CNTS2", "CNTS3", "CNTS4","CNTG0", "CNTG1", "CNTG2", "CNTG3", "CNTG4"];
        $this->pAccess              =    $this->services->checkPermission($this->permission[0]);
        $this->pCreate              =    $this->services->checkPermission($this->permission[1]);
        $this->pView                =    $this->services->checkPermission($this->permission[2]);
        $this->pUpdate              =    $this->services->checkPermission($this->permission[3]);
        $this->pDelete              =    $this->services->checkPermission($this->permission[4]);
        $this->pGAccess             =    $this->services->checkPermission($this->permission[5]);
        $this->pGCreate             =    $this->services->checkPermission($this->permission[6]);
        $this->pGView               =    $this->services->checkPermission($this->permission[7]);
        $this->pGUpdate             =    $this->services->checkPermission($this->permission[8]);
        $this->pGDelete             =    $this->services->checkPermission($this->permission[9]);
    }
    
    #[Route('/', name: 'app_contact_group_index', methods: ['GET'])]
    public function index(ContactGroupRepository $contactGroupRepository): Response
    {
        $data   =[
            "data"              =>   []
        ];
        return new JsonResponse($data);
    }

    #[Route('/list', name: 'app_group_list', methods: ['POST'])]
    public function getGroupList(Request $request, ContactGroupRepository $contactGoupRepository): Response
    {
        //Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Récupération de la liste des groupes de contacts : token invalide'));

        $data           =   [];
        $tabGroup       =   [];

        $groups         =   $this->em->getRepository(ContactGroup::class)->findByManager($this->getUser());
        foreach ($groups as $key => $group) {

            // $tabGroup[$key][0]      =   $group->getContactGroupField()->getUid();
            // $tabGroup[$key][1]      =   $group->getName();
            // $tabGroup[$key][2]      =   $group->getContactGroupField()->getField1();
            // $tabGroup[$key][3]      =   $group->getContactGroupField()->getField2();
            // $tabGroup[$key][4]      =   $group->getContactGroupField()->getField3();
            // $tabGroup[$key][5]      =   $group->getContactGroupField()->getField4();
            // $tabGroup[$key][6]      =   $group->getContactGroupField()->getField5();
            // $tabGroup[$key][7]      =   $group->getCreatedAt()->format("c");
            // $tabGroup[$key][8]      =   $group->getUpdatedAt()?$group->getUpdatedAt()->format("c"):$this->intl->trans('Pas de modification');
            // $tabGroup[$key][9]      =   $group->getContactGroupField()->getUid();


        }
       
        $this->services->addLog($this->intl->trans('Lecture de la liste des transactions'));
        $data = [
                    "data"              =>   $tabGroup
                ];
       
        return new JsonResponse($data);
    }

    #[Route('/new', name: 'app_contact_group_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ContactGroupRepository $contactGroupRepository): Response
    {
        //Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Création groupe de contact : token invalide'));
        
        $groupName  =   trim($request->request->get('groupName'));
        $user       =   $this->getUser();
        if ($groupName !="" ) {

            $contactGroup           =   new ContactGroup();

            $set1                   =   trim($request->request->get('set1')) != "" ? trim($request->request->get('set1')) : "param1";
                $set2                   =   trim($request->request->get('set2')) != "" ? trim($request->request->get('set2')) : "param2";
                $set3                   =   trim($request->request->get('set3')) != "" ? trim($request->request->get('set3')) : "param3";
                $set4                   =   trim($request->request->get('set4')) != "" ? trim($request->request->get('set4')) : "param4";
                $set5                   =   trim($request->request->get('set5')) != "" ? trim($request->request->get('set5')) : "param5";

            
            $contactGroup->setName($groupName);
                $contactGroup->setManager($user);
                $contactGroup->setUid(uniqid());
                $contactGroup->setCreatedAt(new \DatetimeImmutable());
                $contactGroupRepository->add($contactGroup,true);

            return $this->services->msg_success($this->intl->trans("Ajout d'un groupe de contact"),$this->intl->trans("Votre groupe de contact a été ajouté avec succès"));
        }
        else 
        return $this->services->msg_error($this->intl->trans("Echec d'ajout de groupe de contact, nom manquant") ,$this->intl->trans("Veuillez renseigner le nom du groupe"));
    }

    #[Route('/{id}', name: 'app_contact_group_show', methods: ['GET'])]
    public function show(ContactGroup $contactGroup): Response
    {
        return $this->render('contact_group/show.html.twig', [
            'contact_group' => $contactGroup,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contact_group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContactGroup $contactGroup, ContactGroupRepository $contactGroupRepository): Response
    {
        $form = $this->createForm(ContactGroupType::class, $contactGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactGroupRepository->add($contactGroup, true);

            return $this->redirectToRoute('app_contact_group_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contact_group/edit.html.twig', [
            'contact_group' => $contactGroup,
            'form' => $form,
        ]);
    }

    #[Route('/delete', name: 'app_contact_group_delete', methods: ['GET','POST'])]
    public function delete(Request $request, ContactGroupRepository $contactGroupRepository): Response
    {
		//Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Suppression groupe de contact : token invalide'));
        
        foreach ($request->get('tabUid') as $key => $value) {
            // $contactGroupField  =   $this->em->getRepository(ContactGroupField::class)->findOneByUid($value);
            // $contactGroup       =   $contactGroupField->getContactGroup();
            // $contactGroupFieldRepository->remove($contactGroupField);
            // $contactGroupRepository->remove($contactGroup);
        }
        return $this->services->msg_success($this->intl->trans("Suppression de groupe de contact"),$this->intl->trans("Votre groupe de contact a été supprimé avec succès"));
        
    }
}
