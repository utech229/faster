<?php

namespace App\Security;

use App\Service\uBrand;
use App\Service\AddLogs;
use App\Service\BaseUrl;
use App\Service\Services;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;


class UserAuthenticator extends AbstractAuthenticator //AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator, BaseUrl $baseUrl, TranslatorInterface $intl, uBrand $brand,
    EntityManagerInterface $entityManager, UserRepository $userRepository, StatusRepository $statusRepository,
    RoleRepository $roleRepository, Services $services)
     {
         $this->urlGenerator = $urlGenerator;
         $this->baseUrl       = $baseUrl->init();
         $this->em	         = $entityManager;
         $this->intl          = $intl;
         $this->brand         = $brand;
         $this->userRepository = $userRepository;
         $this->roleRepository = $roleRepository;
         $this->statusRepository = $statusRepository;
         $this->services = $services;
     }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'app_login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $user = $this->userRepository->findOneBy(['brand' => $this->brand->get()['brand'], 'email' => $email]);// find a user based on an "email" form field
        if (!$user) {
            throw new CustomUserMessageAuthenticationException($this->intl->trans("Vos informations de connexion sont 
            inexistantes, veuillez vérifier et réessayer"));
        }else {
            switch ($user->getStatus()->getCode()) {
                case 7:
                    throw new CustomUserMessageAuthenticationException($this->intl->trans('Vos identifiants ont été banis du système, 
                    vous devez créer un nouveau compte avec de nouvel identifiant'));
                    break;
                case 5:
                    throw new CustomUserMessageAuthenticationException($this->intl->trans('Oups ! Votre compte est suspendu pour le moment. 
                    Veuillez contacter l\'administrateur du système.'));
                    break;
                case 4:
                    throw new CustomUserMessageAuthenticationException($this->intl->trans('Oups ! Vous avez désactivé votre compte,
                    veuillez lancer le processus de réactivation du compte'));
                    break;
                case 0:
                    throw new CustomUserMessageAuthenticationException($this->intl->trans('Oups ! Vous avez désactivé votre compte,
                    veuillez lancer le processus de réactivation du compte'));
                    break;
                
                default:
                    break;
            }
        }

        $request->getSession()->set(Security::LAST_USERNAME, $user->getUid());

        return new Passport(
            new UserBadge($user->getUid()),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    /**
     * Called when authentication executed, but failed (e.g. wrong username password).
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the login page or a 403 response.
     *
     * If you return null, the request will continue, but the user will
     * not be authenticated. This is probably not what you want to do.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = $exception ? $this->translator->trans('Identifiants de connexion incorrects')/*$exception->getMessage()*/ : $this->translator->trans('Missing credentials');
        $request->getSession()->getFlashBag()->add('error', $message);
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}
