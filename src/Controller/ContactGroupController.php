<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\User;
use App\Service\Services;
use App\Entity\ContactGroup;
use App\Form\ContactGroupType;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ContactGroupRepository;
use App\Repository\ContactRepository;
use DateTime;
use DateTimeImmutable;
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

        $this->permission           =    ["CNTS0", "CNTS1", "CNTS2", "CNTS3", "CNTS4","CNTS5","CNTG0", "CNTG1", "CNTG2", "CNTG3", "CNTG4","CNTG5"];
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
        $this->pGAllView            =    $this->services->checkPermission($this->permission[11]);
    
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

        if (!$this->pGView) {
            
            $data = [
                "data"              =>   $tabGroup
            ];
            return new JsonResponse($data);
        }

        list($typeUser,$Id) =   $this->services->checkThisUser($this->pGAllView);

        switch ($request->request->get('_uid')) {
            case 'all':
                if ($typeUser == 0) {
                    $groups   =    $this->em->getRepository(ContactGroup::class)->findAll();
                
                }
                else
                {
                    $users   =   $this->services->getUserByPermission($this->pGCreate,$typeUser,$Id,1);
                
                    foreach ($users as $key => $user) {
                        foreach ($user->getContactGroups() as $key => $group) {
                            
                            $tabGroup[$key][0]      =   $group->getUid();
                            $tabGroup[$key][1]      =   $group->getName();
                            $tabGroup[$key][2]      =   $group->getField1();
                            $tabGroup[$key][3]      =   $group->getField2();
                            $tabGroup[$key][4]      =   $group->getField3();
                            $tabGroup[$key][5]      =   $group->getField4();
                            $tabGroup[$key][6]      =   $group->getField5();
                            $tabGroup[$key][7]      =   $group->getCreatedAt()->format("c");
                            $tabGroup[$key][8]      =   $group->getUpdatedAt()?$group->getUpdatedAt()->format("c"):$this->intl->trans('Pas de modification');
                            $tabGroup[$key][9]      =   $group->getUid();

                        }
                    }
                    $data   =[
                                "data"              =>   $tabGroup,
                            ];
                    return new JsonResponse($data);
                }
                break;
            case '':
                    $groups   = [];
                    break;
            
            default:
                    $groups   =    $this->em->getRepository(ContactGroup::class)->findByManager($this->em->getRepository(User::class)->findByUid($request->request->get('_uid')));
                    break;
        }

        foreach ($groups as $key => $group) {
                            
            $tabGroup[$key][0]      =   $group->getUid();
            $tabGroup[$key][1]      =   $group->getName();
            $tabGroup[$key][2]      =   $group->getField1();
            $tabGroup[$key][3]      =   $group->getField2();
            $tabGroup[$key][4]      =   $group->getField3();
            $tabGroup[$key][5]      =   $group->getField4();
            $tabGroup[$key][6]      =   $group->getField5();
            $tabGroup[$key][7]      =   $group->getCreatedAt()->format("c");
            $tabGroup[$key][8]      =   $group->getUpdatedAt()?$group->getUpdatedAt()->format("c"):$this->intl->trans('Pas de modification');
            $tabGroup[$key][9]      =   $group->getUid();

        }
       
        $this->services->addLog($this->intl->trans('Lecture de la liste des groupes de contacts'));
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

        if($request->request->get('user_group')==null || $request->request->get('user_group')=="") return $this->services->msg_error($this->intl->trans("Echec d'ajout de groupe de contact, Utilisateur manquant") ,$this->intl->trans("Veuillez sélectionner l'utilisateur auquel vous souhaitez ajouter un groupe de contacts."));
        $user       =   $this->em->getRepository(User::class)->findOneByUid($request->request->get('user_group'));
         
        if ($groupName !="" ) {
            $request->request->get('admin')? $admin =   $this->em->getRepository(User::class)->findOneByUid($request->request->get('admin')):$admin =   null;

            $contactGroup           =   new ContactGroup();

            $set1                   =   trim($request->request->get('set1')) != "" ? trim($request->request->get('set1')) : "Champ1";
                $set2                   =   trim($request->request->get('set2')) != "" ? trim($request->request->get('set2')) : "Champ2";
                $set3                   =   trim($request->request->get('set3')) != "" ? trim($request->request->get('set3')) : "Champ3";
                $set4                   =   trim($request->request->get('set4')) != "" ? trim($request->request->get('set4')) : "Champ4";
                $set5                   =   trim($request->request->get('set5')) != "" ? trim($request->request->get('set5')) : "Champ5";

            
            $contactGroup->setName($groupName);
                $contactGroup->setField1($set1);
                $contactGroup->setField2($set2);
                $contactGroup->setField3($set3);
                $contactGroup->setField4($set4);
                $contactGroup->setField5($set5);
                $contactGroup->setManager($user);
                $contactGroup->setAdmin($admin);
                $contactGroup->setUid(uniqid());
                $contactGroup->setCreatedAt(new \DatetimeImmutable());
                $contactGroupRepository->add($contactGroup,true);

                $data = [
                           "uid" => $contactGroup->getUid(),
                           "name"=> $contactGroup->getName()
                ];
            return $this->services->msg_success($this->intl->trans("Ajout d'un groupe de contact"),$this->intl->trans("Votre groupe de contact a été ajouté avec succès"),$data);
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

    #[Route('/edit', name: 'app_contact_group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContactGroupRepository $contactGroupRepository): Response
    {
        //Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Suppression groupe de contact : token invalide'));

        $groupName  =   trim($request->request->get('groupName'));
        $user       =   $this->getUser();
        if ($groupName !="" ) {
            $contactGroup =  $contactGroupRepository->findOneByUid($request->request->get('_uid'));

                $set1                   =   trim($request->request->get('set1')) != "" ? trim($request->request->get('set1')) : "Champ1";
                    $set2                   =   trim($request->request->get('set2')) != "" ? trim($request->request->get('set2')) : "Champ2";
                    $set3                   =   trim($request->request->get('set3')) != "" ? trim($request->request->get('set3')) : "Champ3";
                    $set4                   =   trim($request->request->get('set4')) != "" ? trim($request->request->get('set4')) : "Champ4";
                    $set5                   =   trim($request->request->get('set5')) != "" ? trim($request->request->get('set5')) : "Champ5";

                
                $contactGroup->setName($groupName);
                    $contactGroup->setField1($set1);
                    $contactGroup->setField2($set2);
                    $contactGroup->setField3($set3);
                    $contactGroup->setField4($set4);
                    $contactGroup->setField5($set5);
                    $contactGroup->setUpdatedAt( new \DateTimeImmutable());
                    $contactGroupRepository->add($contactGroup,true);

            return $this->services->msg_success($this->intl->trans("Modification d'un groupe de contacts"),$this->intl->trans("Votre groupe de contact a été modifié avec succès"));
        }
        else 
        return $this->services->msg_error($this->intl->trans("Echec de modification d'un groupe de contacts, nom manquant") ,$this->intl->trans("Veuillez renseigner le nom du groupe"));
    }

    #[Route('/delete', name: 'app_contact_group_delete', methods: ['GET','POST'])]
    public function delete(Request $request, ContactGroupRepository $contactGroupRepository, ContactRepository $contactRepository): Response
    {
		//Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Suppression groupe de contact : token invalide'));
        
        foreach ($request->get('tabUid') as $key => $value) {
            $contactGroup   =   $this->em->getRepository(ContactGroup::class)->findOneByUid($value);
            $contactGroupRepository->remove($contactGroup);
        }
        return $this->services->msg_success($this->intl->trans("Suppression de groupe de contact"),$this->intl->trans("Votre groupe de contact a été supprimé avec succès"));
        
    }

    #[Route('/getgroup', name: 'app_get_group', methods: ['POST'])]
    public function getGroup(Request $request, ContactGroupRepository $contactGoupRepository): Response
    {
        //Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Récupération des groupes de contacts : token invalide'));

        $tabGroup       =   [];
        $data           =   [
                                "data"  =>   $tabGroup
                        ];

        if (!$this->pGView) return new JsonResponse($data);

        list($typeUser,$Id) =   $this->services->checkThisUser($this->pGAllView);
        switch ($request->request->get('_uid')) {
            case 'all':
                if ($typeUser == 0) {
                    $groups   =    $this->em->getRepository(ContactGroup::class)->findAll();
                }
                else
                {
                    $users   =   $this->services->getUserByPermission($this->pGCreate,$typeUser,$Id,1);
                
                    foreach ($users as $key => $user) {
                        foreach ($user->getContactGroups() as $key => $group) {
                            
                            $tabGroup[$key][0]      =   $group->getUid();
                            $tabGroup[$key][1]      =   $group->getName();
                        }
                    }
                    $data   =[
                                "data"              =>   $tabGroup,
                            ];
                    return new JsonResponse($data);
                }
                break;
            case '':
                    $groups   = [];
                    break;
            
            default:
                    $groups   =    $this->em->getRepository(ContactGroup::class)->findByManager($this->em->getRepository(User::class)->findByUid($request->request->get('_uid')));
                    break;
        }

        if (!$groups)  return new JsonResponse($data);

        foreach ($groups as $key => $group) {
                            
            $tabGroup[$key][0]      =   $group->getUid();
            $tabGroup[$key][1]      =   $group->getName();
        }
       
        $this->services->addLog($this->intl->trans('Récuperation des groupes de contacts'));
        $data = [
                    "data"              =>   $tabGroup
                ];
       
        return new JsonResponse($data);
    }

    #[Route('/getinfogroup', name: 'app_get_info_group', methods: ['POST'])]
    public function getInfoGroup(Request $request, ContactGroupRepository $contactGoupRepository): Response
    {
        //Vérification du tokken
		if (!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token')))
        return $this->services->invalid_token_ajax_list($this->intl->trans('Récupération des groupes de contacts : token invalide'));

        $infoGroup       =   [];

        if (!$this->pGView) return new JsonResponse($infoGroup);

        $group   =    $this->em->getRepository(ContactGroup::class)->findOneByUid($request->request->get('_uid'));

        $infoGroup[1]           =   "Champ1";       $infoGroup[2]      =   "Champ2";
            $infoGroup[3]       =   "Champ3";       $infoGroup[4]      =   "Champ4";
            $infoGroup[5]       =   "Champ5";
        
            if (!$group)  return new JsonResponse($infoGroup);
            
            $infoGroup[1]      =   $group->getField1();$infoGroup[2]    =   $group->getField2();
                $infoGroup[3]  =   $group->getField3();$infoGroup[4]    =   $group->getField4();
                $infoGroup[5]  =   $group->getField5();
       
        $this->services->addLog($this->intl->trans("Récuperation des informations d'un groupe de contacts"));
       
        return new JsonResponse($infoGroup);
    }
}
