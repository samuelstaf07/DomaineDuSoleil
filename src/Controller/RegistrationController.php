<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Services\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, UsersRepository $usersRepository, MailerService $mailerService, VerifyEmailHelperInterface $verifyEmailHelper): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $image = new Images();
        $image->setId(1)->setAlt('mon image')->setSrc('source');
        $image->setIsHomePage(0);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword))
                ->setIsActive(1)
                ->setAccountNumber('0')
                ->setLastLogAt(new \DateTimeImmutable())
                ->setNbPoints(0)
                ->setIsEmailAuthentificated(false)
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setImage($image)
            ;

            if ($usersRepository->count([]) === 0) {
                $user->setRoles(['ROLE_ADMIN']);
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

            $this->addFlash('success', 'Un email de confirmation vous a été envoyé.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
