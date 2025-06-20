<?php

namespace App\Controller\admin;

use App\Entity\Bills;
use App\Form\BillsType;
use App\Repository\BillsRepository;
use App\Services\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/bills')]
final class BillsController extends AbstractController
{
    #[Route(name: 'app_bills_index', methods: ['GET'])]
    public function index(BillsRepository $billsRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page',1);
        $sort = $request->query->getString('sort', 'null');
        $direction = $request->query->getString('direction', 'null');
        $search = $request->query->get('search', '');

        $queryBuilder = $billsRepository->createQueryBuilder('bill')
                                ->leftJoin('bill.user', 'user')
                                ->addSelect('user');

        if (!empty($search)) {
            $queryBuilder
                ->andWhere('user.lastname LIKE :search OR user.firstname LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', $page),
            20
        );

        return $this->render('admin/bills/index.html.twig', [
            'bills' => $pagination,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/{id}/changeActive', name: 'app_bills_change_active')]
    public function delete($id, BillsRepository $billsRepository, Request $request, EntityManagerInterface $entityManager, MailerService $mailerService): Response
    {
        $bill = $billsRepository->find($id);
        $bill->setStatus(!$bill->getStatus());

        if(!$bill->getStatus()){
            $mailerService->sendDisabledBill(
                $bill->getUser()->getEmail(),
                $bill->getUser()->getFirstname(),
                $bill
            );
        }

        $entityManager->persist($bill);
        $entityManager->flush();

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_comments_index');
    }
}
