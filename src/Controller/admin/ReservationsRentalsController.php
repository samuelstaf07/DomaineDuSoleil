<?php

namespace App\Controller\admin;

use App\Entity\ReservationsRentals;
use App\Form\RefundType;
use App\Form\ReservationsRentalsFullType;
use App\Form\ReservationsRentalsType;
use App\Form\StatusReservationType;
use App\Repository\RentalsRepository;
use App\Repository\ReservationsRentalsRepository;
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

#[Route('/admin/reservations/rentals')]
final class ReservationsRentalsController extends AbstractController
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

    #[Route(name: 'app_reservations_rentals_index', methods: ['GET'])]
    public function index(ReservationsRentalsRepository $reservationsRentalsRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page',1);
        $sort = $request->query->getString('sort', 'null');
        $direction = $request->query->getString('direction', 'null');

        $reservationsRentals = $reservationsRentalsRepository->createQueryBuilder('reservations_rental')
            ->leftJoin('reservations_rental.rentals', 'rentals')
            ->addSelect('rentals')
            ->leftJoin('reservations_rental.user', 'user')
            ->addSelect('user')
            ->leftJoin('reservations_rental.bill', 'bill')
            ->addSelect('bill');

        $pagination = $paginator->paginate(
            $reservationsRentals,
            $request->query->getInt('page', $page),
            20
        );

        return $this->render('admin/reservations_rentals/index.html.twig', [
            'reservations_rentals' => $pagination,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'app_reservations_rentals_new_step1')]
    public function newStep1(Request $request): Response
    {
        $reservationsRental = new ReservationsRentals();
        $form = $this->createForm(ReservationsRentalsFullType::class, $reservationsRental, [
            'step' => 1
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            return $this->redirectToRoute('app_reservations_rentals_new_step2', [
                'idUser' => $reservationsRental->getUser()->getId(),
                'idRental' => $reservationsRental->getRentals()->getId(),
            ]);
        }

        return $this->render('admin/reservations_rentals/new.html.twig', [
            'reservations_rental' => $reservationsRental,
            'form' => $form,
            'step' => 1,
        ]);
    }

    #[Route('/new/{idUser}/{idRental}', name: 'app_reservations_rentals_new_step2')]
    public function newStep2($idUser, $idRental, Request $request, EntityManagerInterface $entityManager, RentalsRepository $rentalsRepository, UsersRepository $usersRepository): Response
    {
        $user = $usersRepository->find($idUser);
        $rental = $rentalsRepository->find($idRental);

        if(!$user || !$rental){
            $this->addFlash('danger', 'Utilisateur ou logement incorrect.');
            return $this->redirectToRoute('app_reservations_rentals_new_step1');
        }

        $reservationsRental = new ReservationsRentals();
        $reservationsRental->setUser($user);
        $reservationsRental->setRentals($rental);

        $reservedDates = [];

        foreach ($rental->getUpcomingReservations() as $reservation) {
            $reservedDates[] = [
                'title' => 'Réservé',
                'start' => $reservation->getDateStart()->format('Y-m-d'),
                'end' => $reservation->getDateEnd()->format('Y-m-d'),
            ];
        }

        $form = $this->createForm(ReservationsRentalsFullType::class, $reservationsRental, [
            'step' => 2
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $dateStart = $reservationsRental->getDateStart();
            $dateEnd = $reservationsRental->getDateEnd();

            if(!$rental->isActive()){
                $this->addFlash('danger', 'Vous ne pouvez pas réserver un logement inactif.');
                return $this->redirectToRoute('app_reservations_rentals_new_step1');
            }

            if ($dateStart > $dateEnd) {
                $this->addFlash('danger', 'La date de fin ne peut pas être antérieure à la date de début.');
                return $this->redirectToRoute('app_reservations_rentals_new_step1');
            }

            $userDates = [];
            $allDatesUser = new \DatePeriod($dateStart, new \DateInterval('P1D'), (clone $dateEnd)->modify('+1 day'));
            foreach ($allDatesUser as $date) {
                $userDates[] = $date->format('d-m-Y');
            }

            $reservedAllDates = [];
            foreach ($reservedDates as $date) {
                $start = new \DateTimeImmutable($date['start']);
                $end = new \DateTimeImmutable($date['end']);
                $rangePeriod = new \DatePeriod($start, new \DateInterval('P1D'), (clone $end)->modify('+1 day'));
                foreach ($rangePeriod as $dt) {
                    $reservedAllDates[] = $dt->format('d-m-Y');
                }
            }

            $check = false;
            foreach ($userDates as $date) {
                if (in_array($date, $reservedAllDates)) {
                    $check = true;
                    break;
                }
            }

            if ($check) {
                $this->addFlash('danger', 'Les dates sélectionnées sont déjà réservées.');
                return $this->redirectToRoute('app_reservations_rentals_new_step1');
            }


            $reservationsRental->setBill(null);
            $reservationsRental->setStatusReservation(1);
            $reservationsRental->setStatusBaseDeposit(0);
            $reservationsRental->setTotalDepositReturned(0);
            $reservationsRental->setTotalPrice(0);
            $reservationsRental->setDateReservation(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));
            $reservationsRental->setIsRefund(0);

            $entityManager->persist($reservationsRental);
            $entityManager->flush();

            $this->addFlash('success', 'La réservation a été ajoutée.');
            return $this->redirectToRoute('app_reservations_rentals_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservations_rentals/new.html.twig', [
            'reservedDates' => $reservedDates,
            'reservations_rental' => $reservationsRental,
            'form' => $form,
            'step' => 2,
            'user' => $reservationsRental->getUser(),
            'rentals' => $reservationsRental->getRentals(),
        ]);
    }

    #[Route('/{id}', name: 'app_reservations_rentals_show', methods: ['GET'])]
    public function show(ReservationsRentals $reservationsRental): Response
    {
        return $this->render('admin/reservations_rentals/show.html.twig', [
            'reservations_rental' => $reservationsRental,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservations_rentals_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReservationsRentals $reservationsRental, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StatusReservationType::class, $reservationsRental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservations_rentals_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservations_rentals/edit.html.twig', [
            'reservations_rental' => $reservationsRental,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_reservations_rentals_delete')]
    public function delete($id, ReservationsRentalsRepository $reservationsRentalsRepository, EntityManagerInterface $entityManager): Response
    {

        $reservationsRentals = $reservationsRentalsRepository->find($id);

        if($reservationsRentals->getBill() != null){
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer une réservation fait par un utilisateur.');
            return $this->redirectToRoute('app_reservations_rentals_show', [
                'id' => $reservationsRentals->getId(),
            ]);
        }

        $entityManager->remove($reservationsRentals);
        $entityManager->flush();

        $this->addFlash('success', 'La réservation a bien été supprimer.');
        return $this->redirectToRoute('app_reservations_rentals_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/refund', name: 'app_reservations_rentals_refund')]
    public function refund($id, ReservationsRentalsRepository $reservationsRentalsRepository, EntityManagerInterface $entityManager, Request $request): Response
    {

        $reservationsRentals = $reservationsRentalsRepository->find($id);

        if (!$reservationsRentals->getBill()->getPaymentIntentId()) {
            $referer = $request->headers->get('referer');
            $this->addFlash('danger', 'Cette facture n\'est pas liée à un paiement Stripe.');
            if ($referer) {
                return $this->redirect($referer);
            }
        }

        if($reservationsRentals->getTotalPrice() <= $reservationsRentals->getTotalDepositReturned()){
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

            $maxRefundable = $reservationsRentals->getTotalPrice();
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
                        'payment_intent' => $reservationsRentals->getBill()->getPaymentIntentId(),
                        'amount' => ((int) round($amount * 100)),
                    ]);

                    $reservationsRentals->setStatusBaseDeposit(true);

                    if($reservationsRentals->getTotalDepositReturned() == null){
                        $reservationsRentals->setTotalDepositReturned($amount);
                    }else{
                        $reservationsRentals->setTotalDepositReturned($reservationsRentals->getTotalDepositReturned() + $amount);
                    }

                    if($reservationsRentals->getTotalPrice() <= $reservationsRentals->getTotalDepositReturned()){
                        $reservationsRentals->setStatusReservation(false);
                    }

                    $entityManager->persist($reservationsRentals);
                    $entityManager->flush();

                    $this->addFlash('success', 'Remboursement effectué avec succès.');

                } catch (ApiErrorException $e) {
                    $this->addFlash('danger', 'Erreur Stripe : ' . $e->getMessage());
                }

                return $this->redirectToRoute('app_reservations_rentals_edit', [
                    'id' => $reservationsRentals->getId(),
                ]);
            }
        }


        return $this->render('admin/refund/rental.html.twig', [
            'form' => $form->createView(),
            'rental' => $reservationsRentals->getRentals(),
            'user' => $reservationsRentals->getUser(),
            'reservation' => $reservationsRentals,
        ]);
    }

    #[Route('/{id}/changeActive', name: 'app_reservations_rentals_change_active')]
    public function changeActive($id, Request $request, ReservationsRentalsRepository $reservationsRentalsRepository, EntityManagerInterface $entityManager, MailerService $mailerService): Response
    {
        $reservationsRentals = $reservationsRentalsRepository->find($id);

        $reservationsRentals->setStatusReservation(!$reservationsRentals->getStatusReservation());

        if(!$reservationsRentals->getStatusReservation()){
            $mailerService->sendDisabledReservationRental(
                $reservationsRentals->getUser()->getEmail(),
                $reservationsRentals->getUser()->getFirstname(),
                $reservationsRentals
            );
        }

        $entityManager->persist($reservationsRentals);
        $entityManager->flush();

        return $this->redirectToRoute('app_reservations_rentals_index');
    }
}
