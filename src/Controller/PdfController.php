<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfController extends AbstractController
{
    #[Route('{_locale}/pdf', name: 'pdf')]
    public function index(): Response
    {
        return $this->render('pdf/index.html.twig', [
            'controller_name' => 'PdfController']);
    }

    #[Route('{_locale}/dpdf/{id}', name: 'app_dpdf')]
    public function dpdf(string $id): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('pdf/mypdf.html.twig', [
            'title' => "Welcome to our PDF Test"
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $fichier = $dompdf->stream("test.pdf", [
            "Attachment" => true
        ]);

        return $this->json(array('message' => $fichier));
    }

}
