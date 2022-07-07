<?php

namespace App\Service;

use App\Service\Services;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



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
        $email = (new TemplatedEmail())
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

    public function nativeSend(/*$from, $to = [], $subject,*/):Response
    {

        $message = '
     <html>
      <head>
       <title>Calendrier des anniversaires pour Août</title>
      </head>
      <body>
       <p>Voici les anniversaires à venir au mois d\'Août !</p>
       <table>
        <tr>
         <th>Personne</th><th>Jour</th><th>Mois</th><th>Année</th>
        </tr>
        <tr>
         <td>Josiane</td><td>3</td><td>Août</td><td>1970</td>
        </tr>
        <tr>
         <td>Emma</td><td>26</td><td>Août</td><td>1973</td>
        </tr>
       </table>
      </body>
     </html>
     ';

     //Sujet
     $subject = 'Calendrier des anniversaires pour Août';

        // Plusieurs destinataires
     $to  = 'enockiatk@gmail.com, enockidev@gmail.com'; // notez la virgule

     // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
     $headers[] = 'MIME-Version: 1.0';
     $headers[] = 'Content-type: text/html; charset=iso-8859-1';

     // En-têtes additionnels
     $headers[] = 'To: Mary <mary@example.com>, Kelly <kelly@example.com>';
     $headers[] = 'From: Anniversaire <anniversaire@example.com>';
     $headers[] = 'Cc: anniversaire_archive@example.com';
     $headers[] = 'Bcc: anniversaire_verif@example.com';

     // Envoi
     $send = mail($to, $subject, $message, implode("\r\n", $headers));
     return $send;
    }

    
}
