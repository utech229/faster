<?php 

namespace App\Controller;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/{_locale}/mailer')]
class MailerController extends AbstractController
{
    #[Route('/email', name: 'm_send_mail20', methods: ['POST', 'GET'])]
    public function sendEmail20(MailerInterface $mailer): JsonResponse
    {
        $email = (new Email())
            ->from('hello@example.com')
            ->to('urbantech229@gmail.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!');
            //->html('<p>See Twig integration for better HTML integration!</p>');

        $memail =  $mailer->send($email);
        return $this->services->msg_success(
            $this->intl->trans("Mail"),
            $memail
        );

        // ...
    }
}