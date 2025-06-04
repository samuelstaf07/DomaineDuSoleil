<?php

namespace App\Controller;

use App\Entity\Images;
use App\Form\ChangeMailType;
use App\Form\ImageUserType;
use App\Form\UserModifyFormType;
use App\Repository\BillsRepository;
use App\Repository\ReservationsEventsRepository;
use App\Repository\ReservationsRentalsRepository;
use App\Repository\UsersRepository;
use App\Services\MailerService;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
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

        $reservationWithoutComment = [];
        foreach ($reservationsRentalsRepository->findFinishedReservations($this->getUser()) as $reservation){
            if(!$reservation->getRentals()->hasCommentByUser($this->getUser())){
                $reservationWithoutComment[] = $reservation;
            }
        }

        return $this->render('account/index.html.twig', [
            'finishedReservationsRentals' => $reservationWithoutComment,
            'reservationsRentals' => $reservationsRentalsRepository->findCurrentAndUpcomingByUser($user),
            'reservationsEvents' => $reservationsEventsRepository->findUpcomingReservationsByUser($user),
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/account/{section}', name: 'app_account_section')]
    public function section(
        string $section,
        Request $request,
        EntityManagerInterface $entityManager,
        MailerService $mailerService,
        VerifyEmailHelperInterface $verifyEmailHelper,
        SluggerInterface $slugger,
        UserPasswordHasherInterface $passwordHasher,
        UsersRepository $usersRepository,
        BillsRepository $billsRepository,
        ReservationsRentalsRepository $reservationsRentalsRepository,
        ReservationsEventsRepository $reservationsEventsRepository
    ): Response
    {
        $views = [
            'bills' => 'account/_bills.html.twig',
            'rentals' => 'account/_reservationsrentals.html.twig',
            'events' => 'account/_reservationsevents.html.twig',
            'settings' => 'account/_settings.html.twig',
            'modify' => 'account/_modify.html.twig',
            'passwordmodify' => 'account/_passwordmodify.html.twig',
            'deleteaccount' => 'account/_deleteaccount.html.twig',
            'changeimageuser' => 'account/_changeimageuser.html.twig',
            'changemail' => 'account/_changemail.html.twig',
        ];

        if($section == "modify"){
            $user = $this->getUser();
            $form = $this->createForm(UserModifyFormType::class, $user);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $user->setUpdatedAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));
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

            if (in_array('ROLE_ADMIN', $user->getRoles())) {

                $reservationWithoutComment = [];
                foreach ($reservationsRentalsRepository->findFinishedReservations($this->getUser()) as $reservation){
                    if(!$reservation->getRentals()->hasCommentByUser($this->getUser())){
                        $reservationWithoutComment[] = $reservation;
                    }
                }

                return $this->render('account/index.html.twig', [
                    'finishedReservationsRentals' => $reservationWithoutComment,
                    'reservationsRentals' => $reservationsRentalsRepository->findCurrentAndUpcomingByUser($user),
                    'reservationsEvents' => $reservationsEventsRepository->findUpcomingReservationsByUser($user),
                ]);
            }

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

        if($section == "changeimageuser"){
            $user = $this->getUser();
            $form = $this->createForm(ImageUserType::class, $user);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $file = $form->get('image')->getData();

                if ($file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                    try {
                        $file->move(
                            $this->getParameter('uploads_directory'), $newFilename
                        );

                        $imagine = new Imagine();
                        $imagePath = $this->getParameter('uploads_directory') . '/' . $newFilename;

                        $imagine->open($imagePath)
                            ->thumbnail(
                                new Box(300, 300),
                                ManipulatorInterface::THUMBNAIL_OUTBOUND
                            )
                            ->save($imagePath);

                    } catch (FileException $e) {
                        $this->addFlash('danger', 'Erreur lors de l’envoi du fichier.');
                        return $this->redirectToRoute('app_account', [], 303);
                    }

                    $image = new Images();
                    $image->setSrc($newFilename);
                    $image->setAlt('Image de profil de ' . $user->getEmail());
                    $image->setIsHomePage(false);

                    $user->setImage($image);
                    $user->setUpdatedAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));

                    $entityManager->persist($user);
                    $entityManager->flush();
                }


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

        if($section == "changemail"){
            $user = $this->getUser();
            $form = $this->createForm(ChangeMailType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
                    $this->addFlash('danger', 'Mot de passe incorrect.');
                    return $this->redirectToRoute('app_account');
                }else if($usersRepository->emailExists($data['newEmail'])){
                    $this->addFlash('danger', 'Email déja utilisé.');
                    return $this->redirectToRoute('app_account');
                }else {
                    $mailerService->sendChangeMail(
                        $user->getEmail(),
                        $user->getFirstname(),
                        $data['newEmail'],
                    );
                    $mailerService->sendChangeMail(
                        $data['newEmail'],
                        $user->getFirstname(),
                        $data['newEmail'],
                    );

                    $user->setEmail($data['newEmail']);
                    $user->setUpdatedAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));
                    $entityManager->flush();

                    $this->addFlash('success', 'Votre adresse e-mail a été modifiée.');
                    return $this->redirectToRoute('app_account');
                }
            }

            return $this->render('account/_changemail.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        if($section == "sendVerifyEmail"){

            if(!$this->getUser()->isEmailAuthentificated()){
                $signatureComponents = $verifyEmailHelper->generateSignature(
                    'app_verify_email',
                    $this->getUser()->getId(),
                    $this->getUser()->getEmail(),
                    ['id' => $this->getUser()->getId()]
                );

                $mailerService->sendEmailConfirmation(
                    $this->getUser()->getEmail(),
                    $this->getUser()->getFirstname(),
                    $signatureComponents->getSignedUrl()
                );
                $this->addFlash('success', 'Un email de confirmation vous a été envoyé.');
            }else{
                $this->addFlash('warning', 'Votre email est déja vérifié.');
            }

            return $this->redirectToRoute('app_account');
        }

        return $this->render($views[$section],[
            'bills' => $billsRepository->findActiveBillsByUser($this->getUser()),
            'user' => $this->getUser(),
        ]);
    }
}
