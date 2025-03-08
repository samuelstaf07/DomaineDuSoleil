<?php

namespace App\Controller\admin;

use App\Entity\RentalImages;
use App\Form\RentalImagesType;
use App\Repository\RentalImagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/rental/images')]
final class RentalImagesController extends AbstractController
{
    #[Route(name: 'app_rental_images_index', methods: ['GET'])]
    public function index(RentalImagesRepository $rentalImagesRepository): Response
    {
        return $this->render('admin/rental_images/index.html.twig', [
            'rental_images' => $rentalImagesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_rental_images_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rentalImage = new RentalImages();
        $form = $this->createForm(RentalImagesType::class, $rentalImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rentalImage);
            $entityManager->flush();

            return $this->redirectToRoute('app_rental_images_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rental_images/new.html.twig', [
            'rental_image' => $rentalImage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rental_images_show', methods: ['GET'])]
    public function show(RentalImages $rentalImage): Response
    {
        return $this->render('admin/rental_images/show.html.twig', [
            'rental_image' => $rentalImage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rental_images_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RentalImages $rentalImage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RentalImagesType::class, $rentalImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_rental_images_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rental_images/edit.html.twig', [
            'rental_image' => $rentalImage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rental_images_delete', methods: ['POST'])]
    public function delete(Request $request, RentalImages $rentalImage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rentalImage->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($rentalImage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rental_images_index', [], Response::HTTP_SEE_OTHER);
    }
}
