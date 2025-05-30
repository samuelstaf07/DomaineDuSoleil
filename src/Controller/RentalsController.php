<?php

namespace App\Controller;

use App\Entity\ReservationsRentals;
use App\Form\ReservationsRentalsType;
use App\Repository\CommentsRepository;
use App\Repository\RentalsRepository;
use App\Repository\ReservationsRentalsRepository;
use DateTimeImmutable;
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
        $id,
        SessionInterface $session,
        RentalsRepository $rentalsRepository,
        CommentsRepository $commentsRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $rental = $rentalsRepository->find($id);

        $now = new \DateTimeImmutable('now');
        $oneMonthAgo = $now->sub(new \DateInterval('P1M'));
        $twoMonthsLater = $now->add(new \DateInterval('P2M'));

        dump($rental->needToBeOnPromotion());

        if($rental->getLastReservation()){
            dump("last", $rental->getLastReservation()->getDateEnd()->format('d/m/Y'));
            dump($rental->getLastReservation()->getDateEnd() <= $oneMonthAgo);
        }

        if($rental->getNextReservation()){
            dump("next", $rental->getNextReservation()->getDateStart()->format('d/m/Y'));
            dump($rental->getNextReservation()->getDateStart() >= $twoMonthsLater);
        }


        $reservedDates = [];

        foreach ($rental->getUpcomingReservations() as $reservation) {
            $reservedDates[] = [
                'title' => 'Réservé',
                'start' => $reservation->getDateStart()->format('Y-m-d'),
                'end' => $reservation->getDateEnd()->format('Y-m-d'),
            ];
        }

        $cart = $session->get('myCart', []);
        foreach ($cart as $item) {
            if ($item['type'] === 'rental' && $rentalsRepository->find($item['rentalId'])->getId() === $rental->getId()) {
                $reservedDates[] = [
                    'title' => 'Réservation utilisateur',
                    'start' => $item['dateStart']->format('Y-m-d'),
                    'end' => $item['dateEnd']->format('Y-m-d'),
                ];
            }
        }

        $reservation = new ReservationsRentals();
        $reservation->setRentals($rental);

        $form = $this->createForm(ReservationsRentalsType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateStart = $reservation->getDateStart();
            $dateEnd = $reservation->getDateEnd();

            if(!$rental->isActive()){
                $this->addFlash('danger', 'Vous ne pouvez pas réserver un logement inactif.');
                return $this->render('rental/index.html.twig', [
                    'rental' => $rental,
                    'reservedDates' => $reservedDates,
                    'comments' => $commentsRepository->findActiveCommentsByRental($rental->getId()),
                    'form' => $form->createView(),
                ]);
            }

            if ($dateStart > $dateEnd) {
                $this->addFlash('danger', 'La date de fin ne peut pas être antérieure à la date de début.');
                return $this->render('rental/index.html.twig', [
                    'rental' => $rental,
                    'reservedDates' => $reservedDates,
                    'comments' => $commentsRepository->findActiveCommentsByRental($rental->getId()),
                    'form' => $form->createView(),
                ]);
            }

            $interval = $dateStart->diff($dateEnd);
            if ($interval->m > 2 || ($interval->y > 0) || ($interval->m == 2 && $interval->d > 0)) {
                $this->addFlash('danger', 'Les dates invalides : la durée entre la date de début et la date de fin ne peut pas dépasser 2 mois.');
                return $this->render('rental/index.html.twig', [
                    'rental' => $rental,
                    'reservedDates' => $reservedDates,
                    'comments' => $commentsRepository->findActiveCommentsByRental($rental->getId()),
                    'form' => $form->createView(),
                ]);
            }

            $userDates = [];
            $allDatesUser = new \DatePeriod($dateStart, new \DateInterval('P1D'), (clone $dateEnd)->modify('+1 day'));
            foreach ($allDatesUser as $date) {
                $userDates[] = $date->format('d-m-Y');
            }

            $reservedAllDates = [];
            foreach ($reservedDates as $date) {
                $start = new \DateTimeImmutable($date['start']);
                $end = new \DateTimeImmutable($date['end']);
                $rangePeriod = new \DatePeriod($start, new \DateInterval('P1D'), (clone $end)->modify('+1 day'));
                foreach ($rangePeriod as $dt) {
                    $reservedAllDates[] = $dt->format('d-m-Y');
                }
            }

            $check = false;
            foreach ($userDates as $date) {
                if (in_array($date, $reservedAllDates)) {
                    $check = true;
                    break;
                }
            }

            if ($check) {
                $this->addFlash('danger', 'Les dates sélectionnées sont déjà réservées.');
                return $this->render('rental/index.html.twig', [
                    'rental' => $rental,
                    'reservedDates' => $reservedDates,
                    'comments' => $commentsRepository->findActiveCommentsByRental($rental->getId()),
                    'form' => $form->createView(),
                ]);
            }

            $session->set('newReservationRental', [
                'rentalId' => $rental->getId(),
                'type' => 'rental',
                'image' => $rental->getHomePageImage(),
                'dateStart' => $dateStart,
                'dateEnd' => $dateEnd,
            ]);

            return $this->redirectToRoute('app_cart');
        }

        return $this->render('rental/index.html.twig', [
            'rental' => $rental,
            'reservedDates' => $reservedDates,
            'comments' => $commentsRepository->findActiveCommentsByRental($rental->getId()),
            'form' => $form->createView(),
        ]);
    }


}
