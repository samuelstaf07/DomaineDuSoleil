<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Form\CommentsType;
use App\Form\CommentType;
use App\Repository\RentalsRepository;
use App\Repository\ReservationsRentalsRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentsController extends AbstractController
{
    #[Route('/rental/{id}/addComment', name: 'app_add_comment')]
    public function index($id, RentalsRepository $rentalsRepository, ReservationsRentalsRepository $reservationsRentalsRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $rentals = $rentalsRepository->find($id);

        if(!$rentals){
            $this->addFlash('danger', 'Aucun logement.');
            return $this->redirectToRoute('app_account');
        }

        if($rentals->hasCommentByUser($this->getUser())){
            $this->addFlash('danger', 'Vous avez déja posté un commentaire sur ce logement.');
            return $this->redirectToRoute('app_account');
        }

        if(!$reservationsRentalsRepository->findByUserAndRental($this->getUser(), $rentals)){
            $this->addFlash('danger', 'Vous n\'avez pas de réservation sur ce logement.');
            return $this->redirectToRoute('app_account');
        }

        $comment = new Comments();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $comment->setUser($this->getUser());
            $comment->setRentals($rentals);
            $comment->setCreatedAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));
            $comment->setIsActive(true);
            $comment->setDisabledAt(null);
            $comment->setChangedAt(null);

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Le commentaire a été envoyé.');
            return $this->redirectToRoute('app_rental', [
                'id' => $rentals->getId(),
            ]);
        }


        return $this->render('comments/index.html.twig', [
            'rentals' => $rentals,
            'form' => $form,
        ]);
    }

    #[Route('/comment/{id}/edit', name: 'app_edit_comment')]
    public function edit(
        Comments $comment,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($comment->getUser() !== $this->getUser() || $comment->getIsActive()) {
            $this->addFlash('danger', 'Vous n\'avez pas le droit de modifier ce commentaire.');
            return $this->redirectToRoute('app_account');
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setChangedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Brussels')));
            $comment->setIsActive(true);
            $comment->setDisabledAt(null);
            $entityManager->flush();

            $this->addFlash('success', 'Le commentaire a été modifié avec succès.');
            return $this->redirectToRoute('app_account');
        }

        return $this->render('comments/edit.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
            'rentals' => $comment->getRentals(),
        ]);
    }

}
