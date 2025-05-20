<?php

namespace App\Controller\admin;

use App\Entity\Comments;
use App\Form\CommentsType;
use App\Repository\CommentsRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/comments')]
final class CommentsController extends AbstractController
{
    #[Route(name: 'app_comments_index', methods: ['GET'])]
    public function index(CommentsRepository $commentsRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page',1);
        $sort = $request->query->getString('sort', 'null');
        $direction = $request->query->getString('direction', 'null');

        $comments = $commentsRepository->createQueryBuilder('comment')
            ->leftJoin('comment.user', 'user')
            ->addSelect('user');

        $pagination = $paginator->paginate(
            $comments,
            $request->query->getInt('page', $page),
            20
        );

        return $this->render('admin/comments/index.html.twig', [
            'comments' => $pagination,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'app_comments_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comments();
        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_comments_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/comments/new.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_comments_show')]
    public function show(Comments $comment): Response
    {
        return $this->render('admin/comments/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_comments_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comments $comment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_comments_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/comments/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_comments_delete', methods: ['POST'])]
    public function delete(Request $request, Comments $comment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_comments_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/changeActive', name: 'app_comments_change_active')]
    public function changeActive($id, CommentsRepository $commentsRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $comment = $commentsRepository->find($id);
        $comment->setIsActive(!$comment->getIsActive());

        if($comment->getIsActive()){
            $comment->setDisabledAt(null);
        }else{
            $comment->setDisabledAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));
        }

        $entityManager->persist($comment);
        $entityManager->flush();

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_comments_index');
    }
}
