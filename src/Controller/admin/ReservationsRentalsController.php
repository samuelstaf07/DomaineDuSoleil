<?php

namespace App\Controller\admin;

use App\Entity\ReservationsRentals;
use App\Form\ReservationsRentalsType;
use App\Repository\ReservationsRentalsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reservations/rentals')]
final class ReservationsRentalsController extends AbstractController
{
    #[Route(name: 'app_reservations_rentals_index', methods: ['GET'])]
    public function index(ReservationsRentalsRepository $reservationsRentalsRepository): Response
    {
        return $this->render('admin/reservations_rentals/index.html.twig', [
            'reservations_rentals' => $reservationsRentalsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reservations_rentals_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservationsRental = new ReservationsRentals();
        $form = $this->createForm(ReservationsRentalsType::class, $reservationsRental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservationsRental);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservations_rentals_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservations_rentals/new.html.twig', [
            'reservations_rental' => $reservationsRental,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservations_rentals_show', methods: ['GET'])]
    public function show(ReservationsRentals $reservationsRental): Response
    {
        return $this->render('admin/reservations_rentals/show.html.twig', [
            'reservations_rental' => $reservationsRental,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservations_rentals_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReservationsRentals $reservationsRental, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationsRentalsType::class, $reservationsRental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservations_rentals_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservations_rentals/edit.html.twig', [
            'reservations_rental' => $reservationsRental,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservations_rentals_delete', methods: ['POST'])]
    public function delete(Request $request, ReservationsRentals $reservationsRental, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservationsRental->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservationsRental);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservations_rentals_index', [], Response::HTTP_SEE_OTHER);
    }
}
