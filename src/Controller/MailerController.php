<?php 

namespace App\Controller;

use App\Service\uBrand;
use App\Service\sMailer;
use App\Service\Services;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/{_locale}/mailer')]
class MailerController extends AbstractController
{
    public function __construct(TranslatorInterface $intl, uBrand $brand, Services $services, EntityManagerInterface $em, sMailer $mailer)
	{
		$this->intl			= $intl;
		$this->brand		= $brand;
		$this->services			= $services;
		$this->mailer			= $mailer;
		$this->em			= $em;
    }


    #[Route('/email', name: 'm_send_mail20', methods: ['POST', 'GET'])]
    public function sendEmail20(MailerInterface $mailer): JsonResponse
    {
        $memail = $this->mailer->nativeSend();
        return  new JsonResponse(["data" =>  $memail]);
        /*$email = (new Email())
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
        return  new JsonResponse(["data" =>  $memail]);
    
        // ...*/
    }
}