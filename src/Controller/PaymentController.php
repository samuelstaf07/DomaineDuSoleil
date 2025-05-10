<?php

namespace App\Controller;

use App\Services\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
    public function createCheckoutSession(Request $request, SessionInterface $session): Response
    {
        $productStripe = [];
        $order = $session->get('myCart', []);

        if (empty($order)) {
            $this->addFlash('warning', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart');
        }

        foreach ($order as $product) {
            if ($product['type'] === "event") {
                $totalPrice = $product['eventPrice'] * $product['nbPlaces'] * 100;
                $productName = $product['eventTitle'];
            } elseif ($product['type'] === "rental") {
                $nbDay = $product['dateStart']->diff($product['dateEnd'])->days + 1;
                $pricePerDay = $product['rentalIsOnPromotion']
                    ? floor($product['rentalPricePerDay'] * 90)
                    : floor($product['rentalPricePerDay'] * 100);
                $totalPrice = (int) round($nbDay * $pricePerDay);
                $productName = $product['rentalTitle'];
            }

            $productStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $totalPrice,
                    'product_data' => [
                        'name' => $productName,
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
                            'name' => 'Caution de nettoyage pour ' . $productName,
                        ],
                    ],
                    'quantity' => 1,
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
    public function stripeWebhook(Request $request, EntityManagerInterface $em): Response
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
            $sessionStripe = $event->data->object;

            //ajouter dans la db
        }

        return new Response('Webhook reçu', 200);
    }
}
