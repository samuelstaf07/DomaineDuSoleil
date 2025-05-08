<?php

namespace App\Controller;

use App\Form\UserModifyFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AccountController extends AbstractController
{
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette section.');
        }
        return $this->render('account/index.html.twig');
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/account/modify', name: 'app_account_modify')]
    public function modify(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserModifyFormType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/modify.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/account/{section}', name: 'app_account_section')]
    public function section(string $section): Response
    {
        $views = [
            'bills' => 'account/_bills.html.twig',
            'rentals' => 'account/_reservationsrentals.html.twig',
            'events' => 'account/_reservationsevents.html.twig',
            'settings' => 'account/_settings.html.twig',
        ];

        return $this->render($views[$section],[
            'bills' => $this->getUser()->getBills(),
        ]);
    }
}
