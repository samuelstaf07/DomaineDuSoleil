<?php

namespace App\Controller;

use App\Entity\Rentals;
use App\Repository\CommentsRepository;
use App\Repository\RentalsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RentalsRepository $rentalsRepository, CommentsRepository $commentsRepository): Response
    {
        $rentals = $rentalsRepository->findAllRentalsWithDiscount();
        $comments = [];

        foreach ($rentals as $rental){
            $comments[] = $commentsRepository->findCommentsByRentals($rental);
        }

        return $this->render('home/index.html.twig', [
            'rentals' => $rentals,
            'comments' => $comments,
        ]);
    }
}
