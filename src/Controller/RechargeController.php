<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RechargeController extends AbstractController
{
    #[Route('/recharge', name: 'app_recharge')]
    public function index(): Response
    {
        return $this->render('recharge/index.html.twig', [
            'controller_name' => 'RechargeController',
        ]);
    }
}
