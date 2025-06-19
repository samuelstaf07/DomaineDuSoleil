<?php

namespace App\Controller\admin;

use App\Entity\ReservationsEvents;
use App\Form\RefundType;
use App\Form\ReservationsEventsFullType;
use App\Form\ReservationsEventsType;
use App\Repository\EventsRepository;
use App\Repository\ReservationsEventsRepository;
use App\Repository\UsersRepository;
use App\Services\MailerService;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
    public function index(ReservationsEventsRepository $reservationsEventsRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page',1);
        $sort = $request->query->getString('sort', 'null');
        $direction = $request->query->getString('direction', 'null');

        $reservationsEvents = $reservationsEventsRepository->createQueryBuilder('reservations_event')
            ->leftJoin('reservations_event.event', 'event')
            ->addSelect('event')
            ->leftJoin('reservations_event.user', 'user')
            ->addSelect('user')
            ->leftJoin('reservations_event.bill', 'bill')
            ->addSelect('bill');

        $pagination = $paginator->paginate(
            $reservationsEvents,
            $request->query->getInt('page', $page),
            20
        );

        return $this->render('admin/reservations_events/index.html.twig', [
            'reservations_events' => $pagination,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'app_reservations_events_new_step1')]
    public function newStep1(Request $request): Response
    {
        $reservationsEvents = new ReservationsEvents();
        $form = $this->createForm(ReservationsEventsFullType::class, $reservationsEvents, [
            'step' => 1
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            return $this->redirectToRoute('app_reservations_events_new_step2', [
                'idUser' => $reservationsEvents->getUser()->getId(),
                'idEvent' => $reservationsEvents->getEvent()->getId(),
            ]);
        }

        return $this->render('admin/reservations_events/new.html.twig', [
            'reservations_event' => $reservationsEvents,
            'form' => $form,
            'step' => 1,
        ]);
    }

    #[Route('/new/{idUser}/{idEvent}', name: 'app_reservations_events_new_step2')]
    public function newStep2($idUser, $idEvent, Request $request, EntityManagerInterface $entityManager, EventsRepository $eventsRepository, UsersRepository $usersRepository): Response
    {
        $user = $usersRepository->find($idUser);
        $event = $eventsRepository->find($idEvent);

        if(!$user || !$event){
            $this->addFlash('danger', 'Utilisateur ou événement incorrect.');
            return $this->redirectToRoute('app_reservations_events_new_step1');
        }

        $reservationsEvents = new ReservationsEvents();
        $reservationsEvents->setUser($user);
        $reservationsEvents->setEvent($event);

        $form = $this->createForm(ReservationsEventsFullType::class, $reservationsEvents, [
            'step' => 2
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($event->isPast()){
                $this->addFlash('danger', 'Événement déjà passé.');
                return $this->redirectToRoute('app_reservations_events_new_step1');
            }

            if($reservationsEvents->getNbPlaces() > $event->getRemainingPlaces()){
                $this->addFlash('danger', 'Pas assez de places pour la demande.');
                return $this->redirectToRoute('app_reservations_events_new_step1');
            }


            $reservationsEvents->setTotalDeposit(0);
            $reservationsEvents->setTotalDepositReturned(0);
            $reservationsEvents->setDateReservation(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));
            $reservationsEvents->setBill(null);
            $reservationsEvents->setIsRefund(false);
            $reservationsEvents->setIsActive(true);

            $entityManager->persist($reservationsEvents);
            $entityManager->flush();

            $this->addFlash('success', 'La réservation a été ajoutée.');
            return $this->redirectToRoute('app_reservations_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservations_events/new.html.twig', [
            'reservations_event' => $reservationsEvents,
            'form' => $form,
            'step' => 2,
            'user' => $reservationsEvents->getUser(),
            'event' => $reservationsEvents->getEvent(),
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
            return $this->redirectToRoute('app_reservations_events_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(ReservationsEventsType::class, $reservationsEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La réservation a été modifiée.');
            return $this->redirectToRoute('app_reservations_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservations_events/edit.html.twig', [
            'reservations_event' => $reservationsEvent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_reservations_events_delete')]
    public function delete($id, ReservationsEventsRepository $reservationsEventsRepository, EntityManagerInterface $entityManager): Response
    {
        $reservationsEvents = $reservationsEventsRepository->find($id);

        if($reservationsEvents->getBill()){
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer une réservation fait par un utilisateur.');
            return $this->redirectToRoute('app_reservations_events_show', [
                'id' => $reservationsEvents->getId(),
            ]);
        }

        $entityManager->remove($reservationsEvents);
        $entityManager->flush();

        $this->addFlash('success', 'La réservation a bien été supprimer.');
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
    public function changeActive($id, ReservationsEventsRepository $reservationsEventsRepository, EntityManagerInterface $entityManager, Request $request, MailerService $mailerService): Response
    {
        $reservationsEvents = $reservationsEventsRepository->find($id);

        if($reservationsEvents->getIsActive()){
            $reservationsEvents->setIsActive(!$reservationsEvents->getIsActive());

            if(!$reservationsEvents->getIsActive()){
                $mailerService->sendDisabledReservationEvent(
                    $reservationsEvents->getUser()->getEmail(),
                    $reservationsEvents->getUser()->getFirstname(),
                    $reservationsEvents
                );
            }

            $entityManager->persist($reservationsEvents);
            $entityManager->flush();

            $this->addFlash('success', 'La réservation a été désactivé');
        }else{
            if($reservationsEvents->getBill() == null){
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
