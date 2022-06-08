<?php

namespace App\Twig;

use App\Entity\User;
use App\Service\BaseUrl;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
//use Symfony\Component\Translation\Translator;

class AppExtension extends AbstractExtension
{
	//private $translator;

	public function __construct() {
		//$this->translator = $translator;
	}

	public function getFilters(): array
	{
		return [
			// If your filter generates SAFE HTML, you should add a third
			// parameter: ['is_safe' => ['html']]
			// Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
			new TwigFilter('currentLanguage', [$this, 'currentLanguage']),
			new TwigFilter('checkLanguage', [$this, 'checkLanguage']),
		];
	}

	public function getFunctions(): array
	{
		return [
			new TwigFunction('permission', [$this, 'checkPermission']),
			new TwigFunction('menu', [$this, 'menu']),
			new TwigFunction('truncateText', [$this, 'truncateText']),
			//new TwigFunction('asset', [$this, 'basePath']),
		];
	}

	/**
	 * Summary of currentLanguage
	 * @param string $_locale
	 * @return string
	 */
	public function currentLanguage(string $_locale)
	{
		switch ($_locale){
			case "en":
				return "English";
			default:
				return "Français";
		}
	}

	/**
	 * Summary of checkLanguage
	 * @param string $_locale
	 * @return string
	 */
	public function checkLanguage(string $_locale)
	{
		switch ($_locale){
			case "en":
				return "en";
			default:
				return "fr";
		}
	}

	/**
    * @param mixed $codePermission
    * codePermission est un tableau de chaine
	* @param object|null $user == app.user
	* @param bool $granted
    * @return bool
    * Retourne le status de la première permission trouvée
    */
	public function checkPermission($codePermission, object $user, bool $granted = true)
    {
		if(!$user) return false;

		$authorizations	=	$user->getRole()->getAuthorizations();

		$master = false;

		foreach ($authorizations as $key => $value) {
			$permission = $value->getPermission();
			if(is_array($codePermission)){
				if (in_array($permission->getCode(), $codePermission, true)) {
					if(
						$granted == true
						&& (
							$value->getStatus()->getCode() == false
							|| $permission->getStatus()->getCode() == 2
						)
					) return false;

					if($value->getStatus()->getCode() == true && $permission->getStatus()->getCode() <= 3) $master = true;
				}
			}else{
				if ($permission->getCode() == $codePermission){
					if($granted)
						return ($value->getStatus()->getCode() == true && $permission->getStatus()->getCode() == 3) ? true : false;
					else
						return ($value->getStatus()->getCode() == true && $permission->getStatus()->getCode() <= 3) ? true : false;
				}
			}
		}

        return $master;
	}

	/*public function basePath(){
		$manifest = file_exists("./build/manifest.json") ? "./build/manifest.json" : "./public/build/manifest.json";

        $data = json_decode(file_get_contents($manifest), true);

        return $data["asset"] ? $data["asset"] : "/";
	}*/

	public function menu(string $uri, $path){
		if(is_array($path) )
			return  (in_array($uri, $path, true)) ? ' active' : '';
		else
			return ($uri === $path) ? ' active' : '';
	}

	public function truncateText(string $string, $trunc = true){
		$string = substr($string, 0, 50);
		if($trunc === true)
			return  $string.' ...';
		else
			return $string;
	}
}
