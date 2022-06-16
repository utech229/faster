<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\Serializer\SerializerBuilder;

class Message extends AbstractController
{
    private $page1 = 160;
    private $page2 = 205;
    private $page3 = 357;

    public function trueLength($originMessage)
    {
        $message = $this->corrigeCarac($originMessage);

        return [true, $message, strlen($message)];
    }

    public function getAmountSMS($sms, $user, $phone)
    {
        return [true, 12.0, ""];
    }

    public function checkSender($manager, $sender)
    {
        try {
            if(is_string($sender))
            {
                foreach ($manager->getSenders() as $onSender) {
                    if($onSender->getName() == $sender) return $onSender;
                }
            }
                else if(is_object($sender) && $manager === $sender->getManager())
            {
                return $sender;
            }
        } catch (\Exception $e) {
            //
        }

        return null;
    }

    public function checkSendingAt($date_heure_send, $timezone)
    {
        $server_tz = date_default_timezone_get();

        $startAt = null;
        if($date_heure_send || $timezone){
            // Convertion de la date et heure du client eau time zone du server
            try {
                $startAt = new \DateTime($date_send." ".$heure_send." ".$timezone);
                $startAt->setTimezone(new \DateTimeZone($server_tz));

                return [true, $startAt];
            } catch (\Exception $e) {
                return [false, $startAt];
            }
        }else{
            return [true, $startAt];
        }
    }

    public function corrigeCarac($originMessage)
    {
        $message = "";
        for ($i=0; $i < strlen($originMessage); $i++) {
            switch ($originMessage[$i])
            {
                case 'à': $message += 'a'; break;
                case 'à': $message += 'a'; break;
                case 'â': $message += 'a'; break;
                case 'ä': $message += 'a'; break;
                case 'À': $message += 'A'; break;
                case 'Â': $message += 'A'; break;
                case 'Ä': $message += 'A'; break;
                case 'ç': $message += 'c'; break;
                case 'é': $message += 'e'; break;
                case 'ê': $message += 'e'; break;
                case 'ë': $message += 'e'; break;
                case 'è': $message += 'e'; break;
                case 'É': $message += 'E'; break;
                case 'È': $message += 'E'; break;
                case 'Ê': $message += 'E'; break;
                case 'Ë': $message += 'E'; break;
                default: $message += $originMessage[$i]; break;
            }
        }
        //&('"-è_)=~#{[|`\^@]}$ù*!:;,¤£¨%µ^%µ§/.?€`]}"')
        return $message;
    }
}
