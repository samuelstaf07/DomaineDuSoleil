<?php

namespace App\Controller;

use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class EmailVerificationController extends AbstractController
{
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        VerifyEmailHelperInterface $verifyEmailHelper,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $userId = $request->get('id');
        if (!$userId || !($user = $usersRepository->find($userId))) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_login');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmation($request->getUri(), $userId, $user->getEmail());
            $user->setIsEmailAuthentificated(true);
            $entityManager->flush();
            $this->addFlash('success', 'Votre email a bien été vérifié. Vous pouvez vous connecter mais vous serez limité.');
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', 'Le lien de vérification est invalide ou a expiré.');
        }

        return $this->redirectToRoute('app_login');
    }
}
