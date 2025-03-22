<?php

namespace App\Controller\admin;

use App\Entity\ReservationsEvents;
use App\Form\ReservationsEventsType;
use App\Repository\ReservationsEventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reservations/events')]
final class ReservationsEventsController extends AbstractController
{
    #[Route(name: 'app_reservations_events_index', methods: ['GET'])]
    public function index(ReservationsEventsRepository $reservationsEventsRepository): Response
    {
        return $this->render('reservations_events/index.html.twig', [
            'reservations_events' => $reservationsEventsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reservations_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservationsEvent = new ReservationsEvents();
        $form = $this->createForm(ReservationsEventsType::class, $reservationsEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservationsEvent);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservations_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservations_events/new.html.twig', [
            'reservations_event' => $reservationsEvent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservations_events_show', methods: ['GET'])]
    public function show(ReservationsEvents $reservationsEvent): Response
    {
        return $this->render('reservations_events/show.html.twig', [
            'reservations_event' => $reservationsEvent,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservations_events_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReservationsEvents $reservationsEvent, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationsEventsType::class, $reservationsEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservations_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservations_events/edit.html.twig', [
            'reservations_event' => $reservationsEvent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservations_events_delete', methods: ['POST'])]
    public function delete(Request $request, ReservationsEvents $reservationsEvent, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservationsEvent->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservationsEvent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservations_events_index', [], Response::HTTP_SEE_OTHER);
    }
}
