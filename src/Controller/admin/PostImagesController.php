<?php

namespace App\Controller\admin;

use App\Entity\PostImages;
use App\Form\PostImagesType;
use App\Repository\PostImagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/post/images')]
final class PostImagesController extends AbstractController
{
    #[Route(name: 'app_post_images_index', methods: ['GET'])]
    public function index(PostImagesRepository $postImagesRepository): Response
    {
        return $this->render('admin/post_images/index.html.twig', [
            'post_images' => $postImagesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_post_images_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $postImage = new PostImages();
        $form = $this->createForm(PostImagesType::class, $postImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($postImage);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_images_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/post_images/new.html.twig', [
            'post_image' => $postImage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_images_show', methods: ['GET'])]
    public function show(PostImages $postImage): Response
    {
        return $this->render('admin/post_images/show.html.twig', [
            'post_image' => $postImage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_images_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PostImages $postImage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostImagesType::class, $postImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_post_images_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/post_images/edit.html.twig', [
            'post_image' => $postImage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_images_delete', methods: ['POST'])]
    public function delete(Request $request, PostImages $postImage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$postImage->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($postImage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_images_index', [], Response::HTTP_SEE_OTHER);
    }
}
