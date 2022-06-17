<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use Symfony\Component\Mime\Address;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier, StatusRepository $statusRepository)
    {
        $this->emailVerifier     = $emailVerifier;
        $this->statusRepository  = $statusRepository;
        $this->comptes = [
			['Owner' =>'','Operator'=>'','Phone'=>'','TransactionId'=>'','Country'=>'', 'Status'=>''],
			['Banque'=>'','Country'=>'','NAccount'=>'','Swift'=>'','DocID'=>'','DocRIB'=>''],
			['Owner' =>'','NBIN'=>'','CVV2'=>'','NAccount'=>'']
		];
    }

    #[Route('/{_locale}/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            // A commenter revoir lorsque l'envoi des mail est activé
            $user->setCreatedAt(new \DatetimeImmutable());
            $user->setBalance(0);
            $user->setPaymentAccount($this->comptes);
            $user->setApikey($this->services->idgenerate(32));
            $user->setUid(time().uniqid());
            $user->setIsAffiliate(true);
            $user->setIsVerified(true);
            $user->setPostPay(0);
            $user->setIsDlr(0);

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            /*$this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('support@upayapi.app', 'Support technic'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );*/
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/{_locale}/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_home');
    }
}
