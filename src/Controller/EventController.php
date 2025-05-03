<?php

namespace App\Controller;

use App\Entity\ReservationsEvents;
use App\Form\ReservationsEventsType;
use App\Repository\EventsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/events', name: 'app_events')]
    public function index(EventsRepository $eventsRepository): Response
    {
        return $this->render('events/index.html.twig', [
            'events' => $eventsRepository->findActiveEvents(),
        ]);
    }
    #[Route('/event/{id}', name: 'app_event')]
    public function event($id, SessionInterface $session, EventsRepository $eventsRepository, Request $request): Response
    {
        $event = $eventsRepository->find($id);

        if($event->isActive()){

            $reservation = new ReservationsEvents();
            $reservation->setEvent($event);

            $form = $this->createForm(ReservationsEventsType::class, $reservation);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $cart = $session->get('myCart', []);
                $totalPlaceCart = 0;

                foreach ($cart as $elemCart) {
                    if (
                        $elemCart['type'] === 'event' &&
                        isset($elemCart['eventId']) &&
                        $elemCart['eventId'] === $event->getId()
                    ) {
                        $totalPlaceCart += $elemCart['nbPlaces'];
                    }
                }

                $reservationsTotal = $totalPlaceCart + $reservation->getNbPlaces();

                if ($reservationsTotal > 20) {
                    $this->addFlash('danger', 'Vous ne pouvez pas avoir plus de 20 places pour cet événement !');
                    return $this->render('event/index.html.twig', [
                        'event' => $event,
                        'form' => $form->createView(),
                    ]);
                }

                if($reservationsTotal > $event->getRemainingPlaces()){
                    $this->addFlash('danger', 'Pas assez de places pour la demande.');
                    return $this->render('event/index.html.twig', [
                        'event' => $event,
                        'form' => $form->createView(),
                    ]);
                }
                $session->set('newReservationEvent', [
                    'eventId' => $event->getId(),
                    'eventTitle' => $event->getTitle(),
                    'eventPrice' => $event->getPrice(),
                    'type' => 'event',
                    'image' => $event->getHomePageImage(),
                    'nbPlaces' => $reservation->getNbPlaces(),
                ]);

                return $this->redirectToRoute('app_cart');
            }


            return $this->render('event/index.html.twig', [
                'event' => $event,
                'form' => $form->createView(),
            ]);
        }else{
            return $this->redirectToRoute('app_home');
        }
    }
}
