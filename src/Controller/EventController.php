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
            $reservation->setEventId($event);

            $form = $this->createForm(ReservationsEventsType::class, $reservation);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $session->set('newReservationEvent', [
                    'event' => $event,
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
