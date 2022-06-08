<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\RequestStack;


class BaseUrl extends AbstractController
{
    protected $baseUrl;

	public function __construct(RequestStack $requestStack)
	{
        $this->baseUrl = $requestStack->getCurrentRequest()->getSchemeAndHttpHost();
    }

    public function init(){
       return $this->baseUrl;
    }

    public function avatarInitUrl(){
        return $this->baseUrl.'/app/uploads/avatar/';
    }
}
