<?php

namespace App\Controller\admin;

use App\Entity\Images;
use App\Entity\Rentals;
use App\Form\ImageRentalsType;
use App\Form\RentalsType;
use App\Repository\CommentsRepository;
use App\Repository\ImagesRepository;
use App\Repository\RentalsRepository;
use App\Repository\ReservationsRentalsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/rentals')]
final class RentalsController extends AbstractController
{
    #[Route(name: 'app_rentals_index', methods: ['GET'])]
    public function index(RentalsRepository $rentalsRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page',1);
        $sort = $request->query->getString('sort', 'null');
        $direction = $request->query->getString('direction', 'null');

        $rentals = $rentalsRepository->createQueryBuilder('rental');

        $pagination = $paginator->paginate(
            $rentals,
            $request->query->getInt('page', $page),
            20
        );

        return $this->render('admin/rentals/index.html.twig', [
            'rentals' => $pagination,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'app_rentals_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rental = new Rentals();
        $form = $this->createForm(RentalsType::class, $rental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rental->setIsActive(false);
            $rental->setIsOnPromotion(false);
            $entityManager->persist($rental);
            $entityManager->flush();

            $this->addFlash('success', 'Votre logement a été publié.');
            return $this->redirectToRoute('app_rentals_add_home_image', [
                'id' => $rental->getId(),
            ]);
        }

        return $this->render('admin/rentals/new.html.twig', [
            'rental' => $rental,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rentals_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rentals $rental, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RentalsType::class, $rental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_images_change_home_image', [
                'id' => $rental->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rentals/edit.html.twig', [
            'rental' => $rental,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rentals_delete')]
    public function delete($id, RentalsRepository $rentalsRepository, EntityManagerInterface $entityManager, ReservationsRentalsRepository $reservationsRentalsRepository, CommentsRepository $commentsRepository): Response
    {
        $rental = $rentalsRepository->find($id);

        if(count($rental->getReservations()) > 0 || count($rental->getComments()) > 0){
            $this->addFlash('danger', 'Le logement possède des réservations/commentaires, impossible de le supprimer.');
            return $this->redirectToRoute('app_rentals_edit', [
                'id' => $id,
            ]);
        }

        $entityManager->remove($rental);
        $entityManager->flush();

        return $this->redirectToRoute('app_rentals_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/addImage', name: 'app_rentals_add_image')]
    public function addImage($id, RentalsRepository $rentalsRepository, EntityManagerInterface $entityManager, ReservationsRentalsRepository $reservationsRentalsRepository, SluggerInterface $slugger, Request $request)
    {
        $rental = $rentalsRepository->find($id);

        if(count($rental->getImages()) >= 4){
            $this->addFlash('warning', 'Vous ne pouvez pas ajouter plus de 4 images.');
            return $this->redirectToRoute('app_rentals_edit', [
                'id' => $id,
            ]);
        }

        $image = new Images();

        $form = $this->createForm(ImageRentalsType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('image')->getData();

            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                try {
                    $uploadedFile->move(
                        $this->getParameter('uploads_directory'), $newFilename
                    );

                    $imagine = new Imagine();
                    $imagePath = $this->getParameter('uploads_directory') . '/' . $newFilename;

                    $imagine->open($imagePath)
                        ->thumbnail(
                            new Box(1200, 1200),
                            ManipulatorInterface::THUMBNAIL_OUTBOUND
                        )
                        ->save($imagePath);

                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l’envoi du fichier.');
                    return $this->render('admin/rentals/addImage.html.twig', [
                        'rental' => $rental,
                        'form' => $form->createView(),
                    ]);
                }

                $image->setRentals($rental);
                $image->setIsHomePage(false);
                $image->setSrc($newFilename);
                $image->setAlt($originalFilename);

                $entityManager->persist($image);
                $entityManager->flush();

                $this->addFlash('success', 'L\'image a été ajoutée avec succès.');
                return $this->redirectToRoute('app_rentals_edit', [
                    'id' => $id,
                ]);
            }

        }

        return $this->render('admin/rentals/addImage.html.twig', [
            'rental' => $rental,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/addHomeImage', name: 'app_rentals_add_home_image')]
    public function changeHomeImage(int $id,RentalsRepository $rentalsRepository, ImagesRepository $imagesRepository, EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger): Response {
        $rental = $rentalsRepository->find($id);

        if($rental->getHomePageImage()){
            $this->addFlash('danger', 'Vous ne pouvez pas ajouter une image d\'accueil à un logement qui en a déja une.');
            return $this->redirectToRoute('app_rentals_edit', [
                'id' => $id,
            ]);
        }

        $form = $this->createForm(ImageRentalsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('image')->getData();

            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                try {
                    $uploadedFile->move(
                        $this->getParameter('uploads_directory'), $newFilename
                    );

                    $imagine = new Imagine();
                    $imagePath = $this->getParameter('uploads_directory') . '/' . $newFilename;

                    $imagine->open($imagePath)
                        ->thumbnail(
                            new Box(1200, 1200),
                            ManipulatorInterface::THUMBNAIL_OUTBOUND
                        )
                        ->save($imagePath);

                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l’envoi du fichier.');
                    return $this->render('admin/rentals/addHomeImage.html.twig', [
                        'rental' => $rental,
                        'form' => $form->createView(),
                    ]);
                }

                $image = new Images();
                $image->setSrc($newFilename);
                $image->setAlt($originalFilename);
                $image->setIsHomePage(true);
                $image->setRentals($rental);
                $rental->setHomeImage($image);

                $entityManager->persist($image);

                $entityManager->flush();

                $this->addFlash('success', 'L\'image d\'accueil a bien été ajoutée.');
                return $this->redirectToRoute('app_rentals_edit', [
                    'id' => $rental->getId(),
                ]);
            }
        }

        return $this->render('admin/rentals/addHomeImage.html.twig', [
            'rental' => $rental,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/reservations', name: 'app_rentals_reservations')]
    public function reservations($id, RentalsRepository $rentalsRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $rental = $rentalsRepository->find($id);

        $reservationsMin = [];
        $reservations = $rental->getReservations();

        foreach($reservations as $reservation){
            $reservationsMin[] = [
                'title' => $reservation->getUser()->getFirstname() . ' ' . $reservation->getUser()->getLastname(),
                'start' => $reservation->getDateStart()->format('Y-m-d'),
                'end' => $reservation->getDateEnd()->modify('+1 day')->format('Y-m-d'),
                'id' => $reservation->getId(),
            ];
        }

        return $this->render('admin/reservations/reservations.html.twig', [
            'rental' => $rental,
            'reservationsMin' => $reservationsMin,
            'reservations' => $reservations,
        ]);
    }

    #[Route('/{id}/comments', name: 'app_rentals_comments')]
    public function comments($id, RentalsRepository $rentalsRepository, CommentsRepository $commentsRepository): Response
    {
        $rental = $rentalsRepository->find($id);
        $comments = $rental->getComments();

        return $this->render('admin/rentals/comments.html.twig', [
            'rental' => $rental,
            'comments' => $comments,
        ]);
    }
}
