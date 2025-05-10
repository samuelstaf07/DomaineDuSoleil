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
    #[Route('/bill/{id}', name: 'app_bills')]
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

        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        $html = $this->renderView('pdf/bill.html.twig', [
            'bill' => $bill,
            'user' => $bill->getUser(),
            'events' => $bill->getReservationsEvents(),
            'rentals' => $bill->getReservationsRentals(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="facture_' . $bill->getId() . '.pdf"',
            ]
        );
    }
}
