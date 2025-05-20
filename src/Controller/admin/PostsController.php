<?php

namespace App\Controller\admin;

use App\Entity\Images;
use App\Entity\Posts;
use App\Form\ImageRentalsType;
use App\Form\PostsType;
use App\Repository\ImagesRepository;
use App\Repository\PostsRepository;
use App\Repository\ReservationsRentalsRepository;
use DateTimeZone;
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

#[Route('/admin/posts')]
final class PostsController extends AbstractController
{
    #[Route(name: 'app_posts_index', methods: ['GET'])]
    public function index(PostsRepository $postsRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page',1);
        $sort = $request->query->getString('sort', 'null');
        $direction = $request->query->getString('direction', 'null');

        $posts = $postsRepository->createQueryBuilder('post');

        $pagination = $paginator->paginate(
            $posts,
            $request->query->getInt('page', $page),
            20
        );

        return $this->render('admin/posts/index.html.twig', [
            'posts' => $pagination,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'app_posts_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Posts();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUserId($this->getUser());
            $post->setCreatedAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Votre poste a été publié.');
            return $this->redirectToRoute('app_images_add_home_image_post', [
                'id' => $post->getId(),
            ]);
        }

        return $this->render('admin/posts/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_posts_show', methods: ['GET'])]
    public function show(Posts $post): Response
    {
        return $this->render('admin/posts/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_posts_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Posts $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_posts_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/posts/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_posts_delete')]
    public function delete($id, Request $request, PostsRepository $postsRepository, EntityManagerInterface $entityManager): Response
    {
        $post = $postsRepository->find($id);

        foreach($post->getImages() as $image){
            $entityManager->remove($image);
        }

        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'Le post a été supprimé avec succès.');
        return $this->redirectToRoute('app_posts_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/addHomeImage', name: 'app_images_add_home_image_post')]
    public function addHomeImage(int $id, PostsRepository $postsRepository, ImagesRepository $imagesRepository, EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger): Response {
        $post = $postsRepository->find($id);

        if($post->getHomePageImage()){
            $this->addFlash('danger', 'Vous ne pouvez pas ajouter une image d\'accueil à un poste qui en a déja une.');
            return $this->redirectToRoute('app_posts_edit', [
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
                    return $this->render('admin/posts/addHomeImage.html.twig', [
                        'post' => $post,
                        'form' => $form->createView(),
                    ]);
                }

                $image = new Images();
                $image->setSrc($newFilename);
                $image->setAlt($originalFilename);
                $image->setIsHomePage(true);
                $image->setPosts($post);
                $post->setHomeImage($image);

                $entityManager->persist($image);

                $entityManager->flush();

                $this->addFlash('success', 'L\'image d\'accueil a bien été ajoutée.');
                return $this->redirectToRoute('app_posts_edit', [
                    'id' => $post->getId(),
                ]);
            }
        }

        return $this->render('admin/posts/addHomeImage.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/addImage', name: 'app_posts_add_image')]
    public function addImage($id, PostsRepository $postsRepository, EntityManagerInterface $entityManager, SluggerInterface $slugger, Request $request)
    {
        $post = $postsRepository->find($id);

        if(count($post->getImages()) >= 4){
            $this->addFlash('warning', 'Vous ne pouvez pas ajouter plus de 4 images.');
            return $this->redirectToRoute('app_posts_edit', [
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
                    return $this->render('admin/posts/addImage.html.twig', [
                        'posts' => $post,
                        'form' => $form->createView(),
                    ]);
                }

                $image->setPosts($post);
                $image->setIsHomePage(false);
                $image->setSrc($newFilename);
                $image->setAlt($originalFilename);

                $entityManager->persist($image);
                $entityManager->flush();

                $this->addFlash('success', 'L\'image a été ajoutée avec succès.');
                return $this->redirectToRoute('app_posts_edit', [
                    'id' => $id,
                ]);
            }

        }

        return $this->render('admin/posts/addImage.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
}
