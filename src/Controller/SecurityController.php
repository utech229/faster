<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\DbInitData;
use App\Form\RegistrationFormType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/{_locale}/login')]
class SecurityController extends AbstractController
{
   
    public function __construct(BaseUrl $baseUrl, TranslatorInterface $intl, uBrand $brand,
    EntityManagerInterface $entityManager, UserRepository $userRepository, 
    RoleRepository $roleRepository, Services $services, StatusRepository $statusRepository)
    {
        $this->baseUrl       = $baseUrl->init();
        $this->em	         = $entityManager;
        $this->intl          = $intl;
        $this->brand         = $brand;
        $this->services      = $services;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->statusRepository  = $statusRepository;
    }

    #[Route(path: '', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }else {
            $user = new User();
            $form = $this->createForm(RegistrationFormType::class, $user);
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/'.$this->brand->get()['loginform'], 
        [
            'last_username' => $lastUsername, 
            'error' => $error,
            'title'           => $this->intl->trans('Connexion').' - '. $this->brand->get()['name'],
            'menu_text'       => $this->intl->trans('Connexion'),
            'brand'           => $this->brand->get(),
            'baseUrl'         => $this->baseUrl,
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
