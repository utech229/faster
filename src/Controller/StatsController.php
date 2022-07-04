<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
#[Route('/{_locale}/stats')]
class StatsController extends AbstractController
{
	#[Route('', name: 'message_sms_stats')]
	public function index(): Response
	{
		return $this->render('stats/index.html.twig', [
			'controller_name' => 'StatsController',
		]);
	}
}
