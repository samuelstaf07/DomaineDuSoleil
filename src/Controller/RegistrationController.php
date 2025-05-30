<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Services\MailerService;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(SessionInterface$session, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UsersRepository $usersRepository, MailerService $mailerService, VerifyEmailHelperInterface $verifyEmailHelper): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $image = new Images();
        $image->setId(1)->setAlt('Image de profil de l\'utilisateur')->setSrc('null');
        $image->setIsHomePage(0);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword))
                ->setIsActive(1)
                ->setLastLogAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')))
                ->setIsEmailAuthentificated(false)
                ->setUpdatedAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')))
                ->setCreatedAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')))
                ->setBirthDate($user->getBirthDate())
                ->setImage($image)
            ;

            if ($usersRepository->count([]) === 0) {
                $user->setRoles(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            $mailerService->sendEmailConfirmation(
                $user->getEmail(),
                $user->getFirstname(),
                $signatureComponents->getSignedUrl()
            );

            $this->addFlash('success', 'Un email de confirmation vous a été envoyé. Vous pouvez vous connecter mais vous serez limité.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
