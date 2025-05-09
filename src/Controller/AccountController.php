<?php

namespace App\Controller;

use App\Form\PasswordModifyFormType;
use App\Form\UserModifyFormType;
use App\Repository\ReservationsEventsRepository;
use App\Repository\ReservationsRentalsRepository;
use App\Services\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class AccountController extends AbstractController
{
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/account', name: 'app_account')]
    public function index(ReservationsRentalsRepository $reservationsRentalsRepository, ReservationsEventsRepository $reservationsEventsRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger','Vous devez être connecté pour accéder à cette section.');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('account/index.html.twig', [
            'reservationsRentals' => $reservationsRentalsRepository->findCurrentAndUpcomingByUser($user),
            'reservationsEvents' => $reservationsEventsRepository->findUpcomingReservationsByUser($user),
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/account/{section}', name: 'app_account_section')]
    public function section(string $section, Request $request, EntityManagerInterface $entityManager, MailerService $mailerService, VerifyEmailHelperInterface $verifyEmailHelper): Response
    {
        $views = [
            'bills' => 'account/_bills.html.twig',
            'rentals' => 'account/_reservationsrentals.html.twig',
            'events' => 'account/_reservationsevents.html.twig',
            'settings' => 'account/_settings.html.twig',
            'modify' => 'account/_modify.html.twig',
            'passwordmodify' => 'account/_passwordmodify.html.twig',
            'deleteaccount' => 'account/_deleteaccount.html.twig',
        ];

        if($section == "modify"){
            $user = $this->getUser();
            $form = $this->createForm(UserModifyFormType::class, $user);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
                return $this->redirectToRoute('app_account', [], 303);
            }else if($form->isSubmitted() && !$form->isValid()){
                $this->addFlash('danger', 'Vos informations n\'ont pas été mises à jour.');
                return $this->redirectToRoute('app_account', [], 303);
            }

            return $this->render($views[$section], [
                'form' => $form->createView(),
            ]);
        }


        if($section == "passwordmodify"){
            $user = $this->getUser();

            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_password_reset',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            $mailerService->sendPasswordReset(
                $user->getEmail(),
                $user->getFirstname(),
                $signatureComponents->getSignedUrl()
            );

            return $this->render('account/_passwordsend.html.twig');
        }


        if($section == "deleteaccount"){
            $user = $this->getUser();

            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_delete_account',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            $mailerService->sendDeleteAccount(
                $user->getEmail(),
                $user->getFirstname(),
                $signatureComponents->getSignedUrl()
            );

            return $this->render('account/_deletedaccount.html.twig');
        }

        return $this->render($views[$section],[
            'bills' => $this->getUser()->getBills(),
        ]);
    }
}
