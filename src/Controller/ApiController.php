<?php

namespace App\Controller;

use App\Entity\User;

use App\Service\APIResponse;
use App\Service\BrickPhone;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class ApiController extends AbstractController
{
    #[Route('/message/send', name: 'api_send')]
    public function created(Request $request)
    {
        $request = Request::createFromGlobals();

        // Vérifier l'entêtemp
        list($code, $token, $service) = $this->checkHeader($request->headers);
        if($code != 200) return (new APIResponse())->new(["error"=>"bad header", "status"=>$code], $code);

        // Vérification de la méthode
        $method = $request->getMethod();
        if($method != "POST" && $method != "GET") return (new APIResponse())->new(["error"=>"bad method", "status"=>405], 405);

        $sender = $request->get('sender', null);
        $message = $request->get('message', '');
        $phone_number = $request->get('phone', null);
        $internal_ref = $request->request->get('internal_ref', null);
        $process = $request->request->get('process', "");
        $expired = $request->request->get('expired', null);

        $msg = [];
        if(!$expired) $msg[] = "bad or no expired date";
        if(!$email) $msg[] = "bad or no email";
        if(!$lastname) $msg[] = "bad or no lastname";
        if(!$firstname) $msg[] = "bad or no firstname";
        if(!$internal_ref) $msg[] = "bad or no internal_ref";
        $country = $this->brick->getInfosCountryFromCode($phone_number);
        if(!$phone_number || !$country) $msg[] = "bad or no phone_number";
        if(!$this->router->checkAmount($amount, 100)) $msg[] = "bad or no amount";

        if($msg != []) return (new APIResponse())->new(["error"=>$msg, "status"=>403], 403);

        $expiredAt = (new \DateTimeImmutable($expired, new \DateTimeZone('GMT')))->modify('+120 min');

        $expiredAt->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        if(!$country) return (new APIResponse())->new(["error"=>"API error country", "status"=>403], 403);

        $process = (strtoupper($process) == "CARD")?"CARD":"MOBILE";

        $transation = new Transaction();
        $transation->setDescription($description)
            ->setIdTransaction("")
            ->setRefExterne("")
            ->setRefInterne($internal_ref)
            ->setUid($this->src->getUniqid())
            ->setOriginAmount($amount)
            ->setAmount($amount)
            ->setFinalAmount($amount)
            ->setAgregator("")
            ->setStatus(0)
            ->setFees(0)
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setEmail($email)
            ->setPhone($phone_number)
            ->setCurrency("XOF")
            ->setCurrencyChange(1)
            ->setCountry($country["code"])
            ->setProcess($process)
            ->setService($service)
            ->setExpiredAt($expiredAt)
            ->setEnv($environment)
            ->setCreatedAt(new \DateTimeImmutable())
        ;

        $this->em->persist($transation);
        //if($environment == "dev") $this->em->flush();

        list($new_transation, $response) = $this->router->processRouter($transation);

        if($response["status"] < 200 && $response["status"] > 399) return (new APIResponse())->new(["error"=>$response["api"]["message"], "status"=>500], 500);

        $this->em->persist($new_transation);
        $this->em->flush();

        return (new APIResponse())->new([
            "id_transaction"=>$new_transation->getUid(),
            "external_id"=>$new_transation->getIdTransaction(),
            "external_ref"=>$new_transation->getRefExterne(),
            "internal_ref"=>$new_transation->getRefInterne(),
            "amount"=>$new_transation->getOriginAmount(),
            "devise_change"=>$new_transation->getCurrencyChange(),
            "amount_devise"=>$new_transation->getAmount(),
            "amount_paie"=>$new_transation->getFinalAmount(),
            "status"=>$this->router->getStatusText($new_transation->getStatus()),
            "fees"=>$new_transation->getFees(),
            "firstname"=>$new_transation->getFirstName(),
            "lastname"=>$new_transation->getLastName(),
            "email"=>$new_transation->getEmail(),
            "phone_number"=>$new_transation->getPhone(),
            "payment_phone"=>($new_transation->getPhonePaie())?$new_transation->getPhonePaie():"-",
            "currency"=>$new_transation->getCurrency(),
            "country"=>$new_transation->getCountry(),
            "process"=>($new_transation->getProcess()=="CARD")?"CARD":"MOBILE",
            "operator"=>$new_transation->getProcess(),
            "service_key"=>$new_transation->getService()->getApikey(),
            "service_name"=>$new_transation->getService()->getName(),
            "expired"=>$new_transation->getExpiredAt()->format("c"),
            "description"=>$new_transation->getDescription(),
            "response"=>$response
        ], $response["status"]);
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

        if($auth_token == "") return [404, "", null];

        list($code, $service) = $this->checkUser($auth_token);

        return [$code, $auth_token, $service];
    }

    public function checkUser($token = null)
    {
        //$service = $this->em->getRepository(Service::class)->findOneBy(["name"=>$user, "uid"=>$psw, "apikey"=>$token]);
        $service = $this->em->getRepository(User::class)->findOneBy(["apikey"=>$token]);

        if(!$service) return [428, null];

        return [200, $service];
    }
}
