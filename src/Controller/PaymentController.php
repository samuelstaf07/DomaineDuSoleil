<?php

namespace App\Controller;

use App\Services\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class PaymentController extends AbstractController
{
    private UrlGeneratorInterface $urlGenerator;
    private string $stripeSecretKey;

    public function __construct(UrlGeneratorInterface $urlGenerator, string $stripeSecretKey){
        $this->urlGenerator = $urlGenerator;
        $this->stripeSecretKey = $stripeSecretKey;
    }


    #[Route('/cart/create-session-stripe', name: 'app_payment_stripe')]
    public function createCheckoutSession(Request $request, SessionInterface $session, CsrfTokenManagerInterface $csrfTokenManager): Response
    {

        $productStripe = [];
        $order = $session->get('myCart', []);

        if(empty($order)){
            $this->addFlash('warning', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart');
        }

        foreach ($order as $product){
            if($product['type'] == "event"){
                $totalPrice = $product['eventPrice'] * $product['nbPlaces'] * 100;
                $productName = $product['eventTitle'];
            }else if($product['type'] == "rental"){
                $nbDay = $product['dateStart']->diff($product['dateEnd'])->days + 1;

                if ($product['rentalIsOnPromotion']) {
                    $pricePerDay = floor($product['rentalPricePerDay'] * 90);
                } else {
                    $pricePerDay = floor($product['rentalPricePerDay'] * 100);
                }

                $totalPriceDay = $nbDay * $pricePerDay;
                $totalPrice = (int) round($totalPriceDay);
                $productName = $product['rentalTitle'];
            }

            $productStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $totalPrice,
                    'product_data' => [
                        'name' => $productName,
                    ]
                ],
                'quantity' => 1
            ];
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

        return $this->render('payment/success.html.twig');
    }

    #[Route('/cart/cancel', name: 'app_payment_stripe_cancel')]
    public function stripeCancel(SessionInterface $session): Response
    {
        return $this->render('payment/cancel.html.twig');
    }
}
