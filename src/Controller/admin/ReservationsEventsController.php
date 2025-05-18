<?php

namespace App\Controller\admin;

use App\Entity\ReservationsEvents;
use App\Form\RefundType;
use App\Form\ReservationsEventsType;
use App\Repository\ReservationsEventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\Refund;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/admin/reservations/events')]
final class ReservationsEventsController extends AbstractController
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

    #[Route(name: 'app_reservations_events_index', methods: ['GET'])]
    public function index(ReservationsEventsRepository $reservationsEventsRepository): Response
    {
        return $this->render('admin/reservations_events/index.html.twig', [
            'reservations_events' => $reservationsEventsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reservations_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservationsEvent = new ReservationsEvents();
        $form = $this->createForm(ReservationsEventsType::class, $reservationsEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservationsEvent);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservations_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservations_events/new.html.twig', [
            'reservations_event' => $reservationsEvent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservations_events_show', methods: ['GET'])]
    public function show(ReservationsEvents $reservationsEvent): Response
    {
        return $this->render('admin/reservations_events/show.html.twig', [
            'reservations_event' => $reservationsEvent,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservations_events_edit', methods: ['GET', 'POST'])]
    public function edit($id, Request $request, ReservationsEventsRepository $reservationsEventsRepository, ReservationsEvents $reservationsEvent, EntityManagerInterface $entityManager): Response
    {
        $reservationsEventUser = $reservationsEventsRepository->find($id);

        if($reservationsEventUser->getBill()->getPaymentIntentId()){
            $this->addFlash('danger', 'Vous ne pouvez pas modifier une réservation fait par un utilisateur.');
            $referer = $request->headers->get('referer');
            if ($referer) {
                return $this->redirect($referer);
            }
        }

        $form = $this->createForm(ReservationsEventsType::class, $reservationsEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservations_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservations_events/edit.html.twig', [
            'reservations_event' => $reservationsEvent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservations_events_delete', methods: ['POST'])]
    public function delete(Request $request, ReservationsEvents $reservationsEvent, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservationsEvent->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservationsEvent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservations_events_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/refund', name: 'app_reservations_events_refund')]
    public function refund($id, ReservationsEventsRepository $reservationsEventsRepository, EntityManagerInterface $entityManager, Request $request): Response
    {

        $reservationsEvents = $reservationsEventsRepository->find($id);

        if (!$reservationsEvents->getBill()->getPaymentIntentId()) {
            $referer = $request->headers->get('referer');
            $this->addFlash('danger', 'Cette facture n\'est pas liée à un paiement Stripe.');
            if ($referer) {
                return $this->redirect($referer);
            }
        }

        if($reservationsEvents->getTotalDeposit() <= $reservationsEvents->getTotalDepositReturned()){
            $referer = $request->headers->get('referer');
            $this->addFlash('danger', 'Vous ne pouvez pas rembourser un élément déja remboursé.');
            if ($referer) {
                return $this->redirect($referer);
            }
        }

        Stripe::setApiKey($this->stripeSecretKey);

        $form = $this->createForm(RefundType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $amount = $form->get('amount')->getData();

            $maxRefundable = $reservationsEvents->getTotalDeposit();
            if ($amount > $maxRefundable) {
                $this->addFlash('danger', 'Le montant dépasse le montant total payé.');
                $referer = $request->headers->get('referer');
                if ($referer) {
                    return $this->redirect($referer);
                }
            } else {
                Stripe::setApiKey($this->stripeSecretKey);

                try {
                    Refund::create([
                        'payment_intent' => $reservationsEvents->getBill()->getPaymentIntentId(),
                        'amount' => ((int) round($amount * 100)),
                    ]);

                    $reservationsEvents->setIsRefund(true);

                    if($reservationsEvents->getTotalDepositReturned() == null){
                        $reservationsEvents->setTotalDepositReturned($amount);
                    }else{
                        $reservationsEvents->setTotalDepositReturned($reservationsEvents->getTotalDepositReturned() + $amount);
                    }

                    if($reservationsEvents->getTotalDeposit() <= $reservationsEvents->getTotalDepositReturned()){
                        $reservationsEvents->setIsActive(false);
                    }

                    $entityManager->persist($reservationsEvents);
                    $entityManager->flush();

                    $this->addFlash('success', 'Remboursement effectué avec succès.');

                } catch (ApiErrorException $e) {
                    $this->addFlash('danger', 'Erreur Stripe : ' . $e->getMessage());
                }

                return $this->redirectToRoute('app_reservations_events_show', [
                    'id' => $reservationsEvents->getId(),
                ]);
            }
        }


        return $this->render('admin/refund/event.html.twig', [
            'form' => $form->createView(),
            'event' => $reservationsEvents->getEvent(),
            'user' => $reservationsEvents->getUser(),
            'reservation' => $reservationsEvents,
        ]);
    }
    #[Route('/{id}/changeActive', name: 'app_reservations_events_change_active')]
    public function changeActive($id, ReservationsEventsRepository $reservationsEventsRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $reservationsEvents = $reservationsEventsRepository->find($id);

        if($reservationsEvents->getIsActive()){
            $reservationsEvents->setIsActive(!$reservationsEvents->getIsActive());

            $entityManager->persist($reservationsEvents);
            $entityManager->flush();

            $this->addFlash('success', 'La réservation a été désactivé');
        }else{
            if($reservationsEvents->getBill()->getPaymentIntentId() == null){
                $reservationsEvents->setIsActive(!$reservationsEvents->getIsActive());

                $entityManager->persist($reservationsEvents);
                $entityManager->flush();

                $this->addFlash('success', 'La réservation a été activé');
            }else{
                $this->addFlash('danger', 'Vous ne pouvez pas activer une réservations désactivé.');
            }
        }

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_reservations_events_index', [], Response::HTTP_SEE_OTHER);
    }

}
