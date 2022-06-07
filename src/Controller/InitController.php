<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InitController extends AbstractController
{

    public function __construct(){
	}
    #[Route('/')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_login');
    }

    #[Route('/{_locale}')]
    public function indexi(): Response
    {
        return $this->redirectToRoute('app_login');
    }
}
