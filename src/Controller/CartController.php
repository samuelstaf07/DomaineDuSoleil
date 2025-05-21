<?php

namespace App\Controller;

use App\Entity\Bills;
use App\Entity\ReservationsEvents;
use App\Entity\ReservationsRentals;
use App\Form\IsAdultType;
use App\Form\ReservationsRentalsType;
use App\Repository\EventsRepository;
use App\Repository\RentalsRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, Request $request): Response
    {
        $newReservationRental = $session->get('newReservationRental');
        $newReservationEvent = $session->get('newReservationEvent');
        $myCart = $session->get('myCart', []);
        $idCounter = $session->get('cartIdCounter', 0);

        if ($newReservationRental !== null) {
            $idCounter++;
            $newReservationRental['id'] = $idCounter;
            $myCart[] = $newReservationRental;

            $session->set('myCart', $myCart);
            $session->remove('newReservationRental');
        }

        if ($newReservationEvent !== null) {
            $idCounter++;
            $newReservationEvent['id'] = $idCounter;
            $myCart[] = $newReservationEvent;

            $session->set('myCart', $myCart);
            $session->remove('newReservationEvent');
        }

        $session->set('cartIdCounter', $idCounter);

        $form = $this->createForm(isAdultType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $session->set('is_adult_confirmed', true);
            return $this->redirectToRoute('app_payment_stripe');
        }

        return $this->render('cart/index.html.twig', [
            'cartElements' => $myCart,
            'form' => $form,
        ]);
    }

    #[Route('/cart/resetCart', name: 'app_reset_cart')]
    public function resetCart(SessionInterface $session): Response
    {
        $session->remove('myCart');

        $session->remove('newReservation');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/delElement/{id}', name: 'app_delete_element_cart')]
    public function delElement(int $id, SessionInterface $session): Response
    {
        $cart = $session->get('myCart', []);

        $cart = array_filter($cart, function ($item) use ($id) {
            return $item['id'] != $id;
        });

        $session->set('myCart', array_values($cart));

        return $this->redirectToRoute('app_cart');
    }
}
