<?php

namespace App\Controller;

use App\Repository\BillsRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class BillsController extends AbstractController
{
    #[Route('/bill/{id}', name: 'app_bill')]
    public function index($id, BillsRepository $billsRepository, Security $security): Response
    {
        $bill = $billsRepository->find($id);

        if(!$bill){
            $this->addFlash('danger', 'Aucune Facture trouvée.');
            return $this->redirectToRoute('app_home');
        }

        if ($bill->getUser() !== $this->getUser() && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $this->addFlash('danger', 'Vous n\'êtes pas autorisé à accéder à cette facture.');
            return $this->redirectToRoute('app_home');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/bills/bill_' . $bill->getId() . '.pdf';

        if (!file_exists($filePath)) {
            $this->addFlash('danger', 'Le fichier de la facture est introuvable.');
            return $this->redirectToRoute('app_home');
        }

        $pdfContent = file_get_contents($filePath);

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="facture_' . $bill->getId() . '.pdf"',
            ]
        );
    }
}
