<?php

namespace App\Controller\admin;

use App\Entity\Users;
use App\Repository\CommentsRepository;
use App\Repository\RentalsRepository;
use App\Repository\UsersRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/users')]
final class UsersController extends AbstractController
{
    #[Route(name: 'app_users_index', methods: ['GET'])]
    public function index(UsersRepository $usersRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page',1);
        $sort = $request->query->getString('sort', 'null');
        $direction = $request->query->getString('direction', 'null');

        $users = $usersRepository->createQueryBuilder('user');

        $pagination = $paginator->paginate(
            $users,
            $request->query->getInt('page', $page),
            20
        );

        return $this->render('admin/users/index.html.twig', [
            'users' => $pagination,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/{id}', name: 'app_users_show', methods: ['GET'])]
    public function show(Users $user): Response
    {
        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/changeActive', name: 'app_users_change_active')]
    public function delete($id, UsersRepository $usersRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $usersRepository->find($id);

        if(in_array('ROLE_ADMIN', $user->getRoles())){
            $this->addFlash("danger", "Vous ne pouvez pas modifier un administrateur");
            return $this->redirectToRoute('app_users_index');
        }

        $user->setIsActive(!$user->isActive());
        $user->setUpdatedAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));
        $entityManager->persist($user);
        $entityManager->flush();

        if($user->isActive()){
            $this->addFlash("success", "Le compte de l'utilisateur a été activé.");
        }else{
            $this->addFlash("success", "Le compte de l'utilisateur a été désactivé.");
        }

        return $this->redirectToRoute('app_users_show', [
            'id' => $id,
        ]);
    }

    #[Route('/{id}/reservations', name: 'app_users_reservations')]
    public function reservationsRentals($id, UsersRepository $usersRepository): Response
    {
        $user = $usersRepository->find($id);

        $reservationsMin = [];
        $reservations = $user->getReservationsRentals();

        foreach($reservations as $reservation){
            $reservationsMin[] = [
                'title' => 'Logement: ' . $reservation->getRentals()->getTitle(),
                'start' => $reservation->getDateStart()->format('Y-m-d'),
                'end' => $reservation->getDateEnd()->modify('+1 day')->format('Y-m-d'),
            ];
        }

        $reservations = $user->getReservationsEvents();

        foreach($reservations as $reservation){
            $reservationsMin[] = [
                'title' => 'Événement: ' . $reservation->getEvent()->getTitle(),
                'start' => $reservation->getEvent()->getDate()->format('Y-m-d'),
                'end' => $reservation->getEvent()->getDate()->modify('+1 day')->format('Y-m-d'),
            ];
        }

        return $this->render('admin/reservations/reservationsUser.html.twig', [
            'user' => $user,
            'reservationsMin' => $reservationsMin,
            'reservationsRentals' => $user->getReservationsRentals(),
            'reservationsEvents' => $user->getReservationsEvents(),
        ]);
    }

    #[Route('/{id}/comments', name: 'app_users_comments')]
    public function comments($id, UsersRepository $usersRepository, CommentsRepository $commentsRepository): Response
    {
        $user = $usersRepository->find($id);
        $comments = $user->getComments();

        return $this->render('admin/users/comments.html.twig', [
            'user' => $user,
            'comments' => $comments,
        ]);
    }

    #[Route('/{id}/bills', name: 'app_users_bills')]
    public function bills($id, UsersRepository $usersRepository, CommentsRepository $commentsRepository): Response
    {
        $user = $usersRepository->find($id);
        $bills = $user->getBills();

        return $this->render('admin/users/bill.html.twig', [
            'user' => $user,
            'bills' => $bills,
        ]);
    }
}
