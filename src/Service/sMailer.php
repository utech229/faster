<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Services;
use Symfony\Contracts\Translation\TranslatorInterface;



class sMailer extends AbstractController
{
    protected $brand;
   
	public function __construct(TranslatorInterface $intl,Services $services, MailerInterface $mailer)
	{
       $this->intl    = $intl;
       $this->services = $services;
       $this->mailer = $mailer;
    }

    public function send()
    {
        $mailer = $this->mailer;
        $email = (new Email())
            ->from('support@zekin.app')
            ->to('enockiatk@gmail.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $send =  $mailer->send($email);
        return $send;
    }

    
}
