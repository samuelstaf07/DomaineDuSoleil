<?php

namespace App\Controller;

use App\Repository\RentalsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
    public function rental($id, RentalsRepository $rentalsRepository): Response
    {
        foreach ($rentalsRepository->find($id)->getImages() as $value){
            dump($value);
        }

        return $this->render('rental/index.html.twig', [
            'rental' => $rentalsRepository->find($id),
        ]);
    }
}
