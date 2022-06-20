<?php

namespace App\Service;

use App\Entity\Role;
use App\Entity\Router;
use App\Service\User;
use App\Entity\Sender;
use App\Entity\Status;
use App\Service\sBrand;
use App\Service\AddLogs;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Entity\Permission;
use App\Entity\Authorization;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\RouterRepository;
use App\Repository\SenderRepository;
use App\Repository\StatusRepository;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AuthorizationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class DbInitData extends AbstractController
{
   
	public function __construct(TranslatorInterface $intl, sBrand $brand, Services $services,
    EntityManagerInterface $entityManager,UserRepository $userRepository,  PermissionRepository $permissionRepository, 
    RoleRepository $roleRepository, AuthorizationRepository $authorizationRepository, StatusRepository $statusRepository,
    RouterRepository $routerRepository, SenderRepository $senderRepository)
    {
        $this->services      = $services;
        $this->em	         = $entityManager;
        $this->intl          = $intl;
        $this->permissionRepository = $permissionRepository;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->authorizationRepository = $authorizationRepository;
        $this->statusRepository = $statusRepository;
        $this->routerRepository = $routerRepository;
        $this->senderRepository = $senderRepository;
    }

    public function addRole():void 
    {
        $existedUser  = $this->userRepository->findAll();
        if (!$existedUser) {
            $roleCodes = ["AFF0", "USE", "AFF1", "RES", "ACC", "ADM", "SUP"];
            $roleNames = [ "AFFILIATE_USER", "USER", "AFFILIATE_RESELLER", "RESELLER", "ACCOUNTING", "ADMINISTRATOR", "SUPER_ADMINISTRATOR"];
            $roleDescription = [
                "Rôle d'un affilié d'utilisateur",
                "Rôle de l'utilisateur simple", 
                "Rôle d'un affilié de revendeur", 
                "Rôle d'un revendeur du système", 
                "Rôle d'un comptable", 
                "Rôle pour un administrateur du système",
                "Rôle du super administrateur du système"
            ];
            for ($i=0; $i < (count($roleCodes)); $i++) { 
                $roleUser  = $this->roleRepository->findOneBy(['code' => $roleCodes[$i]]);
                if (!$roleUser) {
                    $role = new Role();
                    $role->setCode($roleCodes[$i]);
                    $role->setName($roleNames[$i]);
                    $role->setStatus($this->statusRepository->findOneByCode(3));
                    $role->setLevel($i);
                    $role->setDescription($roleDescription[$i]);
                    $role->setCreatedAt(new \DatetimeImmutable());
                    $this->em->persist($role);
                }
            }
            $this->em->flush();
            $response = true;
        }else{
            $response = false;
        }
    
    }

    public function addPermission():void 
    {
        $permissionCodes = [
            "UTI0", "UTI1", "UTI2","UTI3","UTI4", "PER0", "PER1","PER2","PER3","PER4","ROL0", "ROL1", "ROL2","ROL3","ROL4",
            "TRAN0", "TRAN1", "TRAN2","TRAN3","TRAN4","UST0", "UST1", "UST2","UST3","UST4","PFIL0", 
            "PFIL1", "PFIL2", "PFIL3","PFIL4","CFIG0", "CFIG1", "CFIG2","CFIG3","CFIG4",
            "LOG0", "LOG1","AFFL0", "AFFL1", "AFFL2","AFFL3","AFFL4","PAY0", "PAY1", "PAY2","PAY3","PAY4",
            "MET0", "MET1", "MET2","MET3","MET4","ROUT0", "ROUT1", "ROUT2","ROUT3","ROUT4","SEND0", "SEND1", "SEND2","SEND3","SEND4",
            "STUT0", "STUT1", "STUT2","STUT3","STUT4","CNTS0", "CNTS1", "CNTS2","CNTS3","CNTS4","CNTG0", "CNTG1", "CNTG2","CNTG3","CNTG4",
            "SMSS0", "SMSS1", "SMSS2","SMSS3","SMSS4","SMSC0", "SMSC1", "SMSC2","SMSC3","SMSC4","CTGF0", "CTGF1", "CTGF2","CTGF3","CTGF4",
            "CNPY0", "CNPY1", "CNPY2","CNPY3","CNPY4","BRND0", "BRND1", "BRND2","BRND3","BRND4","XPER0", "XPER1", "XPER2","XPER3","XPER4",
            "REC0", "REC1", "REC2","REC3","REC4", "DEV0", "DEV1", "DEV2","DEV3","DEV4"
        ];
        $permissionNames = [
            "Menu utilisateur", "Ajout utilisateur", "Voir utilisateur", "Modification utilisateur", 
            "Suppression utilisateur","Menu permission", "Ajout permission", "Voir permission", "Modification permission", 
            "Suppression permission", "Accès rôle", "Ajout rôle", "Voir rôle ","Modification rôle", "Suppression rôle",
            "Menu Transaction", "Ajout transaction", "Voir transaction","Modification transaction", "Suppression transaction",
            "Menu Paramètre", "Ajout paramètre", "Voir paramètre","Modification paramètre", "Suppression paramètre",
            "Menu Profil", "Ajout profil", "Voir profil","Modification profil", "Suppression profil",
            "Menu configuration", "Ajout configuration", "Voir configuration","Modification configuration", "Suppression configuration",
            "Menu log", "Voir log", "Menu affiliation", "Ajout affiliation", "Voir affiliation","Modification affiliation", "Suppression affiliation",
            "Menu demande de paiement", "Ajout demande de paiement", "Voir demande de paiement","Modification demande de paiement", "Suppression demande de paiement",
            "Menu méthode de paiement", "Ajout méthode de paiement", "Voir méthode de paiement","Modification méthode de paiement", "Suppression méthode de paiement",
            "Menu route du système", "Ajout route du système", "Voir route du système","Modification route du système", "Suppression route du système",
            "Menu sender(s)", "Ajout sender(s)", "Voir sender(s)","Modification sender(s)", "Suppression sender(s)",
            "Menu status", "Ajout status", "Voir status","Modification status", "Suppression status",
            "Menu contact", "Ajout contact", "Voir contact","Modification contact", "Suppression contact",
            "Menu groupe de contact", "Ajout groupe de contact", "Voir groupe de contact","Modification groupe de contact", "Suppression groupe de contact",
            "Menu Message", "Ajout Message", "Voir Message","Modification Message", "Suppression Message",
            "Menu campagne", "Ajout campagne", "Voir campagne","Modification campagne", "Suppression campagne",
            "Menu champ de groupe de contact", "Ajout champ de groupe de contact", "Voir champ de groupe de contact","Modification champ de groupe de contact", "Suppression champ de groupe de contact",
            "Menu entreprise", "Ajout entreprise", "Voir entreprise","Modification entreprise", "Suppression entreprise",
            "Menu marque de revente", "Ajout marque de revente", "Voir marque de revente","Modification marque de revente", "Suppression marque de revente",
            "Menu permission extra", "Ajout permission extra", "Voir permission extra","Modification permission extra", "Suppression permission extra",
            "Menu recharge", "Ajout recharge", "Voir recharge","Modification recharge", "Suppression recharge",
            "Menu développeur", "Ajout développeur (clé api)", "Voir développeur (clé api)","Modification développeur (clé api)", "Suppression développeur (clé api)"
        ];
        $permissionDescription = [
            "Permet d\'afficher la page utilisateurs", 
            "Permet d\'ajouter  un ou plusieurs utilisateur", 
            "Permet de voir tous les utilisateurs de bas niveau de la plateforme", 
            "Permet de modifier les données d\'un ou plusieurs utilisateur(s) de bas niveau",
            "Permet de supprimer un ou plusieurs utilisateur(s) de bas niveau",
            "Permet d\'afficher les permissions", 
            "Permet d\'ajouter une ou plusieurs permission(s)", 
            "Permet de voir toutes les permissions", 
            "Permet de modifier une ou plusieurs permission(s)", 
            "Permet de supprimer une ou plusieurs permission(s)",
            "Permet d\'afficher les rôles", 
            "Permet d\'ajouter une ou plusieurs rôle(s)", 
            "Permet de voir toutes les rôles", 
            "Permet de modifier une ou plusieurs rôle(s)", 
            "Permet de supprimer une ou plusieurs rôle(s)",
            "Permet d\'afficher le menu des transactions", 
            "Permet d\'ajouter une ou plusieurs  transaction(s)", 
            "Permet de voir toutes les  transactions", 
            "Permet de modifier une ou plusieurs  transaction(s)",
            "Permet de supprimer une ou plusieurs  transaction(s)",
            "Permet d\'afficher le menu des paramètres", 
            "Permet d\'ajouter une ou plusieurs  paramètre(s)", 
            "Permet de voir toutes les  paramètres", 
            "Permet de modifier une ou plusieurs  paramètre(s)",
            "Permet de supprimer une ou plusieurs  paramètre(s)",
            "Permet d\'afficher le menu des profils", 
            "Permet d\'ajouter une ou plusieurs  profil(s)", 
            "Permet de voir toutes les  profils", 
            "Permet de modifier une ou plusieurs  profil(s)",
            "Permet de supprimer une ou plusieurs  profil(s)",
            "Permet d\'afficher le menu des configurations", 
            "Permet d\'ajouter une ou plusieurs  configuration(s)", 
            "Permet de voir toutes les  configuration(s)", 
            "Permet de modifier une ou plusieurs configuration(s)",
            "Permet de supprimer une ou plusieurs  configuration(s)",
            "Permet d\'afficher le menu des logs", 
            "Permet de voir toutes les  logs(s)",
            "Permet d\'afficher le menu des affiliations", 
            "Permet d\'ajouter une ou plusieurs  affiliation(s)", 
            "Permet de voir toutes les  affiliation(s)", 
            "Permet de modifier une ou plusieurs affiliation(s)",
            "Permet de supprimer une ou plusieurs  affiliation(s)",
            "Permet d\'afficher le menu des demande de paiements", 
            "Permet d\'ajouter une ou plusieurs  demande de paiement(s)", 
            "Permet de voir toutes les  demande(s)  de paiements", 
            "Permet de modifier une ou plusieurs  demande(s) de paiement(s)",
            "Permet de supprimer une ou plusieurs  demande(s) de paiement(s)",
            "Permet d\'afficher le menu des méthodes de paiements", 
            "Permet d\'ajouter une ou plusieurs  méthode(s) de paiement(s)", 
            "Permet de voir toutes les  méthode(s) de paiements", 
            "Permet de modifier une ou plusieurs méthode(s) de paiement(s)",
            "Permet de supprimer une ou plusieurs  méthode(s) de paiement(s)",
            "Permet d\'afficher le menu des routes du système", 
            "Permet d\'ajouter un ou plusieurs routes du système", 
            "Permet de voir toutes les  routes du systèmes", 
            "Permet de modifier une ou plusieurs route(s) du système",
            "Permet de supprimer une ou plusieurs route(s) du système",
            "Permet d\'afficher le menu sender", 
            "Permet d\'ajouter un ou plusieurs sender(s)", 
            "Permet de voir toutes les  senders", 
            "Permet de modifier une ou plusieurs sender(s)",
            "Permet de supprimer une ou plusieurs sender(s)",
            "Permet d\'afficher le menu status", 
            "Permet d\'ajouter un ou plusieurs status(s)", 
            "Permet de voir toutes les  statuss", 
            "Permet de modifier une ou plusieurs status(s)",
            "Permet de supprimer une ou plusieurs status(s)",
            "Permet d\'afficher le menu contact", 
            "Permet d\'ajouter un ou plusieurs contact(s)", 
            "Permet de voir toutes les  contacts", 
            "Permet de modifier un ou plusieurs contact(s)",
            "Permet de supprimer un ou plusieurs contact(s)",
            "Permet d\'afficher le menu groupe contact", 
            "Permet d\'ajouter un ou plusieurs groupe de contact(s)", 
            "Permet de voir toutes les  groupe de contacts", 
            "Permet de modifier un ou plusieurs groupe de contact(s)",
            "Permet de supprimer un ou plusieurs groupe de contact(s)",
            "Permet d\'afficher le menu message", 
            "Permet d\'ajouter un ou plusieurs message(s)", 
            "Permet de voir toutes les  messages", 
            "Permet de modifier un ou plusieurs message(s)",
            "Permet de supprimer un ou plusieurs message(s)",
            "Permet d\'afficher le menu campagne", 
            "Permet d\'ajouter un ou plusieurs campagne(s)", 
            "Permet de voir toutes les  campagnes", 
            "Permet de modifier un ou plusieurs campagne(s)",
            "Permet de supprimer un ou plusieurs campagne(s)",
            "Permet d\'afficher le menu champ de groupe de contact", 
            "Permet d\'ajouter un ou plusieurs champ de groupe de contact(s)", 
            "Permet de voir toutes les  champ de groupe de contacts", 
            "Permet de modifier un ou plusieurs champ de groupe de contact(s)",
            "Permet de supprimer un ou plusieurs champ de groupe de contact(s)",
            "Permet d\'afficher le menu entreprise", 
            "Permet d\'ajouter un ou plusieurs entreprise(s)", 
            "Permet de voir toutes les  entreprises", 
            "Permet de modifier un ou plusieurs entreprise(s)",
            "Permet de supprimer un ou plusieurs entreprise(s)",
            "Permet d\'afficher le menu marque de revente", 
            "Permet d\'ajouter un ou plusieurs marque de revente(s)", 
            "Permet de voir toutes les  marque de reventes", 
            "Permet de modifier un ou plusieurs marque de revente(s)",
            "Permet de supprimer un ou plusieurs marque de revente(s)",
            "Permet d\'afficher le menu permission extra", 
            "Permet d\'ajouter un ou plusieurs permission extra(s)", 
            "Permet de voir toutes les  permission extras", 
            "Permet de modifier un ou plusieurs permission extra(s)",
            "Permet de supprimer un ou plusieurs permission extra(s)",
            "Permet d\'afficher le menu recharge", 
            "Permet d\'ajouter un ou plusieurs recharge(s)", 
            "Permet de voir toutes les  recharges", 
            "Permet de modifier un ou plusieurs recharge(s)",
            "Permet de supprimer un ou plusieurs recharge(s)",
            "Permet d\'afficher le menu developper", 
            "Permet d\'ajouter un ou plusieurs developper(s)", 
            "Permet de voir toutes les  developpers", 
            "Permet de modifier un ou plusieurs developper(s)",
            "Permet de supprimer un ou plusieurs developper(s)",
        ];
        $status = $this->statusRepository->findOneByCode(3);
        for ($i=0; $i < (count($permissionCodes)); $i++) { 
            $permissionUser  = $this->permissionRepository->findOneBy(['code' => $permissionCodes[$i]]);
            if (!$permissionUser) {
                $permission = new Permission();
                $permission->setCode($permissionCodes[$i]);
                $permission->setName($permissionNames[$i]);
                $permission->setDescription($permissionDescription[$i]);
                $permission->setCreatedAt(new \DatetimeImmutable());
                $permission->setStatus($status);
                $this->em->persist($permission);
            }
        }
        $this->em->flush();
    }

    #[Route('/addauthorization', name: 'app_authorization')]
    public function addAuthorization(): void
    {
        $roleId = [7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,
        7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7];
        $permissionId = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,
        31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,
        71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,103,104,105,106,107,108,109,110,111,112,
    113,114,115,116,117];
        $status = $this->statusRepository->findOneByCode(3);
        for ($i=0; $i < (count($roleId)); $i++) 
        { 
            $role              = $this->roleRepository->findOneBy(['id' => $roleId[$i]]);
            $permission        = $this->permissionRepository->findOneBy(['id' => $permissionId[$i]]);
            $is_authorization  = $this->authorizationRepository->findOneBy(['id' => $permissionId[$i]]);
           
            if (!$is_authorization) {
                $authorization = new Authorization();  
                $authorization->setRole($role);
                $authorization->setPermission($permission);
                $authorization->setStatus($status);
                $authorization->setDescription($role->getDescription().' - '.$permission->getDescription());
                $authorization->setCreatedAt(new \DatetimeImmutable());
                $this->em->persist($authorization);
            }
        }
        $this->em->flush();
    }

    public function addStatus():void 
    {
        $existedStatus  = $this->statusRepository->findAll();
        if (!$existedStatus) {
            $statusCodes = [0,1,2,3,4,5,6,7];
            $statusNames = [ "Programmé","En cours", "En attente", "Actif", "Désactivé", "Suspendu", "Approuvé", "Annulé"];
            $statusDescription = [
                "Statut programmé sur une entité",
                "Statut en cours  d'une entité",
                "Statut en attente d'une entité",
                "Statut actif d'une entité",
                "Statut désactivé d'une entité",
                "Statut suspendu d'une entité",
                "Statut approuvé d'une entité",
                "Statut annulé d'une entité"
            ];
            for ($i=0; $i < (count($statusCodes)); $i++) { 
                $status  = $this->statusRepository->findOneBy(['code' => $statusCodes[$i]]);
                if (!$status) {
                    $status = new Status();
                    $status->setUid($this->services->idgenerate(15));
                    $status->setCode($statusCodes[$i]);
                    $status->setName($statusNames[$i]);
                    $status->setDescription($statusDescription[$i]);
                    $status->setCreatedAt(new \DatetimeImmutable());
                    $this->em->persist($status);
                }
            }
            $this->em->flush();
            $response = true;
        }else{
            $response = false;
        }
    }

    public function addRoute():void 
    {
        $existed_route  = $this->routerRepository->findAll();
        if (!$existed_route) {
            $routeNames = [ "Fastermessage_moov", "Fastermessage_mtn", "Zekin_moov", "Zekin_mtn"];
            $routeDescription = [
                "Route d'envoie de SMS par MOOV AFRICA BENIN",
                "Route d'envoie de SMS par MTN BENIN",
                "Route d'envoie de SMS de ZEKIN par MTN BENIN",
                "Route d'envoie de SMS de ZEKIN par MOOV AFRICA BENIN"
            ];
            $status = $this->statusRepository->findOneByCode(3);
            for ($i=0; $i < (count($routeNames)); $i++) {
                $routex = $this->routerRepository->findOneByName($routeNames[$i]);
                if (!$routex) {
                    $route = new Router(); 
                    $route->setUid($this->services->idgenerate(10));
                    $route->setName($routeNames[$i]);
                    $route->setStatus($this->services->status(3));
                    $route->setDescription($routeDescription[$i]);
                    $route->setCreatedAt(new \DatetimeImmutable());
                    $this->em->persist($route);
                }
            }
            $this->em->flush();
            $response = true;
        }else{
            $response = false;
        }
    
    }

    public function addSender():void 
    {
        $existed_sender  = $this->senderRepository->findAll();
        if (!$existed_sender) {
            $senderNames = [ "FASTERMSG"];
            $senderDescription = [
                "Identifiant d'envoie de SMS par défaut du système"
            ];
            $status = $this->statusRepository->findOneByCode(3);
            for ($i=0; $i < (count($senderNames)); $i++) { 
                $senderUser  = $this->senderRepository->findOneBy(['name' => $senderNames[$i]]);
                if (!$senderUser) {
                    $sender = new Sender(); 
                    $sender->setUid($this->services->idgenerate(10));
                    $sender->setManager($this->userRepository->findOneById(1));
                    $sender->setName($senderNames[$i]);
                    $sender->setStatus($status);
                    $sender->setObservation($senderDescription[$i]);
                    $sender->setCreatedAt(new \DatetimeImmutable());
                    $this->em->persist($sender);
                }
            }
            $this->em->flush();
            $response = true;
        }else{
            $response = false;
        }
    
    }

    
}
