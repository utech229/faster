<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\uBrand;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Service\AddEntity;
use App\Repository\UserRepository;
use App\Form\PasswordSettingFormType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/{_locale}/linker')]
class LinkSettingController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, Services $services, EntityManagerInterface $entityManager, TranslatorInterface $translator,
    UserRepository $userRepository, UrlGeneratorInterface $urlGenerator, uBrand $brand,  AddEntity $addEntity, ContactRepository $contactRepository,){
		$this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->userRepository  = $userRepository;
        $this->contactRepository  = $contactRepository;
        $this->addEntity          = $addEntity;
	}
    
    #[Route('/email_edit/{email}/{uid}/{token}', name: 'app_user_email_setting')]
    public function index(Request $request, Services $services, $email, $uid, $token): Response
    {
        if (!$this->isCsrfTokenValid($uid, $token)) { 
            $this->addFlash('warning', $this->intl->trans("Tentative frauduleuse de modification d'adresse email"));
            return $this->redirectToRoute("app_login");}
            
        $user = $this->userRepository->findOneByUid($uid);
        if($user->getActiveCode() == $user->getUsetting()->getUid()) {
            $email = base64_decode (".$email.");
            $user->setEmail($email);
            $user->setActiveCode(null);
            $user->setUpdatedAt(new \DatetimeImmutable());
            $this->userRepository->add($user);
            $this->addFlash('info', $this->intl->trans("Votre email à été modifié avec succès, utilisez le pour vous connecter"));
            return $this->redirectToRoute("app_home");
        }else {
            $this->addFlash('warning', $this->intl->trans("Votre lien de réinitialisation d'activation du nouvelle adresse email est expiré"));
            return $this->redirectToRoute("app_home");
        }
    }

    #[Route('/pass_resetting', name: 'app_password_resetting_new')]
    #[Route('/pass_resetting/{uid}/{code}', name: 'app_password_resetting')]
    public function password_resetting(Request $request, UserPasswordHasherInterface $userPasswordHasher, Services $services,  $uid = null, $code = null): Response
    {
        $user = new User();
        $form = $this->createForm(PasswordSettingFormType::class, $user);
        $form->handleRequest($request);
        if ($request->request->count() > 0 && $code == null)
        {
            
            $uid  = $request->request->get('user');
            $user = $this->userRepository->findOneBy(["uid" => $uid]);
            
            if ($form->isSubmitted() && $form->isValid()) {
                //email verify 
                $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
                $this->userRepository->add($user);
                $message = $this->intl->trans("Votre mot de passe à été modifié avec succès. Vous pouvez vous connecter à présent.");
                $this->addFlash('success', $message);
                return $this->services->msg_success(
                    $this->intl->trans("Réinitialisation du mot de passe"),
                    $message
                );
            }
        }
        else 
        {
            $user = $this->userRepository->findOneBy(["uid" => $uid]);
            if($user->getActiveCode() == $code) {
                $user->setActiveCode(null);
                $user->setUpdatedAt(new \DatetimeImmutable());
                $this->userRepository->add($user);

                $this->addFlash('info', $this->intl->trans("Veuillez saisir votre nouveau mot de passe"));
                return $this->render('registration/'.$this->brand->get()['regisform'], [
                    'title'           => $this->intl->trans('Mot de passe').' - '.$this->brand->get()['name'],
                    'menu_text'       => $this->intl->trans('Mot de passe'),
                    'brand'           => $this->brand->get(),
                    'baseUrl'         => $this->baseUrl->init(),
                    'user'            => $user,
                    'form'            => $form->createView(),
                ]);
            }
            else {
                $this->addFlash('warning', $this->intl->trans("Votre lien de réinitialisation d'activation du nouvelle adresse email est expiré"));
                return $this->redirectToRoute("app_home");
            }
        }
    }

    #[Route('/account_activation/{uid}/{code}', name: 'app_account_activation')]
    public function account_activation(Request $request,Services $services,  $uid = null, $code = null): Response
    {
        $user = $this->userRepository->findOneBy(["uid" => $uid]);

        if ($user->getStatus()->getCode() == 3) {
            $this->addFlash('success', $this->intl->trans("Compte actif, connectez vous avec succès"));
            return $this->redirectToRoute('app_login');
        }else {
            if($user->getActiveCode() == $code) {
                $user->setActiveCode(null);
                $user->setUpdatedAt(new \DatetimeImmutable());
                $user->setStatus($this->services->status(3));
                $this->userRepository->add($user);

                $this->addFlash('success', $this->intl->trans("Compte activé avec succès"));
                return $this->redirectToRoute('app_login');
            }
            else {
                $this->addFlash('warning', $this->intl->trans("Votre lien d'activation de compte est expiré"));
                return $this->redirectToRoute("app_login");
            }
        }
    }

    
    

}
