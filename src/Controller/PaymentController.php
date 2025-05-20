<?php

namespace App\Controller;

use App\Entity\Bills;
use App\Entity\Images;
use App\Entity\ReservationsEvents;
use App\Entity\ReservationsRentals;
use App\Repository\EventsRepository;
use App\Repository\RentalsRepository;
use App\Repository\UsersRepository;
use App\Services\MailerService;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PaymentController extends AbstractController
{
    private UrlGeneratorInterface $urlGenerator;
    private string $stripeSecretKey;
    private string $endpointSecret;

    public function __construct(UrlGeneratorInterface $urlGenerator, string $stripeSecretKey, string $endpointSecret)
    {
        $this->urlGenerator = $urlGenerator;
        $this->stripeSecretKey = $stripeSecretKey;
        $this->endpointSecret = $endpointSecret;
    }

    #[Route('/cart/create-session-stripe', name: 'app_payment_stripe')]
    public function createCheckoutSession(SessionInterface $session): Response
    {
        $productStripe = [];
        $order = $session->get('myCart', []);

        if (empty($order)) {
            $this->addFlash('danger', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart');
        }

        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour pouvoir passer commande.');
            return $this->redirectToRoute('app_cart');
        }

        if (!$this->getUser()->isEmailAuthentificated()) {
            $this->addFlash('danger', 'Vous devez avoir votre email authentifié pour pouvoir passer commande.');
            return $this->redirectToRoute('app_cart');
        }

        foreach ($order as $product) {
            if ($product['type'] === "event") {
                $totalPrice = $product['eventPrice'] * $product['nbPlaces'] * 100;
                $productNameLong = $product['eventTitle'];
            } elseif ($product['type'] === "rental") {
                $nbDay = $product['dateStart']->diff($product['dateEnd'])->days + 1;
                $pricePerDay = $product['rentalIsOnPromotion'] ? floor($product['rentalPricePerDay'] * 90) : floor($product['rentalPricePerDay'] * 100);
                $totalPrice = (int) round($nbDay * $pricePerDay);
                $productNameIni = $product['rentalTitle'];
                $productNameLong = '"' . $product['rentalTitle'] . '" du ' . $product['dateStart']->format('d-m-Y') . " au " . $product['dateEnd']->format('d-m-Y');
            }

            $productStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $totalPrice,
                    'product_data' => [
                        'name' => $productNameLong ,
                    ],
                ],
                'quantity' => 1,
            ];

            if ($product['type'] === "rental") {
                $productStripe[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => 5000,
                        'product_data' => [
                            'name' => 'Frais de nettoyage pour "' . $productNameIni . '"',
                        ],
                    ],
                    'quantity' => 1,
                ];
                $productStripe[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => ($product['rentalPricePerDay'] * 2) * 100,
                        'product_data' => [
                            'name' => 'Caution pour "' . $productNameIni . '"',
                        ],
                    ],
                    'quantity' => 1,
                ];
            }
        }

        //Do a json cart because a limit of 500char send
        $orderChange = [];

        foreach ($order as $elem){
            if($elem['type'] == "rental"){
                $orderChange[] = [
                    'type' => $elem['type'],
                    'id' => $elem['rentalId'],
                    'dateStart' => $elem['dateStart']->format('d-m-Y'),
                    'dateEnd' => $elem['dateEnd']->format('d-m-Y'),
                ];
            }else if($elem['type'] == "event"){
                $orderChange[] = [
                    'type' => $elem['type'],
                    'id' => $elem['eventId'],
                    'nbPlaces' => $elem['nbPlaces'],
                ];
            }
        }

        Stripe::setApiKey($this->stripeSecretKey);

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => $productStripe,
            'mode' => 'payment',
            'success_url' => $this->urlGenerator->generate('app_payment_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->urlGenerator->generate('app_payment_stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'metadata' => [
                'user_id' => $this->getUser()->getId(),
                'cart' => json_encode($orderChange),
            ],
        ]);

        $session->set('stripe_checkout_url', $checkout_session->url);
        $session->set('stripe_checkout_id', $checkout_session->id);

        return $this->redirectToRoute('app_payment_stripe_redirect');
    }


    #[Route('/cart/redirect-to-stripe', name: 'app_payment_stripe_redirect', methods: ['GET'])]
    public function redirectToStripe(SessionInterface $session): Response
    {
        $url = $session->get('stripe_checkout_url');

        if (!$url) {
            $this->addFlash('danger', 'Erreur de redirection vers Stripe.');
            return $this->redirectToRoute('app_cart');
        }

        return $this->render('payment/redirect.html.twig', [
            'checkoutUrl' => $url,
        ]);
    }

    #[Route('/cart/success', name: 'app_payment_stripe_success')]
    public function stripeSuccess(SessionInterface $session): Response
    {
        $checkoutId = $session->get('stripe_checkout_id');

        if (!$checkoutId) {
            $this->addFlash('danger', 'Session invalide.');
            return $this->redirectToRoute('app_cart');
        }

        Stripe::setApiKey($this->stripeSecretKey);
        $stripeSession = Session::retrieve($checkoutId);

        if ($stripeSession->payment_status !== 'paid') {
            $this->addFlash('danger', 'Le paiement n\'a pas été validé.');
            return $this->redirectToRoute('app_cart');
        }

        $session->remove('myCart', []);
        $this->addFlash('success', 'Votre paiement est validé. Merci pour votre commande !');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/cancel', name: 'app_payment_stripe_cancel')]
    public function stripeCancel(SessionInterface $session): Response
    {
        $checkoutId = $session->get('stripe_checkout_id');

        if (!$checkoutId) {
            $this->addFlash('danger', 'Session invalide.');
            return $this->redirectToRoute('app_cart');
        }

        $this->addFlash('danger','Erreur lors du paiement.');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/webhook/stripe', name: 'app_stripe_webhook', methods: ['POST'])]
    public function stripeWebhook(Request $request, EntityManagerInterface $entityManager, RentalsRepository $rentalsRepository, EventsRepository $eventsRepository, UsersRepository $usersRepository, MailerService $mailerService): Response
    {

        $endpointSecret = $this->endpointSecret;
        $payload = $request->getContent();
        $sig_header = $request->headers->get('stripe-signature');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return new Response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            return new Response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            try {
                $sessionStripe = $event->data->object;

                $cartJson = $sessionStripe->metadata->cart ?? null;
                $cart = $cartJson ? json_decode($cartJson, true) : [];

                $userId = $sessionStripe->metadata->user_id ?? null;
                $user = $usersRepository->find($userId);

                if (!$user){
                    return new Response('User not found', 400);
                }

                $newBill = new Bills();
                $newBill->setDate(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));
                $totalPriceBill = 0;

                $allResRentals = [];
                $allResEvents = [];

                foreach ($cart as $reservation) {
                    if ($reservation['type'] === "rental") {
                        $rental = $rentalsRepository->find($reservation['id']);
                        if (!$rental){
                            throw new \Exception("Rental not found for ID: " . $reservation['id']);
                        }

                        $start = new \DateTimeImmutable($reservation['dateStart']);
                        $end = new \DateTimeImmutable($reservation['dateEnd']);

                        $nbDay = $start->diff($end)->days + 1;


                        $pricePerDay = $rental->isOnPromotion() ? floor($rental->getPricePerDay() * 0.9 * 100) / 100 : floor($rental->getPricePerDay() * 100) / 100;
                        $totalPrice = floor($nbDay * $pricePerDay * 100) / 100;
                        $priceCleaningDeposit = 50;
                        $deposit = ($rental->getPricePerDay() * 2);
                        $totalPriceBill += $totalPrice + $priceCleaningDeposit + ($rental->getPricePerDay() * 2);

                        $reservationRental = new ReservationsRentals();
                        $reservationRental->setBill($newBill);
                        $reservationRental->setUser($user);
                        $reservationRental->setRentals($rental);
                        $reservationRental->setTotalPrice($totalPrice + $priceCleaningDeposit + $deposit);
                        $reservationRental->setTotalDepositReturned(0);
                        $reservationRental->setStatusBaseDeposit(0);
                        $reservationRental->setIsRefund(false);
                        $reservationRental->setDateReservation(new \DateTimeImmutable('now'));
                        $reservationRental->setDateStart(new \DateTimeImmutable($reservation['dateStart']));
                        $reservationRental->setDateEnd(new \DateTimeImmutable($reservation['dateEnd']));
                        $reservationRental->setStatusReservation(1);

                        $allResRentals[] = $reservationRental;
                        $entityManager->persist($reservationRental);
                    } elseif ($reservation['type'] === "event") {

                        $event = $eventsRepository->find($reservation['id']);
                        if (!$event){
                            throw new \Exception("Event not found for ID: " . $reservation['id']);
                        }

                        $reservationEvent = new ReservationsEvents();
                        $totalPrice = ($event->getPrice() * $reservation['nbPlaces']);
                        $totalPriceBill += $totalPrice;

                        $reservationEvent->setEvent($event);
                        $reservationEvent->setNbPlaces($reservation['nbPlaces']);
                        $reservationEvent->setIsActive(1);
                        $reservationEvent->setUser($user);
                        $reservationEvent->setBill($newBill);
                        $reservationEvent->setIsRefund(false);
                        $reservationEvent->setDateReservation(new \DateTimeImmutable('now'));
                        $reservationEvent->setTotalDeposit($totalPrice);
                        $reservationEvent->setTotalDepositReturned(0);


                        $allResEvents[] = $reservationEvent;
                        $entityManager->persist($reservationEvent);
                    }
                }

                $newBill->setDate(new \DateTimeImmutable());
                $newBill->setTotalPrice($totalPriceBill);
                $newBill->setStatus(1);
                $newBill->setUser($user);
                $newBill->setPaymentIntentId($sessionStripe->payment_intent);

                $entityManager->persist($newBill);
                $entityManager->flush();


                $mailerService->sendCommand(
                    $user->getEmail(),
                    $user->getFirstname(),
                    $newBill,
                    $allResEvents,
                    $allResRentals,
                );

                return new Response('Webhook reçu', 200);
            } catch (\Throwable $e) {
                error_log('Stripe webhook error: ' . $e->getMessage());
                return new Response('Webhook error', 500);
            }
        }

        return new Response('Webhook reçu', 200);
    }

}
