<?php

namespace App\Controller;

use App\Repository\RentalsRepository;
use App\Repository\ReservationsRentalsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Date;

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
        $reservedDates = [];

        foreach ($rental->getReservations() as $reservation) {
            $startDate = $reservation->getDateStart();
            $endDate = $reservation->getDateEnd();

            

        }

        return $this->render('rental/index.html.twig', [
            'rental' => $rental,
            '$reservedDates' => $reservedDates,
        ]);
    }
}
