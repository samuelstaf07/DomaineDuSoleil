<?php

namespace App\Controller;

use App\Repository\RentalsRepository;
use App\Repository\ReservationsRentalsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RentalsController extends AbstractController
{
    #[Route('/rentals', name: 'app_rentals')]
    public function index(RentalsRepository $rentalsRepository): Response
    {
        return $this->render('rentals/index.html.twig', [
            'rentals' => $rentalsRepository->findAllRentalsActive(),
        ]);
    }

    #[Route('/rental/{id}', name: 'app_rental')]
    public function rental($id, RentalsRepository $rentalsRepository, ReservationsRentalsRepository $reservationsRentalsRepository): Response
    {
        $rental = $rentalsRepository->find($id);
        $eventsDates = [];

        foreach ($rental->getReservations() as $reservation){
            $eventsDates[] = [
                'start' => $reservation->getDateStart()->format('Y-m-d'),
                'end' => $reservation->getDateEnd()->format('Y-m-d'),
                'title' => 'Réservé',
            ];
        }

        return $this->render('rental/index.html.twig', [
            'rental' => $rental,
            'events' => $eventsDates,
        ]);
    }
}
