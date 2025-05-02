<?php

namespace App\Controller;

use App\Repository\PostsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostsController extends AbstractController
{
    #[Route('/posts', name: 'app_posts')]
    public function index(PostsRepository $postsRepository): Response
    {
        return $this->render('posts/index.html.twig', [
            'posts' => $postsRepository->findActivePosts(),
        ]);
    }

    #[Route('/post/{id}', name: 'app_post')]
    public function post($id, PostsRepository $postsRepository): Response
    {
        $post = $postsRepository->find($id);

        if($post->isActive()){
            return $this->render('post/index.html.twig', [
                'post' => $post,
            ]);
        }else{
            return $this->redirectToRoute('app_home');
        }
    }
}
