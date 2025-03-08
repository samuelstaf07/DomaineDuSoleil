<?php

namespace App\Controller\admin;

use App\Entity\Bills;
use App\Form\BillsType;
use App\Repository\BillsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/bills')]
final class BillsController extends AbstractController
{
    #[Route(name: 'app_bills_index', methods: ['GET'])]
    public function index(BillsRepository $billsRepository): Response
    {
        return $this->render('admin/bills/index.html.twig', [
            'bills' => $billsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_bills_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $bill = new Bills();
        $form = $this->createForm(BillsType::class, $bill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($bill);
            $entityManager->flush();

            return $this->redirectToRoute('app_bills_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/bills/new.html.twig', [
            'bill' => $bill,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bills_show', methods: ['GET'])]
    public function show(Bills $bill): Response
    {
        return $this->render('admin/bills/show.html.twig', [
            'bill' => $bill,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bills_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Bills $bill, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BillsType::class, $bill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_bills_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/bills/edit.html.twig', [
            'bill' => $bill,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bills_delete', methods: ['POST'])]
    public function delete(Request $request, Bills $bill, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bill->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($bill);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_bills_index', [], Response::HTTP_SEE_OTHER);
    }
}
