<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Sender;
use App\Entity\Status;
use App\Entity\Brand;
use App\Entity\SMSCampaign;
use App\Entity\SMSMessageFile;
use App\Service\uBrand;
use App\Service\Services;
use App\Service\Message;
use App\Service\BrickPhone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('/{_locale}/sms/campaign')]
class SMSCampaignController extends AbstractController
{
    private $pathRacine = "campaign/";
    private $pathImport = "campaign/import/temp/";
    private $issetError = false;

    public function __construct(TranslatorInterface $intl, uBrand $brand, Services $src, UrlGeneratorInterface $ug, EntityManagerInterface $em, Message $sMessage, BrickPhone $brickPhone)
	{
       $this->intl          = $intl;
       $this->brand         = $brand;
       $this->src           = $src;
       $this->ug            = $ug;
       $this->em            = $em;
       $this->sMessage      = $sMessage;
       $this->brickPhone    = $brickPhone;
       $this->permission    = [
           "SMSC0", "SMSC1",  "SMSC2", "SMSC3", "SMSC4",
       ];
       $this->pAccess   =	$this->src->checkPermission($this->permission[0]);
       $this->pCreate   =	$this->src->checkPermission($this->permission[1]);
       $this->pList     =	$this->src->checkPermission($this->permission[2]);
       $this->pEdit	    =	$this->src->checkPermission($this->permission[3]);
       $this->pDelete	=	$this->src->checkPermission($this->permission[4]);
    }

    #[Route('', name: 'campaign_sms', methods: ['GET'])]
    public function index(): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            $this->src->addLog($this->intl->trans("Acces refusé à campagne"), 417);
            return $this->redirectToRoute("app_home");
        }

        list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

        $status = [
            $this->src->status(2),
            $this->src->status(3),
            $this->src->status(4),
        ];

        $params = [];

        $brands = $this->em->getRepository(Brand::class)->findBrandBy($userType, $userRequest);

        return $this->renderForm('smscampaign/index.html.twig', [
            'brands'    => $brands,
            'status'    => $status,
            'brand'     => $this->brand->get(),
            'pAccess'   => $this->pAccess,
            'pCreate'   => $this->pCreate,
            'clnUser'   => $userType > 3 ? false : true,
            'pList'     => $this->pList,
            'pEdit'     => $this->pEdit,
            'pDelete'   => $this->pDelete,
            'pageTitle' => []
        ]);
    }

    #[Route('/view/created', name: 'campaign_sms_view_created', methods: ['GET','POST'])]
    public function viewCreated(Request $request): Response
    {
        if(!$this->pAccess)
        {
            $this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisés à accéder à cette page !"));
            $this->src->addLog($this->intl->trans("Acces refusé à la création de campagne"), 417);
            return $this->redirectToRoute("campaign_sms");
        }

        list($userType, $masterId, $userRequest) = $this->src->checkThisUser($this->pList);

        $status = [
            $this->src->status(2),
            $this->src->status(3),
            $this->src->status(4),
        ];

        $params = [];

        $brands = $this->em->getRepository(Brand::class)->findBrandBy($userType, $userRequest);

        return $this->renderForm('smscampaign/new.html.twig', [
            'brands'    => $brands,
            'status'    => $status,
            'brand'     => $this->brand->get(),
            'pAccess'   => $this->pAccess,
            'pCreate'   => $this->pCreate,
            'clnUser'   => $userType > 3 ? false : true,
            'pList'     => $this->pList,
            'pEdit'     => $this->pEdit,
            'pDelete'   => $this->pDelete,
            'pageTitle' => []
        ]);
    }

    #[Route('/created', name: 'campaign_sms_created', methods: ['GET','POST'])]
    public function created(Request $request): Response
    {
        if(!$this->pCreate) return $this->src->no_access(
            $this->intl->trans("Acces refusé à la création de campagne."),
        );

        if (!$this->isCsrfTokenValid('campaign_create', $request->request->get('_token'))) return $this->src->no_access($this->intl->trans("Clé CSRF invalide."));

        $response = $this->createCampaign($request);

        if($response) return $response;

        return $this->redirectToRoute("campaign_sms");
    }

    #[Route('/import/file', name: 'smscampaign_import', methods: ['POST'])]
    public function importFile(Request $request)
    {
        /** @var UploadedFile $FILE */
        $FILE	=	$request->files->get('file');

        if(!isset($FILE)) return $this->src->msg_error(
            $this->intl->trans("Fichier d'importation pour une campagne échoué."),
            $this->intl->trans("Echèc de l'importation du fichier."),
            []
        );

        $return	= $this->src->checkFile($FILE, ["xls", "xlsx", "csv"], 1150028);

        if($return['error'] == true) {
            return $this->src->msg_error(
                $this->intl->trans("Erreur sur le fichier d'importation pour une campagne."),
                $return['info'],
                []
            );
        }

        $url = $this->src->renameFile($FILE, $this->pathImport, true, $this->pathImport, $this->src->getUniqid());

        return $this->src->msg_success(
            $this->intl->trans("Importation de fichier pour une campagne."),
            $this->intl->trans("Importation de fichier effectuée."),
            [
                "filename"=>$FILE->getFilename(),
                "url"=>$this->pathImport.$url,
            ]
        );
    }

    private function createCampaign($request)
    {
        $reqData = $request->request;

        $brand = $this->em->getRepository(Brand::class)->findOneByUid($reqData->get("brand"));

        if(!$brand) return $this->src->msg_error(
            $this->intl->trans("Echec lors de la création d'une campagne SMS. Erreur dans requête."),
            $this->intl->trans("La marque n'est pas indiquée."),
            [],
        );

        $user = $this->em->getRepository(User::class)->findOneBy([
            "uid"=>$reqData->get("user"),
            "brand"=>$brand,
        ]);

        if(!$user) return $this->src->msg_error(
            $this->intl->trans("Echec lors de la création d'une campagne SMS. Utilisateur %1% inconnu sous la marque %2%.", ["%1%"=>$reqData->get("user"), "%2%"=>$brand->getName()]),
            $this->intl->trans("Impossible de retrouver cet utilisateur."),
            [],
        );

        $sender = $this->em->getRepository(Sender::class)->findOneBy([
            "uid"=>$reqData->get("sender"),
            "manager"=>$user,
        ]);

        if(!$sender) return $this->src->msg_error(
            $this->intl->trans("Echec lors de la création d'une campagne SMS. L'identifiant %1% inconnu pour l'utilisateur %2%.", ["%1%"=>$reqData->get("sender"), "%2%"=>$user->getEmail()]),
            $this->intl->trans("Impossible de retrouver cet identifiant."),
            [],
        );

        $phones      = [];
        $programming = $this->src->status(0);
        $progressing = $this->src->status(1);
        $suspend     = $this->src->status(5);
        $amountFull  = 0;
        $dataError   = [];

        $name       = trim($reqData->get("name", null));
        $dateTime   = trim($reqData->get("datetime", null));
        $timezone   = trim($reqData->get("timezone", null));
        $type       = (int)$reqData->get("type", "1");
        $message    = trim($reqData->get("message", ""));

        if($dateTime == "" || $dateTime == null)
        {
            $dateTime = (new \DateTime("now"))->format("Y-m-d H:i");
            $timezone = (new \DateTime("now"))->format("P");
        }

        $dateTime   .= ":00";

        $fileUrl    = $reqData->get("fileUrl", null);

        switch (true) {
            case ($reqData->get("groups", "") != ""):
                $phones = $this->campaignByGroupContacts($reqData->get("groups", ""));
                break;
            case ($reqData->get("phones", "") != ""):
                $phones = $this->campaignByWriteNumbers($reqData->get("phones", ""));
                break;
            case ($reqData->get("fileUrl", "") != ""):
                $phones = $this->campaignByImportation($reqData->get("fileUrl", ""));
                break;
            default: break;
        }

        if($phones == []) return $this->src->msg_error(
            $this->intl->trans("Création de campagne échouée."),
            $this->intl->trans("Aucun contact trouvé."),
            []
        );

		$sendingAt = new \DateTimeImmutable($dateTime." ".$timezone);
		$sendingAt = $sendingAt->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        // $start = ((new \DateTimeImmutable())->diff($sendingAt))->invert;

        $campaign = new SMSCampaign();
        $campaign->setSendingAt($sendingAt)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(null)
            ->setMessage($message)
            ->setCampaignAmount(0)
            ->setSmsType($type)
            ->setIsParameterized(0)
            ->setTimezone($timezone)
            ->setManager($user)
            ->setSender($sender->getName())
            ->setStatus($suspend)
            ->setUid($this->src->getUniqid())
            ->setName($name)
            ->setCreateBy($this->getUser())
        ;

        $this->em->persist($campaign);
        $this->em->flush();

        $data = [];
        foreach ($phones as $key => $phone)
        {
            $errors = [];
            $statusCode = $programming->getCode();
            $phone_data = $this->brickPhone->getInfosCountryFromCode($phone["number"]);
            if(!$phone_data)
            {
                $errors[] = $this->intl->trans("Le n° de téléphone %1% indiqué est incorrect.", ["%1%"=>$phone["number"]]);
                $statusCode = $suspend->getCode();
            }

            $dataMessage = $this->sMessage->setParameters($message, $phone);
            if(!$dataMessage[0])
            {
                $errors[] = $this->intl->trans("Le message contient %1% caractères pour %2% page(s).", ["%1%"=>$dataMessage[2],"%2%"=>$dataMessage[3]]);
                $statusCode = $suspend->getCode();
            }

            $dataAmount = $this->sMessage->getAmountSMS($dataMessage[3], $user);
            if(!$dataAmount[0])
            {
                $errors[] = $dataAmount[2];
                $statusCode = $suspend->getCode();
            }

            $amountFull += $dataAmount[1];

            $data[] = [
                "sendingAt"         => $sendingAt->format("Y-d-m H:i:s P"),
                "createdAt"         => (new \DateTimeImmutable())->format("Y-d-m H:i:s P"),
                "updatedAt"         => null,
                "message"           => $dataMessage[1],
                "messageAmount"     => $dataAmount[1],
                "smsType"           => $type,
                "isParameterized"   => false,
                "timezone"          => $timezone,
                "manager"           => $user->getId(),
                "campaign"          => $campaign->getId(),
                "status"            => $statusCode,
                "sender"            => $sender->getName(),
                "phone"             => str_replace("+","",$phone["number"]),
                "phoneCountry"      => $phone_data,
                "uid"               => $this->src->getUniqid(),
                "originMessage"     => "",
                "createBy"          => $this->getUser()->getId(),
                "error"             => implode("; ", $errors),
                "contact"           => $phone
            ];

            if($statusCode == $suspend->getCode())
            {
                $dataError[] = [
                    count($data),
                    $phone["number"],
                    implode("; ", $errors),
                    $phone,
                    $message,
                    [count($data)-1, $campaign->getUid()],
                ];
            }
        }

        $chemin = $this->pathRacine.$brand->getName()."/".$user->getEmail()."/".$sender->getName()."/";

        if(!$chemin) mkdir($chemin, 0777, true);

        $fileName = (new \DateTimeImmutable())->format("YmdHis").".json";

        fopen($chemin.$fileName, "c+b");

        file_put_contents($chemin.$fileName, json_encode($data));

        if(count($errors) > 0) $campaign->setStatus($suspend);
        else $campaign->setStatus($programming);
        $campaign->setCampaignAmount($amountFull);
        $this->em->persist($campaign);

        $SMSMessageFile = new SMSMessageFile();
        $SMSMessageFile->setName($fileName)
            ->setUrl($chemin.$fileName)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(null)
            ->setCampaign($campaign)
        ;

        $this->em->persist($SMSMessageFile);
        // $this->em->flush();

        $nbrError = count($dataError);
        if($nbrError > 0)
        {
            return $this->src->msg_warning(
                $this->intl->trans("Création de la campagne %1%.", ["%1%"=>$name]),
                $this->intl->trans("Campagne créée, mais SUSPENDUE. %1% ligne(s) d'erreur trouvée(s).", ["%1%"=>$nbrError]),
                $dataError,
            );
        }

        return $this->src->msg_success(
            $this->intl->trans("Création de la campagne %1%.", ["%1%"=>$name]),
            $this->intl->trans("Campagne créée avec succès."),
        );
    }

    private function campaignByGroupContacts($groups)
    {
        return [];
    }

    private function campaignByWriteNumbers($phoneString)
    {
        $phonesData = [];
        $rows = ($phoneString == "" || $phoneString == null) ? [] : explode(";", $phoneString);
        $key = 0;
        for ($i=1; $i < count($rows); $i++) {
            $phonesData[$key]["number"] = $rows[$i];
            $phonesData[$key]["param1"] = "";
            $phonesData[$key]["param2"] = "";
            $phonesData[$key]["param3"] = "";
            $phonesData[$key]["param4"] = "";
            $phonesData[$key]["param5"] = "";
            $phonesData[$key]["param6"] = "";

            $key++;
        }

        //$spreadsheet->getCell('A1')->getValue();

        return $phonesData;
    }

    private function campaignByImportation($fileUrl)
    {
        if(!is_file($fileUrl)) return [];

        $phonesData = [];

        /**  Identify the type of $fileUrl  **/
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($fileUrl);
        /**  Create a new Reader of the type that has been identified  **/
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(true);
        /**  Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = $reader->load($fileUrl);

        $rows = $spreadsheet->getActiveSheet()->toArray();

        $key = 0;
        for ($i=0; $i < count($rows); $i++) {
            $phone = isset($rows[$i][0]) ? $rows[$i][0] : "";
            $phone_data = ($i == 0) ? $this->brickPhone->getInfosCountryFromCode($phone) : true;
            if($phone_data)
            {
                $phonesData[$key]["number"] = $phone;
                $phonesData[$key]["param1"] = isset($rows[$i][1]) ? $rows[$i][1] : "";
                $phonesData[$key]["param2"] = isset($rows[$i][2]) ? $rows[$i][2] : "";
                $phonesData[$key]["param3"] = isset($rows[$i][3]) ? $rows[$i][3] : "";
                $phonesData[$key]["param4"] = isset($rows[$i][4]) ? $rows[$i][4] : "";
                $phonesData[$key]["param5"] = isset($rows[$i][5]) ? $rows[$i][5] : "";
                $phonesData[$key]["param6"] = isset($rows[$i][6]) ? $rows[$i][6] : "";

                $key++;
            }
        }

        //$spreadsheet->getCell('A1')->getValue();

        return $phonesData;
    }
}
