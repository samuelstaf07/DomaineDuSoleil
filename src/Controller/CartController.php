<?php

namespace App\Controller;

use App\Entity\Bills;
use App\Entity\ReservationsEvents;
use App\Entity\ReservationsRentals;
use App\Repository\EventsRepository;
use App\Repository\RentalsRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session): Response
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

        return $this->render('cart/index.html.twig', [
            'cartElements' => $myCart,
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

    #[Route('/cart/confirmCart', name: 'app_confirm_cart')]
    public function confirmCart(SessionInterface $session, EntityManagerInterface $entityManager, RentalsRepository $rentalsRepository, EventsRepository $eventsRepository): Response
    {

        if($this->getUser() && $this->getUser()->isEmailAuthentificated()){
            $cart = $session->get('myCart', []);

            if (empty($cart)) {
                $this->addFlash('warning', 'Votre panier est vide.');
                return $this->redirectToRoute('app_cart');
            }

            $newBill = new Bills();

            $newBill->setDate(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));
            $totalPriceBill = 0;

            foreach ($cart as $reservation){

                if($reservation['type'] == "rental"){
                    $reservationRental = new ReservationsRentals();
                    $rental = $rentalsRepository->find($reservation['rentalId']);
                    $nbDay = $reservation['dateStart']->diff($reservation['dateEnd'])->days + 1;

                    //Il faut demander à l'utilisateur s'il souhaites avoir la caution de nettoyage
                    $cleaningDeposit = true;

                    if ($rental->isOnPromotion()) {
                        $pricePerDay = floor($rental->getPricePerDay() * 0.9 * 100) / 100;
                    } else {
                        $pricePerDay = floor($rental->getPricePerDay() * 100) / 100;
                    }

                    $totalPrice = floor($nbDay * $pricePerDay * 100) / 100;
                    $cleaningDeposit === true ? $priceCleaningDeposit = 50 : $priceCleaningDeposit = 0;
                    $totalPriceBill += $totalPrice + $priceCleaningDeposit;

                    $reservationRental->setBill($newBill);
                    $reservationRental->setUser($this->getUser());
                    $reservationRental->setRentals($rental);
                    $reservationRental->setHasCleaningDeposit($cleaningDeposit);
                    $reservationRental->setTotalPrice($totalPrice + $priceCleaningDeposit);

                    if($reservationRental->hasCleaningDeposit()){
                        $reservationRental->setTotalDepositReturned(50);
                    }else{
                        $reservationRental->setTotalDepositReturned(0);
                    }

                    //Status à 0 = pas payé, 1 = payé par l'utilisateur, 2 = en cours de vérification, 3 = remboursé, 4 =  refusé
                    $reservationRental->setStatusBaseDeposit(1);
                    $reservationRental->setDateReservation(new \DateTimeImmutable('now'));
                    $reservationRental->setDateStart($reservation['dateStart']);
                    $reservationRental->setDateEnd($reservation['dateEnd']);
                    $reservationRental->setStatusReservation(1);

                    $entityManager->persist($reservationRental);

                    foreach ($rental->getImages() as $image) {
                        $entityManager->persist($image);
                    }
                    $entityManager->persist($rental);

                }else if($reservation['type'] == "event"){
                    $reservationEvent = new ReservationsEvents();
                    $event = $eventsRepository->find($reservation['eventId']);
                    $totalPrice = ($event->getPrice() * $reservation['nbPlaces']);
                    $totalPriceBill += $totalPrice;

                    $reservationEvent->setEvent($event);
                    $reservationEvent->setNbPlaces($reservation['nbPlaces']);
                    $reservationEvent->setIsActive(1);
                    $reservationEvent->setUser($this->getUser());
                    $reservationEvent->setBill($newBill);
                    $reservationEvent->setDateReservation(new \DateTimeImmutable('now'));
                    $reservationEvent->setTotalDeposit($totalPrice);

                    $entityManager->persist($reservationEvent);
                }
            }

            $newBill->setDate(new \DateTimeImmutable());
            $newBill->setTotalPrice($totalPriceBill);
            $newBill->setStatus(1);
            $newBill->setUser($this->getUser());

            $this->getUser()->setNbPoints($this->getUser()->getNbPoints() + ((int) ($totalPriceBill / 100)));

            $entityManager->persist($newBill);
            $entityManager->flush();

            $session->remove('myCart');

            $this->addFlash('success','Votre paiement est validé. Nous sommes impatients de vous retrouver au Domaine Du Soleil ! 😊');
            return $this->redirectToRoute('app_cart');
        }else if($this->getUser() && !$this->getUser()->isEmailAuthentificated()){
            $this->addFlash('danger','Vous devez avoir une adresse mail vérifiée pour pouvoir passer une commande.');
            return $this->redirectToRoute('app_cart');
        }else{
            $this->addFlash('danger','Vous devez être connecté pour pouvoir passer une commande.');
            return $this->redirectToRoute('app_cart');
        }
    }

}
