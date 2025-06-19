<?php

namespace App\Controller\admin;

use App\Entity\Comments;
use App\Form\CommentsType;
use App\Repository\CommentsRepository;
use App\Services\MailerService;
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
        $search = $request->query->get('search', '');

        $queryBuilder = $commentsRepository->createQueryBuilder('comment')
            ->leftJoin('comment.user', 'user')
            ->addSelect('user')
            ->leftJoin('comment.rentals', 'rentals')
            ->addSelect('rentals');

        if (!empty($search)) {
            $queryBuilder
                ->andWhere('user.firstname LIKE :search OR user.firstname LIKE :search OR rentals.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', $page),
            20
        );

        return $this->render('admin/comments/index.html.twig', [
            'comments' => $pagination,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }#[Route('/{id}', name: 'app_comments_show')]
    public function show(Comments $comment): Response
    {
        return $this->render('admin/comments/show.html.twig', [
            'comment' => $comment,
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
    public function changeActive($id, CommentsRepository $commentsRepository, EntityManagerInterface $entityManager, Request $request, MailerService $mailerService): Response
    {
        $comment = $commentsRepository->find($id);
        $comment->setIsActive(!$comment->getIsActive());

        if($comment->getIsActive()){
            $comment->setDisabledAt(null);
        }else{
            $comment->setDisabledAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));
            $mailerService->sendDisabledComment(
                $comment->getUser()->getEmail(),
                $comment->getUser()->getFirstname(),
                $comment
            );
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
