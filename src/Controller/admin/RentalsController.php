<?php

namespace App\Controller\admin;

use App\Entity\Rentals;
use App\Form\RentalsType;
use App\Repository\RentalsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rentals')]
#[IsGranted('ROLE_ADMIN')]
final class RentalsController extends AbstractController
{
    #[Route(name: 'app_rentals_index', methods: ['GET'])]
    public function index(RentalsRepository $rentalsRepository): Response
    {
        return $this->render('admin/rentals/index.html.twig', [
            'rentals' => $rentalsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_rentals_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rental = new Rentals();
        $form = $this->createForm(RentalsType::class, $rental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rental);
            $entityManager->flush();

            return $this->redirectToRoute('app_rentals_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rentals/new.html.twig', [
            'rental' => $rental,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rentals_show', methods: ['GET'])]
    public function show(Rentals $rental): Response
    {
        return $this->render('admin/rentals/show.html.twig', [
            'rental' => $rental,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rentals_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rentals $rental, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RentalsType::class, $rental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_rentals_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rentals/edit.html.twig', [
            'rental' => $rental,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rentals_delete', methods: ['POST'])]
    public function delete(Request $request, Rentals $rental, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rental->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($rental);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rentals_index', [], Response::HTTP_SEE_OTHER);
    }
}
