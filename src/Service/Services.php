<?php

namespace App\Service;

use App\Entity\Log;
use App\Entity\User;
use App\Entity\Permission;
use App\Entity\Authorization;

use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class Services extends AbstractController
{
	private $urlGenerator;
	private $translator;
    private $em;

	public function __construct(
		UrlGeneratorInterface $urlGenerator,
		TranslatorInterface $Translator,
        EntityManagerInterface $em, StatusRepository $statusRepository,
	){
		$this->urlGenerator	    = $urlGenerator;
        $this->intl   = $Translator;
		$this->em	  = $em;
		$this->statusRepository	= $statusRepository;
	}

	/**
     * Summary of getUniqid
     * @return string
     * Uniqid avec préfix par rand()
     */
    public function getUniqid(){
        $a_z = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
        return uniqid(rand(0,99).$a_z[rand(0,25)]);
    }

	// Fonction pour traiter les fichiers par post d'un formulaire
    public function checkFile(object $file, array $ext_accept = [], int $length_accept = 1000000)
	{
        $result['error'] = true;
        $result['info'] = "";

        if($file->getError() == 0)
        {
            $extention = pathinfo($file->getClientOriginalName(),PATHINFO_EXTENSION);
            if(in_array($extention, $ext_accept))
            {
                $length = pathinfo($file->getSize(), PATHINFO_BASENAME);
				if($length > $length_accept){
					$result['info']		=	$this->intl->trans("Le fichier est trop gros.");
				} else {
					$result['error']	= false;
				}

            }else $result['info']	=	$this->intl->trans("Type de fichier inconnu ou non autorisé.");
        }else{
            $result['info']	=	$this->intl->trans("Une erreur s'est produite lors de l'envoi du fichier.");
        }

        return $result;
    }

    // Vérifie l'existance d'un fichier et renomme le fichier si neccessaire en ajoutant un numéro au début
    public function renameFile(object $file, string $checkPlace = "", bool $erase = false, string $returnPlace = "", string $newName = "")
	{
        $newFilename = $newName.'.'.$file->guessExtension();
        if($erase == false){
            $is = file_exists($checkPlace.$newFilename);
            $i = 0;
            $filename = $newFilename;
            while ($is == true) {
                $i++;
                $filename = $i.'_'.$newFilename;
                $is = file_exists($checkPlace.$filename);
            }
            $newFilename = $filename;
        }

        if ($file->move($checkPlace,$newFilename)) {
            return $newFilename;
        }else
            return false;
    }

    public function removeFile($link_param, $filename)
    {
        //deleting of existe file
        try {
            $filesystem = new Filesystem();
            $filesystem->remove(['symlink', $link_param.'/'.$filename, 'activity.log']);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
            return false;
        }
        return true;
    }

	// Fonction pour vérifier le contenu d'une variable
    public function checkInput(mixed $input, bool $erroInfo = true, bool $notEmpty = true, bool $notNull = true, bool $isNumeric = false, int $minValue = 0, int $maxValue = 0, int $minLength = 0, int $maxLength = 0, array $notValue = [])
	{
        $error = 0;

        if($notEmpty == true)
            if(str_replace("^ .-_/\$", "", $input) == "") $error = 1;

        if($notNull == true)
            if($input === null) $error = 2;

        if($isNumeric == true)
            if(!ctype_digit($input)) $error = 3;

		if($minValue > 0 && $isNumeric == true && $error != 3)
			if((int)$input < $minValue) $error = 4;

		if($maxValue > 0 && $isNumeric == true && ($error != 3 || $error != 4))
			if((int)$input > $maxValue) $error = 5;

        if($minLength > 0)
            if(strlen($input) < $minLength) $error = 6;

        if($maxLength > 0)
            if(strlen($input) > $maxLength) $error = 7;

        for($i = 0; $i < count($notValue); $i++){
            if($notValue[$i] === $input) $error = 8;
        }

        if($erroInfo == true){
            switch ($error) {
                case 1:
                    $error = $this->intl->trans('vide');
                    break;

                case 2:
                    $error = $this->intl->trans('null');
                    break;

                case 3:
                    $error = $this->intl->trans('pas une numérique');
                    break;

                case 4:
                    $error = $this->intl->trans('doit être suppérieur ou égal à')." ".$minValue;
                    break;

                case 5:
                    $error = $this->intl->trans('doit être inférieur ou égal à')." ".$maxValue;
                    break;

                case 6:
                    $error = $this->intl->trans('trop court');
                    break;

                case 7:
                    $error = $this->intl->trans('trop longue');
                    break;

                case 8:
                    $error = $this->intl->trans("n'est pas disponible");
                    break;

                default:
                    $error = "";
                    break;
            }
        }

        return $error;
    }

	/** Function for add logs */
    public function addLog($task, $status = 200)
    {
        $user = $this->getUser();
        $log = new Log();
        $log->setUser($user);
        $log->setCreatedAt(new \DateTimeImmutable());
        $log->setIp($_SERVER['REMOTE_ADDR']);
        $log->setAgent($_SERVER['HTTP_USER_AGENT']);
		$log->setTask($task);
        $log->setStatus($status);
        $this->em->persist($log);
        $this->em->flush();
        return true;
    }

    /**
    * @param mixed $codePermission
    * codePermission peut être une chaine ou un tableau de chaine
	* @param User|null $user
    * @param bool $granted
	* true : vérification strict losque $codePermission est un tableau
    * @return mixed
    * Retourne le status de la dernière permission trouvée
    */
	public function checkPermission($codePermission, $user = null, $granted = true)
    {
		if (!$user) $user	=	$this -> getUser();
		if (!$user) return 0;

		if(is_array($codePermission)){
			$permission = null;
			foreach ($codePermission as $code) {
				$permission = $this->em->getRepository(Authorization::class)->findByCodePermission($code, $user->getId());

				if($granted && !$permission) return 0;
				else if(!$granted && $permission) return 1;
			}
		}else
			$permission = $this->em->getRepository(Authorization::class)->findByCodePermission($codePermission, $user->getId());

		return $permission ? 1 : 0;
    }

	// $level == 1 (Uniquement les sous marques) $level == 2 (Uniquement les Affiliés) $level == 0 || autres (Tous)
	public function getUserByPermission($codePermission, $userType = null, $user = null, $level = null)
	{
		$allUser = $this->em->getRepository(User::class)->getUsersByPermission(
			$codePermission, $userType, $user, $level
		);

		return $allUser;
	}

     //Notification function for forms errors
    public function formErrorsNotification($validator, $validationObject)
    {
        $errorsList = $validator->validate($validationObject);
        if (count($errorsList) > 0)
        {
            $errorsArray = array();
            foreach ($errorsList as $error) {
                $champ         = $error->getInvalidValue();
                $errorsArray[] = $champ .' : '.str_replace([".", ","], "", $error->getMessage()).' ';
            }
            return new JsonResponse(['message' => str_replace([","], "", $errorsArray),'title' => $this->intl->trans('Donnée(s) invalide(s)'),'type' =>'error', 'status' =>'error', 200]);
        }
    }

    public function invalidForm($form)
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error)
        {
            $errors = (string)$error->getMessage();
        }
        $response = new JsonResponse(['message' => $errors,
        'title' => $this->intl->trans('Formulaire invalide'), 'type' => 'error', 'status' =>'error', 200]);
        return $response;
    }

    //en attente de cas d'utilisation
    public function failedcrud($task){
        $this->addLog($task, 400);
        return new JsonResponse(['message' => $this->intl->trans("Votre opération à échoué, veuillez réessayer"),
        'title' => $this->intl->trans('Attention'), 'type' => 'error', 'status' =>'error', 401]);
    }

    //return inaccessible message to user when he hasn't permission to acess on the page or manage data
    public function no_access($task){
        $this->addLog($task, 417);
        return new JsonResponse(['message' => $this->intl->trans("Vous n'avez pas les accès requis pour traiter ces données"),
        'title' => $this->intl->trans('Alert sécurité'), 'type' => 'error', 'status' =>'error', 417]);
    }

    public function msg_warning($task , $message , $data = null){
        $this->addLog($task, 400);
        return new JsonResponse(['message' => $message,
        'title' => $this->intl->trans('Attention'), 'type' => 'warning', 'status' =>'warning', 'data' => $data, 400]);
    }

    public function msg_info($task , $message , $data = null){
        $this->addLog($task, 200);
        return new JsonResponse(['message' => $message,
        'title' => $this->intl->trans('Attention'), 'type' => 'info', 'status' =>'info', 'data' => $data, 400]);
    }

    public function msg_error($task , $message, $data = null){
        $this->addLog($task, 400);
        return new JsonResponse(['message' => $message,
        'title' => $this->intl->trans('Erreur'), 'type' => 'error', 'status' =>'error','data' => $data,  400]);
    }


    public function msg_success($task , $message, $data = null){
        $this->addLog($task, 200);
        return new JsonResponse([
            'message' => $message,'title'  => $this->intl->trans('Opération réussie'), 'type' => 'success',
            'status' =>'success','data' => $data, 200]);
    }

    //test function
    public function price(){
        return [
            ["price" => "null","name" => "null","dial_code" => "null","code" => "null"],["price" => "0","name" => "Afghanistan","dial_code" => "+93","code" => "AF"],
            ["price" => "0","name" => "Aland Islands","dial_code" => "+358","code" => "AX"],["price" => "0","name" => "Albania","dial_code" => "+355","code" => "AL"],
            ["price" => "0","name" => "Algeria","dial_code" => "+213","code" => "DZ"],["price" => "0","name" => "AmericanSamoa","dial_code" => "+1684","code" => "AS"],
            ["price" => "0","name" => "Andorra","dial_code" => "+376","code" => "AD"],["price" => "0","name" => "Angola","dial_code" => "+244","code" => "AO"],["price" => "0","name" => "Anguilla","dial_code" => "+1264","code" => "AI"],["price" => "0","name" => "Antarctica","dial_code" => "+672","code" => "AQ"],["price" => "0","name" => "Antigua and Barbuda","dial_code" => "+1268","code" => "AG"],["price" => "0","name" => "Argentina","dial_code" => "+54","code" => "AR"],["price" => "0","name" => "Armenia","dial_code" => "+374","code" => "AM"],["price" => "0","name" => "Aruba","dial_code" => "+297","code" => "AW"],["price" => "0","name" => "Australia","dial_code" => "+61","code" => "AU"],["price" => "0","name" => "Austria","dial_code" => "+43","code" => "AT"],["price" => "0","name" => "Azerbaijan","dial_code" => "+994","code" => "AZ"],["price" => "0","name" => "Bahamas","dial_code" => "+1242","code" => "BS"],["price" => "0","name" => "Bahrain","dial_code" => "+973","code" => "BH"],["price" => "0","name" => "Bangladesh","dial_code" => "+880","code" => "BD"],["price" => "0","name" => "Barbados","dial_code" => "+1246","code" => "BB"],["price" => "0","name" => "Belarus","dial_code" => "+375","code" => "BY"],["price" => "0","name" => "Belgium","dial_code" => "+32","code" => "BE"],["price" => "0","name" => "Belize","dial_code" => "+501","code" => "BZ"],["price" => "8","name" => "Benin","dial_code" => "+229","code" => "BJ"],["price" => "0","name" => "Bermuda","dial_code" => "+1441","code" => "BM"],["price" => "0","name" => "Bhutan","dial_code" => "+975","code" => "BT"],["price" => "0","name" => "Bolivia, Plurinational State of","dial_code" => "+591","code" => "BO"],["price" => "0","name" => "Bosnia and Herzegovina","dial_code" => "+387","code" => "BA"],["price" => "0","name" => "Botswana","dial_code" => "+267","code" => "BW"],["price" => "0","name" => "Brazil","dial_code" => "+55","code" => "BR"],["price" => "0","name" => "British Indian Ocean Territory","dial_code" => "+246","code" => "IO"],["price" => "0","name" => "Brunei Darussalam","dial_code" => "+673","code" => "BN"],["price" => "0","name" => "Bulgaria","dial_code" => "+359","code" => "BG"],["price" => "20","name" => "Burkina Faso","dial_code" => "+226","code" => "BF"],["price" => "0","name" => "Burundi","dial_code" => "+257","code" => "BI"],["price" => "0","name" => "Cambodia","dial_code" => "+855","code" => "KH"],["price" => "0","name" => "Cameroon","dial_code" => "+237","code" => "CM"],["price" => "0","name" => "Canada","dial_code" => "+1","code" => "CA"],["price" => "0","name" => "Cape Verde","dial_code" => "+238","code" => "CV"],["price" => "0","name" => "Cayman Islands","dial_code" => "+ 345","code" => "KY"],["price" => "0","name" => "Central African Republic","dial_code" => "+236","code" => "CF"],["price" => "0","name" => "Chad","dial_code" => "+235","code" => "TD"],["price" => "0","name" => "Chile","dial_code" => "+56","code" => "CL"],["price" => "0","name" => "China","dial_code" => "+86","code" => "CN"],["price" => "0","name" => "Christmas Island","dial_code" => "+61","code" => "CX"],["price" => "0","name" => "Cocos (Keeling) Islands","dial_code" => "+61","code" => "CC"],["price" => "0","name" => "Colombia","dial_code" => "+57","code" => "CO"],["price" => "0","name" => "Comoros","dial_code" => "+269","code" => "KM"],["price" => "0","name" => "Congo","dial_code" => "+242","code" => "CG"],["price" => "0","name" => "Congo, The Democratic Republic of the Congo","dial_code" => "+243","code" => "CD"],["price" => "0","name" => "Cook Islands","dial_code" => "+682","code" => "CK"],["price" => "0","name" => "Costa Rica","dial_code" => "+506","code" => "CR"],["price" => "25","name" => "Cote d'Ivoire","dial_code" => "+225","code" => "CI"],["price" => "0","name" => "Croatia","dial_code" => "+385","code" => "HR"],["price" => "0","name" => "Cuba","dial_code" => "+53","code" => "CU"],["price" => "0","name" => "Cyprus","dial_code" => "+357","code" => "CY"],["price" => "0","name" => "Czech Republic","dial_code" => "+420","code" => "CZ"],["price" => "0","name" => "Denmark","dial_code" => "+45","code" => "DK"],["price" => "0","name" => "Djibouti","dial_code" => "+253","code" => "DJ"],["price" => "0","name" => "Dominica","dial_code" => "+1767","code" => "DM"],["price" => "0","name" => "Dominican Republic","dial_code" => "+1849","code" => "DO"],["price" => "0","name" => "Ecuador","dial_code" => "+593","code" => "EC"],["price" => "0","name" => "Egypt","dial_code" => "+20","code" => "EG"],["price" => "0","name" => "El Salvador","dial_code" => "+503","code" => "SV"],["price" => "0","name" => "Equatorial Guinea","dial_code" => "+240","code" => "GQ"],["price" => "0","name" => "Eritrea","dial_code" => "+291","code" => "ER"],["price" => "0","name" => "Estonia","dial_code" => "+372","code" => "EE"],["price" => "0","name" => "Ethiopia","dial_code" => "+251","code" => "ET"],["price" => "0","name" => "Falkland Islands (Malvinas)","dial_code" => "+500","code" => "FK"],["price" => "0","name" => "Faroe Islands","dial_code" => "+298","code" => "FO"],["price" => "0","name" => "Fiji","dial_code" => "+679","code" => "FJ"],["price" => "0","name" => "Finland","dial_code" => "+358","code" => "FI"],["price" => "0","name" => "France","dial_code" => "+33","code" => "FR"],["price" => "0","name" => "French Guiana","dial_code" => "+594","code" => "GF"],["price" => "0","name" => "French Polynesia","dial_code" => "+689","code" => "PF"],["price" => "0","name" => "Gabon","dial_code" => "+241","code" => "GA"],["price" => "0","name" => "Gambia","dial_code" => "+220","code" => "GM"],["price" => "0","name" => "Georgia","dial_code" => "+995","code" => "GE"],["price" => "0","name" => "Germany","dial_code" => "+49","code" => "DE"],["price" => "0","name" => "Ghana","dial_code" => "+233","code" => "GH"],["price" => "0","name" => "Gibraltar","dial_code" => "+350","code" => "GI"],["price" => "0","name" => "Greece","dial_code" => "+30","code" => "GR"],["price" => "0","name" => "Greenland","dial_code" => "+299","code" => "GL"],["price" => "0","name" => "Grenada","dial_code" => "+1473","code" => "GD"],["price" => "0","name" => "Guadeloupe","dial_code" => "+590","code" => "GP"],["price" => "0","name" => "Guam","dial_code" => "+1671","code" => "GU"],["price" => "0","name" => "Guatemala","dial_code" => "+502","code" => "GT"],["price" => "0","name" => "Guernsey","dial_code" => "+44","code" => "GG"],["price" => "0","name" => "Guinea","dial_code" => "+224","code" => "GN"],["price" => "0","name" => "Guinea-Bissau","dial_code" => "+245","code" => "GW"],["price" => "0","name" => "Guyana","dial_code" => "+595","code" => "GY"],["price" => "0","name" => "Haiti","dial_code" => "+509","code" => "HT"],["price" => "0","name" => "Holy See (Vatican City State)","dial_code" => "+379","code" => "VA"],["price" => "0","name" => "Honduras","dial_code" => "+504","code" => "HN"],["price" => "0","name" => "Hong Kong","dial_code" => "+852","code" => "HK"],["price" => "0","name" => "Hungary","dial_code" => "+36","code" => "HU"],["price" => "0","name" => "Iceland","dial_code" => "+354","code" => "IS"],["price" => "0","name" => "India","dial_code" => "+91","code" => "IN"],
            ["price" => "0","name" => "Indonesia","dial_code" => "+62","code" => "ID"],["price" => "0","name" => "Iran, Islamic Republic of Persian Gulf","dial_code" => "+98","code" => "IR"],["price" => "0","name" => "Iraq","dial_code" => "+964","code" => "IQ"],["price" => "0","name" => "Ireland","dial_code" => "+353","code" => "IE"],["price" => "0","name" => "Isle of Man","dial_code" => "+44","code" => "IM"],["price" => "0","name" => "Israel","dial_code" => "+972","code" => "IL"],["price" => "0","name" => "Italy","dial_code" => "+39","code" => "IT"],["price" => "0","name" => "Jamaica","dial_code" => "+1876","code" => "JM"],["price" => "0","name" => "Japan","dial_code" => "+81","code" => "JP"],["price" => "0","name" => "Jersey","dial_code" => "+44","code" => "JE"],["price" => "0","name" => "Jordan","dial_code" => "+962","code" => "JO"],["price" => "0","name" => "Kazakhstan","dial_code" => "+77","code" => "KZ"],["price" => "0","name" => "Kenya","dial_code" => "+254","code" => "KE"],["price" => "0","name" => "Kiribati","dial_code" => "+686","code" => "KI"],["price" => "0","name" => "Korea, Democratic People's Republic of Korea","dial_code" => "+850","code" => "KP"],["price" => "0","name" => "Korea, Republic of South Korea","dial_code" => "+82","code" => "KR"],["price" => "0","name" => "Kuwait","dial_code" => "+965","code" => "KW"],["price" => "0","name" => "Kyrgyzstan","dial_code" => "+996","code" => "KG"],["price" => "0","name" => "Laos","dial_code" => "+856","code" => "LA"],["price" => "0","name" => "Latvia","dial_code" => "+371","code" => "LV"],["price" => "0","name" => "Lebanon","dial_code" => "+961","code" => "LB"],["price" => "0","name" => "Lesotho","dial_code" => "+266","code" => "LS"],["price" => "0","name" => "Liberia","dial_code" => "+231","code" => "LR"],["price" => "0","name" => "Libyan Arab Jamahiriya","dial_code" => "+218","code" => "LY"],["price" => "0","name" => "Liechtenstein","dial_code" => "+423","code" => "LI"],["price" => "0","name" => "Lithuania","dial_code" => "+370","code" => "LT"],["price" => "0","name" => "Luxembourg","dial_code" => "+352","code" => "LU"],["price" => "0","name" => "Macao","dial_code" => "+853","code" => "MO"],["price" => "0","name" => "Macedonia","dial_code" => "+389","code" => "MK"],["price" => "0","name" => "Madagascar","dial_code" => "+261","code" => "MG"],["price" => "0","name" => "Malawi","dial_code" => "+265","code" => "MW"],["price" => "0","name" => "Malaysia","dial_code" => "+60","code" => "MY"],["price" => "0","name" => "Maldives","dial_code" => "+960","code" => "MV"],["price" => "0","name" => "Mali","dial_code" => "+223","code" => "ML"],["price" => "0","name" => "Malta","dial_code" => "+356","code" => "MT"],
            ["price" => "0","name" => "Marshall Islands","dial_code" => "+692","code" => "MH"],["price" => "0","name" => "Martinique","dial_code" => "+596","code" => "MQ"],["price" => "0","name" => "Mauritania","dial_code" => "+222","code" => "MR"],["price" => "0","name" => "Mauritius","dial_code" => "+230","code" => "MU"],["price" => "0","name" => "Mayotte","dial_code" => "+262","code" => "YT"],["price" => "0","name" => "Mexico","dial_code" => "+52","code" => "MX"],["price" => "0","name" => "Micronesia, Federated States of Micronesia","dial_code" => "+691","code" => "FM"],["price" => "0","name" => "Moldova","dial_code" => "+373","code" => "MD"],["price" => "0","name" => "Monaco","dial_code" => "+377","code" => "MC"],["price" => "0","name" => "Mongolia","dial_code" => "+976","code" => "MN"],["price" => "0","name" => "Montenegro","dial_code" => "+382","code" => "ME"],["price" => "0","name" => "Montserrat","dial_code" => "+1664","code" => "MS"],["price" => "0","name" => "Morocco","dial_code" => "+212","code" => "MA"],["price" => "0","name" => "Mozambique","dial_code" => "+258","code" => "MZ"],["price" => "0","name" => "Myanmar","dial_code" => "+95","code" => "MM"],["price" => "0","name" => "Namibia","dial_code" => "+264","code" => "NA"],["price" => "0","name" => "Nauru","dial_code" => "+674","code" => "NR"],["price" => "0","name" => "Nepal","dial_code" => "+977","code" => "NP"],["price" => "0","name" => "Netherlands","dial_code" => "+31","code" => "NL"],
            ["price" => "0","name" => "Netherlands Antilles","dial_code" => "+599","code" => "AN"],["price" => "0","name" => "New Caledonia","dial_code" => "+687","code" => "NC"],["price" => "0","name" => "New Zealand","dial_code" => "+64","code" => "NZ"],["price" => "0","name" => "Nicaragua","dial_code" => "+505","code" => "NI"],["price" => "0","name" => "Niger","dial_code" => "+227","code" => "NE"],["price" => "0","name" => "Nigeria","dial_code" => "+234","code" => "NG"],["price" => "0","name" => "Niue","dial_code" => "+683","code" => "NU"],["price" => "0","name" => "Norfolk Island","dial_code" => "+672","code" => "NF"],["price" => "0","name" => "Northern Mariana Islands","dial_code" => "+1670","code" => "MP"],["price" => "0","name" => "Norway","dial_code" => "+47","code" => "NO"],["price" => "0","name" => "Oman","dial_code" => "+968","code" => "OM"],["price" => "0","name" => "Pakistan","dial_code" => "+92","code" => "PK"],["price" => "0","name" => "Palau","dial_code" => "+680","code" => "PW"],["price" => "0","name" => "Palestinian Territory, Occupied","dial_code" => "+970","code" => "PS"],["price" => "0","name" => "Panama","dial_code" => "+507","code" => "PA"],["price" => "0","name" => "Papua New Guinea","dial_code" => "+675","code" => "PG"],["price" => "0","name" => "Paraguay","dial_code" => "+595","code" => "PY"],["price" => "0","name" => "Peru","dial_code" => "+51","code" => "PE"],["price" => "0","name" => "Philippines","dial_code" => "+63","code" => "PH"],["price" => "0","name" => "Pitcairn","dial_code" => "+872","code" => "PN"],["price" => "0","name" => "Poland","dial_code" => "+48","code" => "PL"],["price" => "0","name" => "Portugal","dial_code" => "+351","code" => "PT"],["price" => "0","name" => "Puerto Rico","dial_code" => "+1939","code" => "PR"],["price" => "0","name" => "Qatar","dial_code" => "+974","code" => "QA"],["price" => "0","name" => "Romania","dial_code" => "+40","code" => "RO"],["price" => "0","name" => "Russia","dial_code" => "+7","code" => "RU"],["price" => "0","name" => "Rwanda","dial_code" => "+250","code" => "RW"],["price" => "0","name" => "Reunion","dial_code" => "+262","code" => "RE"],["price" => "0","name" => "Saint Barthelemy","dial_code" => "+590","code" => "BL"],["price" => "0","name" => "Saint Helena, Ascension and Tristan Da Cunha","dial_code" => "+290","code" => "SH"],["price" => "0","name" => "Saint Kitts and Nevis","dial_code" => "+1869","code" => "KN"],["price" => "0","name" => "Saint Lucia","dial_code" => "+1758","code" => "LC"],["price" => "0","name" => "Saint Martin","dial_code" => "+590","code" => "MF"],["price" => "0","name" => "Saint Pierre and Miquelon","dial_code" => "+508","code" => "PM"],["price" => "0","name" => "Saint Vincent and the Grenadines","dial_code" => "+1784","code" => "VC"],["price" => "0","name" => "Samoa","dial_code" => "+685","code" => "WS"],["price" => "0","name" => "San Marino","dial_code" => "+378","code" => "SM"],["price" => "0","name" => "Sao Tome and Principe","dial_code" => "+239","code" => "ST"],["price" => "0","name" => "Saudi Arabia","dial_code" => "+966","code" => "SA"],["price" => "0","name" => "Senegal","dial_code" => "+221","code" => "SN"],["price" => "0","name" => "Serbia","dial_code" => "+381","code" => "RS"],["price" => "0","name" => "Seychelles","dial_code" => "+248","code" => "SC"],["price" => "0","name" => "Sierra Leone","dial_code" => "+232","code" => "SL"],["price" => "0","name" => "Singapore","dial_code" => "+65","code" => "SG"],["price" => "0","name" => "Slovakia","dial_code" => "+421","code" => "SK"],["price" => "0","name" => "Slovenia","dial_code" => "+386","code" => "SI"],["price" => "0","name" => "Solomon Islands","dial_code" => "+677","code" => "SB"],["price" => "0","name" => "Somalia","dial_code" => "+252","code" => "SO"],["price" => "0","name" => "South Africa","dial_code" => "+27","code" => "ZA"],["price" => "0","name" => "South Sudan","dial_code" => "+211","code" => "SS"],["price" => "0","name" => "South Georgia and the South Sandwich Islands","dial_code" => "+500","code" => "GS"],["price" => "0","name" => "Spain","dial_code" => "+34","code" => "ES"],["price" => "0","name" => "Sri Lanka","dial_code" => "+94","code" => "LK"],["price" => "0","name" => "Sudan","dial_code" => "+249","code" => "SD"],["price" => "0","name" => "Suriname","dial_code" => "+597","code" => "SR"],["price" => "0","name" => "Svalbard and Jan Mayen","dial_code" => "+47","code" => "SJ"],["price" => "0","name" => "Swaziland","dial_code" => "+268","code" => "SZ"],["price" => "0","name" => "Sweden","dial_code" => "+46","code" => "SE"],["price" => "0","name" => "Switzerland","dial_code" => "+41","code" => "CH"],["price" => "0","name" => "Syrian Arab Republic","dial_code" => "+963","code" => "SY"],["price" => "0","name" => "Taiwan","dial_code" => "+886","code" => "TW"],["price" => "0","name" => "Tajikistan","dial_code" => "+992","code" => "TJ"],["price" => "0","name" => "Tanzania, United Republic of Tanzania","dial_code" => "+255","code" => "TZ"],["price" => "0","name" => "Thailand","dial_code" => "+66","code" => "TH"],["price" => "0","name" => "Timor-Leste","dial_code" => "+670","code" => "TL"],["price" => "10","name" => "Togo","dial_code" => "+228","code" => "TG"],["price" => "0","name" => "Tokelau","dial_code" => "+690","code" => "TK"],["price" => "0","name" => "Tonga","dial_code" => "+676","code" => "TO"],["price" => "0","name" => "Trinidad and Tobago","dial_code" => "+1868","code" => "TT"],["price" => "0","name" => "Tunisia","dial_code" => "+216","code" => "TN"],["price" => "0","name" => "Turkey","dial_code" => "+90","code" => "TR"],["price" => "0","name" => "Turkmenistan","dial_code" => "+993","code" => "TM"],["price" => "0","name" => "Turks and Caicos Islands","dial_code" => "+1649","code" => "TC"],["price" => "0","name" => "Tuvalu","dial_code" => "+688","code" => "TV"],["price" => "0","name" => "Uganda","dial_code" => "+256","code" => "UG"],["price" => "0","name" => "Ukraine","dial_code" => "+380","code" => "UA"],["price" => "0","name" => "United Arab Emirates","dial_code" => "+971","code" => "AE"],["price" => "0","name" => "United Kingdom","dial_code" => "+44","code" => "GB"],["price" => "10","name" => "United States","dial_code" => "+1","code" => "US"],["price" => "0","name" => "Uruguay","dial_code" => "+598","code" => "UY"],["price" => "0","name" => "Uzbekistan","dial_code" => "+998","code" => "UZ"],["price" => "0","name" => "Vanuatu","dial_code" => "+678","code" => "VU"],["price" => "0","name" => "Venezuela, Bolivarian Republic of Venezuela","dial_code" => "+58","code" => "VE"],["price" => "0","name" => "Vietnam","dial_code" => "+84","code" => "VN"],["price" => "0","name" => "Virgin Islands, British","dial_code" => "+1284","code" => "VG"],["price" => "0","name" => "Virgin Islands, U.S.","dial_code" => "+1340","code" => "VI"],["price" => "0","name" => "Wallis and Futuna","dial_code" => "+681","code" => "WF"],["price" => "0","name" => "Yemen","dial_code" => "+967","code" => "YE"],["price" => "0","name" => "Zambia","dial_code" => "+260","code" => "ZM"],["price" => "0","name" => "Zimbabwe","dial_code" => "+263","code" => "ZW"]
        ];
    }

    public function idgenerate($length)
    {
        $chaine ="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        srand((double)microtime()*1000000);
        $pass = "";
        for($i=0; $i<$length; $i++){
            @$pass .= $chaine[rand()%strlen($chaine)];
        }
        return $pass;
    }

    public function numeric_generate($length)
    {
        $chaine ="0123456789";
        srand((double)microtime()*1000000);
        $pass = "";
        for($i=0; $i<$length; $i++){
            @$pass .= $chaine[rand()%strlen($chaine)];
        }
        return $pass;
    }

    public function status($code)
    {
		$status = $this->statusRepository->findOneByCode($code);
		if($status){
			$name = $this->intl->trans($status->getName());
			$status->setName($name);
		}
    	return $status;
    }

    public function connectedUser()
    {
       return $this->getUser();
    }

	// : Les signatures de la fonction
	// $pAllView : permission de tous voir
	// $user = null : entité User, si null, l'utilisateur en session est automatiquement récupéré
	// $pManager = null : (boolean) permission de gérer un compte (facultatif)
	// $pBrand = null : (boolean) permission de créer une marque (facultatif)

	// Le retour de la fonction est un tableau
	// Retour : [$code, $id, $arrayRequest]
	// $code : (0 : Super admin ou administrateur; 1 : Gestionnaire de compte, 2 : Revendeur; 3 : Affilié d'un Revendeur; 4 : Utilisateur simple; 5 : Affilié à un utilisateur simple)
	// $id : Si $code == 3 ou 5 => id de son administrateur ; Si $code == 1 ou 2 ou 4 => son propre id ; Si $code == 0 => 0 (car aucun utilisateur n'aura en paramètre l'id de l'administrateur)
	// $arrayRequest : est un tableau formant une partie de requête.
	public function checkThisUser($pAllView, $user = null, $pManager = null, $pBrand = null)
	{
		$session = $user ? $user : $this->getUser();
		if($pManager === null) $pManager = $this->checkPermission("MANGR", $session);
		if($pBrand === null) $pBrand = $this->checkPermission("RES5", $session);

		if($pAllView) return [0, 0, ["master"=>true]]; // Super admin ou administrateur
        else if($pManager) return [1, $session->getId(), ["managerby"=>$session->getId()]]; // Gestionnaire de compte
        else if($pBrand && !$session->getAffiliateManager()) return [2, $session->getId(), ["reselby"=>$session->getId()]]; // Revendeur
        else if($session->getAffiliateManager() && $this->checkPermission("RES5", $session->getAffiliateManager())) return [3, $session->getAffiliateManager()->getId(), ["reselby"=>$session->getAffiliateManager()->getId()]]; // Affilié d'un Revendeur
        else if(!$pBrand && !$session->getAffiliateManager()) return [4, $session->getId(), ["user"=>$session->getId()]]; // Utilisateur
        else if($session->getAffiliateManager()) return [5, $session->getAffiliateManager()->getId(), ["user"=>$session->getAffiliateManager()->getId()]]; // Affilié à un utilisateur
	}


    public function imageSetter($request , $_fileName, $isUpdating = false, $route = false)
	{
        dd("der");
        $response = new Response();

        $placeAvatar  = $this->getParameter($route);
        $filename     = $_fileName;
        $filepath     = $placeAvatar.$filename;

		$response->headers->set('Content-Type', 'application/json');
		$response->headers->set('Access-Control-Allow-Origin', '*');

		$image_remove	=	$request->request->get("avatar_remove");
		/** @var UploadedFile $SETTINGFILE */
        $SETTINGFILE    =	$request->files->get('avatar');
        $image  = ($image_remove == "1") ? "default_logo_1.png" : (($isUpdating) ? $filename : "default_logo_1.png" );
        if(isset($SETTINGFILE) && $SETTINGFILE->getError() == 0){
            $return	=	$this->services->checkFile($SETTINGFILE, ["jpeg", "jpg", "png", "JPEG", "JPG", "PNG"], 200024);
            if($return['error'] == false) {
                return $this->services->renameFile($SETTINGFILE, $placeAvatar, true, $placeAvatar, $filename);
            }else
                return [
                    'error' => true,
                    'info'  => $return['info'],
                ];

        } else
        return $image;
	}
}
