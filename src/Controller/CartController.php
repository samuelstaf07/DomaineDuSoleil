<?php

namespace App\Controller;

use App\Entity\Rentals;
use App\Repository\CommentsRepository;
use App\Repository\EventsRepository;
use App\Repository\PostsRepository;
use App\Repository\RentalsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, RentalsRepository $rentalsRepository, PostsRepository $postsRepository, EventsRepository $eventsRepository): Response
    {
        $newReservationRental = $session->get('newReservationRental');
        $newReservationEvent = $session->get('newReservationEvent');
        $myCart = $session->get('myCart', []);

        if ($newReservationRental !== null) {
            $myCart[] = $newReservationRental;

            $session->set('myCart', $myCart);
            $session->remove('newReservationRental');
        }

        if ($newReservationEvent !== null) {
            $myCart[] = $newReservationEvent;

            $session->set('myCart', $myCart);
            $session->remove('newReservationEvent');
        }

        return $this->render('cart/index.html.twig', [
            'cartElements' => $myCart,
        ]);
    }

    #[Route('/cart/resetCart', name: 'app_reset_cart')]
    public function resetCart(SessionInterface $session, RentalsRepository $rentalsRepository, PostsRepository $postsRepository, EventsRepository $eventsRepository): Response
    {
        $session->remove('myCart');

        $session->remove('newReservation');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/delElement', name: 'app_delete_element')]
    public function delElement(SessionInterface $session, RentalsRepository $rentalsRepository, PostsRepository $postsRepository, EventsRepository $eventsRepository): Response
    {
        $session->remove('myCart');

        $session->remove('newReservation');

        return $this->redirectToRoute('app_cart');
    }
}
