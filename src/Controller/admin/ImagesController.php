<?php

namespace App\Controller\admin;

use App\Form\ImageRentalsType;
use App\Repository\ImagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/images')]
final class ImagesController extends AbstractController
{
    #[Route('/deleteImageRental/{id}', name: 'app_images_delete_rentals')]
    public function index($id, ImagesRepository $imagesRepository, EntityManagerInterface $entityManager): Response
    {
        $image = $imagesRepository->find($id);

        if($image->isHomePage()){
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer une image d\'accueil.');
            return $this->redirectToRoute('app_home');
        }

        $rental = $image->getRentals();

        $entityManager->remove($image);
        $entityManager->flush();

        $this->addFlash('success', 'L\'image a été supprimée.');
        return $this->redirectToRoute('app_rentals_edit', [
            'id' => $rental->getId(),
        ]);
    }

    #[Route('/deleteImagePost/{id}', name: 'app_images_delete_posts')]
    public function deleteImagePost($id, ImagesRepository $imagesRepository, EntityManagerInterface $entityManager): Response
    {
        $image = $imagesRepository->find($id);

        if($image->isHomePage()){
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer une image d\'accueil.');
            return $this->redirectToRoute('app_home');
        }

        $post = $image->getPosts();

        $entityManager->remove($image);
        $entityManager->flush();

        $this->addFlash('success', 'L\'image a été supprimée.');
        return $this->redirectToRoute('app_posts_edit', [
            'id' => $post->getId(),
        ]);
    }
    #[Route('/deleteImageEvent/{id}', name: 'app_images_delete_event')]
    public function deleteImageEvent($id, ImagesRepository $imagesRepository, EntityManagerInterface $entityManager): Response
    {
        $image = $imagesRepository->find($id);

        if($image->isHomePage()){
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer une image d\'accueil.');
            return $this->redirectToRoute('app_home');
        }

        $events = $image->getEvents();

        $entityManager->remove($image);
        $entityManager->flush();

        $this->addFlash('success', 'L\'image a été supprimée.');
        return $this->redirectToRoute('app_events_edit', [
            'id' => $events->getId(),
        ]);
    }

    #[Route('/changeHomePage/{id}', name: 'app_images_change_home_image_rental')]
    public function changeHomeImage(int $id, ImagesRepository $imagesRepository, EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger): Response {
        $image = $imagesRepository->find($id);
        $rental = $image->getRentals();

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
                    return $this->render('admin/rentals/changeHomeImage.html.twig', [
                        'rental' => $rental,
                        'form' => $form->createView(),
                    ]);
                }

                $image->setSrc($newFilename);
                $image->setAlt($originalFilename);

                $rental->setHomeImage($image);

                $entityManager->flush();

                $this->addFlash('success', 'L\'image d\'accueil a bien été changée.');
                return $this->redirectToRoute('app_rentals_edit', [
                    'id' => $rental->getId(),
                ]);
            }
        }

        return $this->render('admin/rentals/changeHomeImage.html.twig', [
            'rental' => $rental,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/changeHomePagePost/{id}', name: 'app_images_change_home_image_post')]
    public function changeHomeImagePost(int $id, ImagesRepository $imagesRepository, EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger): Response {
        $image = $imagesRepository->find($id);
        $post = $image->getPosts();

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
                    return $this->render('admin/post/changeHomeImage.html.twig', [
                        'post' => $post,
                        'form' => $form->createView(),
                    ]);
                }

                $image->setSrc($newFilename);
                $image->setAlt($originalFilename);

                $post->setHomeImage($image);

                $entityManager->flush();

                $this->addFlash('success', 'L\'image d\'accueil a bien été changée.');
                return $this->redirectToRoute('app_posts_edit', [
                    'id' => $post->getId(),
                ]);
            }
        }

        return $this->render('admin/posts/changeHomeImage.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/changeHomePageEvent/{id}', name: 'app_images_change_home_image_event')]
    public function changeHomeImageEvent(int $id, ImagesRepository $imagesRepository, EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger): Response {
        $image = $imagesRepository->find($id);
        $event = $image->getEvents();

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
                    return $this->render('admin/events/changeHomeImage.html.twig', [
                        'event' => $event,
                        'form' => $form->createView(),
                    ]);
                }

                $image->setSrc($newFilename);
                $image->setAlt($originalFilename);

                $event->setHomeImage($image);

                $entityManager->flush();

                $this->addFlash('success', 'L\'image d\'accueil a bien été changée.');
                return $this->redirectToRoute('app_events_edit', [
                    'id' => $event->getId(),
                ]);
            }
        }

        return $this->render('admin/events/changeHomeImage.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

}