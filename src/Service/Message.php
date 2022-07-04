<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\Serializer\SerializerBuilder;

class Message extends AbstractController
{
	private $maxPage1 = 160;
	private $maxPage2 = 205;
	private $maxPage3 = 350;

	public function trueLength($originMessage)
	{
		$message = $this->corrigeCarac($originMessage);
		$length = strlen($message);
		$page = 0;
		switch (true) {
			case ($length == 0): $page = 0; break;
			case ($length <= $this->maxPage1): $page = 1; break;
			case ($length <= $this->maxPage2): $page = 2; break;
			case ($length <= $this->maxPage3): $page = 3; break;
			default: $page = 4; break;
		}
		return [
			($page > 0 && $page < 4) ? true : false,
			$message, $length, $page
		];
	}

	public function getAmountSMS($page, $user, $code)
	{
		if(!$user || !is_int($page)) return [false, 0, "Check signatures"];

		$prices = $user->getPrice();

		if(!isset($prices[$code])) return [false, 0, "Not config for this country"];

		return [true, $page * (float)($prices[$code]["price"]), ""];
	}

	public function checkSender($manager, $sender)
	{
		try {
			foreach ($manager->getSenders() as $onSender) {
				if($onSender->getName() == $sender) return $onSender;
			}
		} catch (\Exception $e) {
		}
		return null;
	}

	public function checkSendingAt($date_heure_send, $timezone)
	{
		$server_tz = date_default_timezone_get();

		$startAt = new \DateTime();
		if($date_heure_send || $timezone){
			// Convertion de la date et heure du client eau time zone du server
			try {
				$startAt = new \DateTime($date_heure_send." ".$timezone);
				$startAt->setTimezone(new \DateTimeZone($server_tz));
			} catch (\Exception $e) {
				return [false, $startAt];
			}
		}
		return [true, $startAt];
	}

	public function corrigeCarac($originMessage)
	{
		$str = str_replace(['<br>', '<br/>', '<br />', "\n", "\r"], [' ', ' ', ' ', ' ', ' '], $originMessage );
		$str = preg_replace('#<[^>]*>#', '', $str );
		$str = htmlentities($str, ENT_NOQUOTES, 'utf-8');
		$str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
		$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
		$str = preg_replace('#&[^;]+;#', '', $str);

		return $str;
	}

	public function setParameters($message, $contact)
	{
		$keyword = array(
			'/{param1}/' => $contact["param1"],
			'/{param2}/' => $contact["param2"],
			'/{param3}/' => $contact["param3"],
			'/{param4}/' => $contact["param4"],
			'/{param5}/' => $contact["param5"],
		);
		$sms = preg_replace(array_keys($keyword), array_values($keyword), $message);
		return $this->trueLength($sms);
	}
}
