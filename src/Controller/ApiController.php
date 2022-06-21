<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\SMSMessage;

use App\Service\APIResponse;
use App\Service\BrickPhone;
use App\Service\Services;
use App\Service\Message;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/v1')]
class ApiController extends AbstractController
{
    public function __construct(TranslatorInterface $intl, EntityManagerInterface $em, Services $src)
    {
        $this->intl          = $intl;
        $this->em            = $em;
        $this->src           = $src;
    }

    #[Route('/sms/send', name: 'api_send')]
    public function created(Request $request, BrickPhone $brick, Message $sMessage)
    {
        $SMSMessage = new SMSMessage();

        $request = Request::createFromGlobals();

        // Vérifier l'entêtemp
        list($code, $token, $manager) = $this->checkHeader($request->headers);
        if($code != 200) return (new APIResponse())->new(["error"=>$this->intl->trans("Erreur dans l'entête de la requête."), "status"=>$code], $code);

        // Vérification de la méthode
        $method = $request->getMethod();
        if($method != "POST" && $method != "GET") return (new APIResponse())->new(["error"=>$this->intl->trans("Erreur sur la méthode d'envoi des données."), "status"=>405], 405);

        $sender = $request->get('from', null);
        $message = $request->get('text', '');
        $phone_number = $request->get('to', null);
        $date_heure_send = $request->get('dateTime', null); // format Y-m-d H:i:s
        $timezone = $request->get('timeZone', null);
        $mode = $request->get('mode', '1');
        $canal = $request->get('canal', 'SMS');

        $msg = [];

        $mySender = $sMessage->checkSender($manager, $sender);
        if($mySender == null) $msg[] = $this->intl->trans("Mauvais ou aucun identifiant d'envoi défini.");

        list($isValid, $newMessage, $length, $page) = $sMessage->trueLength($message);
        if(!$isValid) $msg[] = $this->intl->trans("Le nombre maximal de caractère est 350. Le message contient %1% caractère(s).", ["%1%"=>$length]);

        $phone_data = $brick->getInfosCountryFromCode($phone_number);
        if(!$phone_data) $msg[] = $this->intl->trans("Le numéro de téléphone indiqué est erroné.");

        list($isValid, $sendingAt) = $sMessage->checkSendingAt($date_heure_send, $timezone);
        if(!$isValid) $msg[] = $this->intl->trans("Mauvais format de DATE_HEURE ou TIMEZONE indiqué.");

        if((int)$mode !== 0) $mode = true; else $mode = false;

        if($msg != []) return (new APIResponse())->new(["error"=>$msg, "status"=>403], 403);

        list($valid, $price, $response) = $sMessage->getAmountSMS($newMessage, $manager, $phone_data);
        if(!$valid) return (new APIResponse())->new(["error"=>$response, "status"=>403], 403);

        if($sendingAt) $SMSMessage->setStatus($this->src->status(0));
        else $SMSMessage->setStatus($this->src->status(1));

        $SMSMessage
            ->setManager($manager)
            ->setUid($this->src->getUniqid())
            ->setCampaign(null)
            ->setSendingAt($sendingAt)
            ->setOriginMessage($message)
            ->setMessage($newMessage)
            ->setMessageAmount($price)
            ->setSmsType($mode)
            ->setIsParameterized(false)
            ->setTimezone($timezone ? $timezone : "")
            ->setSender($mySender)
            ->setPhone($phone_number)
            ->setPhoneCountry($phone_data)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(null)
        ;

        $this->em->persist($SMSMessage);

        $lastBalance = $manager->getBalance();
        $manager->setBalance($lastBalance - $SMSMessage->getMessageAmount());
        $this->em->persist($manager);

        $this->em->flush();

        return (new APIResponse())->new([
            "id_sending"=>$SMSMessage->getUid(),
            "amount_paie"=>$SMSMessage->getMessageAmount(),
            "status"=>strtoupper($SMSMessage->getStatus()->getName()),
            "balance"=>$SMSMessage->getManager()->getBalance(), // Vérifier si la récupération est instantanée
            "response"=>$response,
        ], 200);
    }

    #[Route('/account/balance', name: 'api_balance')]
    public function balance(Request $request)
    {
        $request = Request::createFromGlobals();

        // Vérifier l'entêtemp
        list($code, $token, $manager) = $this->checkHeader($request->headers);
        if($code != 200) return (new APIResponse())->new(["error"=>$this->intl->trans("Erreur dans l'entête de la requête."), "status"=>$code], $code);

        // Vérification de la méthode
        $method = $request->getMethod();
        if($method != "POST" && $method != "GET") return (new APIResponse())->new(["error"=>$this->intl->trans("Erreur sur la méthode d'evoie des données."), "status"=>405], 405);

        $balance = $manager()->getBalance();
        $notification = $manager->getSoldeNotification();
        $minSolde = $notification ? $notification->getMinSolde() : 0;

        $response = ($balance < $minSolde) ? $this->intl->trans("Votre solde est en dessous de la limite. Pensez à recharger votre compte.") : "";

        if($balance < 0) $this->intl->trans("Votre solde est négatif. Veuillez recharger votre compte.");

        return (new APIResponse())->new([
            "status"=>strtoupper($manager->getStatus()->getName()),
            "balance"=>$manager()->getBalance(),
            "response"=>$response,
        ], 200);
    }

    public function checkHeader($headers)
    {
        // retrieves an HTTP request header, with normalized, lowercase keys
        $accept = $headers->get('Accept');
        $content = $headers->get('Content-type');
        $auth_token = $headers->get('Authorization');
        $user = $headers->get('User-Agent');

        /*if($accept != "application/json" || $content != "application/json") return [406, "", $environment, null];/*/
        if(!$auth_token) return [401, "", null];

        $auth_token = trim(str_replace("Bearer","",$auth_token));
        //$to = $request->get('apikey', null);

        if($auth_token == "") return [404, "", null];

        list($code, $manager) = $this->checkUser($auth_token);

        return [$code, $auth_token, $manager];
    }

    public function checkUser($token = null)
    {
        //$manager = $this->em->getRepository(Service::class)->findOneBy(["name"=>$user, "uid"=>$psw, "apikey"=>$token]);
        $manager = $this->em->getRepository(User::class)->findOneBy(["apikey"=>$token]);

        if(!$manager) return [428, null];

        return [200, $manager];
    }
}
