<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\User;
use App\Service\uBrand;
use App\Service\AddLogs;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Repository\LogRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
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
#[Route('/{_locale}/users/logs')]
class LogController extends AbstractController
{

    public function __construct(BaseUrl $baseUrl, TranslatorInterface $intl, uBrand $brand,
    UrlGeneratorInterface $urlGenerator, Services $services, StatusRepository $statusRepository,
    EntityManagerInterface $entityManager, LogRepository $logRepository, UserRepository $userRepository,
    RoleRepository $roleRepository)
    {
        $this->baseUrl       = $baseUrl->init();
        $this->urlGenerator  = $urlGenerator;
        $this->services      = $services;
        $this->em	         = $entityManager;
        $this->intl          = $intl;
        $this->brand         = $brand;
        $this->logRepository = $logRepository;
        $this->userRepository = $userRepository;
        $this->roleRepository    = $roleRepository;
        $this->statusRepository  = $statusRepository;
    }

    public function dashData()
    {
        $userRepository = $this->userRepository;
        $all            = $this->logRepository->countAll()[0][1];
        $allInDay       = $this->logRepository->countAllForToday()[0][1];
        $Connexion      = $this->logRepository->countAllConnexion()[0][1];
        $ConnexionInDay = $userRepository->countAllConnexionForToday()[0][1];
        $allUsers       = $userRepository->countAllUsers()[0][1];
        $allactiveUsers = $userRepository->countAllUsersByStatus(1)[0][1];
        $allactiveUsersDivisionByZero =  ($allactiveUsers == 0) ?  1 :  $allactiveUsers ;
        $monthConnexion = $this->logRepository->countAllForMonth($this->services->FirstAndLastDayOfMouth()['first_date'])[0][1];

        $logs           = [
            'countAllLogs'         => $all,
            'countDayAllLogs'      => $allInDay,
            'countAllDayConnexion' => $ConnexionInDay,
            'countAllConnexion'    => $Connexion,
            'countAllUsers'        => $allUsers,
            'countAllActiveUsers'  => $allactiveUsers,
            'monthConnexion'       => $monthConnexion,
            'monthConnexionP'      => ($allactiveUsers * 100 ) / $allactiveUsersDivisionByZero,
        ];

        return $logs;
    }

    #[Route('/view', name: 'app_logs')]
    public function index(TranslatorInterface $translator): Response
    {
        $logs =  $this->dashData();
        $this->services->addLog($this->intl->trans('Accès au logs : activités des utilisateurs'));
        return $this->render('log/index.html.twig', [
            'controller_name' => 'LogController',
            'pageTitle'       => [
                [$this->intl->trans('Log')],
            ],
            'logsData'        => $logs,
            'role'            =>  $this->roleRepository->findAll(),
            'title'           => $this->intl->trans('Logs').' - '. $this->brand->get()['name'],
            'menu_text'       => $this->intl->trans('Logs'),
            'brand'           => $this->brand->get(),
            'baseUrl'         => $this->baseUrl,
        ]);
    }

    #[Route('/list', name: 'app_getLogs')]
    public function getLogs(TranslatorInterface $translator, Request $request) : Response
    {
        $data = array();
        $logs = $this->logRepository->findBy([],["createdAt"=>"DESC"]);
        foreach ($logs  as $log) 
		{
            $logUser  = $log->getUser();
            $usetting = $log->getUser()->getUsetting();
            $row   = array();
            $row['OrderID']   = $log->getId();
            $row['Login']     = $logUser->getEmail();
            $row['Name']      = ($usetting->getFirstName()) ? $usetting->getFirstName().' '.$usetting->getLastName() : $this->intl->trans('Inconnu') ;
            $row['Action']    = $log->getTask();
            $row['Ip']        = $log->getIp();
            $row['Phone']     = ($logUser) ? $logUser->getPhone() : 'Inconnu';
            $row['Agent']     = $log->getAgent();
            $row['Role']      = ($logUser) ? $logUser->getRoles()[0] : 'Inconnu';
            $row['Status']       = $log->getStatus();
            $row['RegisterDate'] = $log->getCreatedAt()->setTimezone(new \DateTimeZone('+01:00'))->format("Y-m-d H:i:sP");
            $data []          = $row;
		}
        $this->services->addLog($this->intl->trans('Lecture du logs : activités des utilisateurs'));
        $output = array("data" => $data);
        return new JsonResponse($output);
    }
}
