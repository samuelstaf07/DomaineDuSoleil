<?php

namespace App\Controller;

use App\Form\PasswordModifyFormType;
use App\Repository\UsersRepository;
use App\Services\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
        if (!$userId || !($user = $usersRepository->find($userId)) || !$user->isActive()) {
            $this->addFlash('danger', 'Erreur de données');
            return $this->redirectToRoute('app_login');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmation($request->getUri(), $userId, $user->getEmail());
            $user->setIsEmailAuthentificated(true);
            $entityManager->flush();
            $this->addFlash('success', 'Votre email a bien été vérifié.');
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('danger', 'Le lien de vérification est invalide ou a expiré.');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route('/passwordReset', name: 'app_password_reset')]
    public function passwordReset(
        Request $request,
        VerifyEmailHelperInterface $verifyEmailHelper,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $userId = $request->get('id');
        if (!$userId || !($user = $usersRepository->find($userId)) || !$user->isActive()) {
            $this->addFlash('danger', 'Erreur de données');
            return $this->redirectToRoute('app_login');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmation($request->getUri(), $userId, $user->getEmail());

            $form = $this->createForm(PasswordModifyFormType::class, $user);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $plainPassword = $form->get('plainPassword')->getData();

                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été changé.');
                return $this->redirectToRoute('app_login', [], 303);
            }else if($form->isSubmitted() && !$form->isValid()){
                $this->addFlash('danger', 'Votre mot de passe n\'a pas été changé.');
            }

            return $this->render('account/_passwordmodify.html.twig', [
                'form' => $form->createView(),
            ]);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('danger', 'Le lien de vérification est invalide ou a expiré.');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route('/deleteAccount', name: 'app_delete_account')]
    public function deleteAccount(
        Request $request,
        VerifyEmailHelperInterface $verifyEmailHelper,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailerService $mailerService
    ): Response {
        $userId = $request->get('id');
        if (!$userId || !($user = $usersRepository->find($userId)) || !$user->isActive()) {
            $this->addFlash('danger', 'Erreur de données');
            return $this->redirectToRoute('app_login');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmation($request->getUri(), $userId, $user->getEmail());
            $this->getUser()->setIsActive(0);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a été supprimé. Pour revenir en arrière, veuillez nous contacter à info@domainedusoleil.be');

            $mailerService->sendInfoDeletedAccount(
                $user->getEmail(),
                $user->getFirstname()
            );
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('danger', 'Le lien de vérification est invalide ou a expiré.');
        }

        return $this->redirectToRoute('app_login');
    }
}
