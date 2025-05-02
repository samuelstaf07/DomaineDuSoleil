<?php

namespace App\Controller;

use App\Entity\ReservationsRentals;
use App\Form\ReservationsRentalsType;
use App\Repository\CommentsRepository;
use App\Repository\RentalsRepository;
use App\Repository\ReservationsRentalsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
    public function rental(
        $id, SessionInterface $session, RentalsRepository $rentalsRepository, CommentsRepository $commentsRepository, Request $request, EntityManagerInterface $entityManager ): Response {

        $rental = $rentalsRepository->find($id);
        $reservedDates = [];

        if ($rental->isActive()) {
            foreach ($rental->getUpcomingReservations() as $reservation) {
                $reservedDates[] = [
                    'title' => 'Réservé',
                    'start' => $reservation->getDateStart()->format('Y-m-d'),
                    'end' => $reservation->getDateEnd()->format('Y-m-d'),
                ];
            }

            $reservation = new ReservationsRentals();
            $reservation->setRentals($rental);

            $form = $this->createForm(ReservationsRentalsType::class, $reservation, [
                'csrf_protection' => true,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $session->set('newReservationRental', [
                   'rental' => $rental,
                   'type' => 'rental',
                   'image' => $rental->getHomePageImage(),
                   'dateStart' => $reservation->getDateStart(),
                   'dateEnd' => $reservation->getDateEnd(),
                ]);

                return $this->redirectToRoute('app_cart');
            }

            return $this->render('rental/index.html.twig', [
                'rental' => $rental,
                'reservedDates' => $reservedDates,
                'comments' => $commentsRepository->findActiveCommentsByRental($rental->getId()),
                'form' => $form->createView(),
            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

}
