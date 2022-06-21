<?php

namespace App\Twig;

use App\Entity\User;
use App\Service\BaseUrl;
use App\Service\Services;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
//use Symfony\Component\Translation\Translator;

class AppExtension extends AbstractExtension
{
	//private $translator;

	public function __construct(Services $src) {
		//$this->translator = $translator;
		$this->src = $src;
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
		return $this->src->checkPermission($codePermission, $user, $granted);
	}

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
