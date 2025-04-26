<?php

namespace App\Controller;

use App\Repository\EventsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/events', name: 'app_events')]
    public function index(EventsRepository $eventsRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventsRepository->findActiveEvents(),
        ]);
    }
}
