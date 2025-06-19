<?php

namespace App\Controller\admin;

use App\Entity\Events;
use App\Entity\Images;
use App\Form\EventsType;
use App\Form\ImageRentalsType;
use App\Repository\EventsRepository;
use App\Repository\ImagesRepository;
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

#[Route('/admin/events')]
final class EventsController extends AbstractController
{
    #[Route(name: 'app_events_index', methods: ['GET'])]
    public function index(EventsRepository $eventsRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page',1);
        $sort = $request->query->getString('sort', 'null');
        $direction = $request->query->getString('direction', 'null');
        $search = $request->query->get('search', '');

        $queryBuilder = $eventsRepository->createQueryBuilder('event');

        if (!empty($search)) {
            $queryBuilder
                ->andWhere('event.title LIKE :search OR event.content LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', $page),
            20
        );

        return $this->render('admin/events/index.html.twig', [
            'events' => $pagination,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'app_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Events();
        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setCreatedAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été ajouté avec succès.');
            return $this->redirectToRoute('app_images_add_home_image_event', [
                'id' => $event->getId(),
            ]);
        }

        return $this->render('admin/events/new.html.twig', [
            'events' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_events_edit')]
    public function edit(Request $request, Events $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/events/edit.html.twig', [
            'events' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_events_delete')]
    public function delete($id, Request $request, EventsRepository $eventsRepository, Events $event, EntityManagerInterface $entityManager): Response
    {
        $event = $eventsRepository->find($id);

        if(count($event->getReservations()) > 0){
            $this->addFlash('danger','Vous ne pouvez pas supprimer un événement qui possède des réservations.');
            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        foreach($event->getImages() as $image){
            $entityManager->remove($image);
        }

        $entityManager->remove($event);
        $entityManager->flush();

        $this->addFlash('success', 'L\'événement a été supprimé.');
        return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/addHomeImage', name: 'app_images_add_home_image_event')]
    public function addHomeImage(int $id, EventsRepository $eventsRepository, ImagesRepository $imagesRepository, EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger): Response {
        $event = $eventsRepository->find($id);

        if($event->getHomePageImage()){
            $this->addFlash('danger', 'Vous ne pouvez pas ajouter une image d\'accueil à un événement qui en a déja une.');
            return $this->redirectToRoute('app_events_edit', [
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
                    return $this->render('admin/events/addHomeImage.html.twig', [
                        'event' => $event,
                        'form' => $form->createView(),
                    ]);
                }

                $image = new Images();
                $image->setSrc($newFilename);
                $image->setAlt($originalFilename);
                $image->setIsHomePage(true);
                $image->setEvents($event);
                $event->setHomeImage($image);

                $entityManager->persist($image);

                $entityManager->flush();

                $this->addFlash('success', 'L\'image d\'accueil a bien été ajoutée.');
                return $this->redirectToRoute('app_events_edit', [
                    'id' => $event->getId(),
                ]);
            }
        }

        return $this->render('admin/events/addHomeImage.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/addImage', name: 'app_event_add_image')]
    public function addImage($id, EventsRepository $eventsRepository, EntityManagerInterface $entityManager, SluggerInterface $slugger, Request $request)
    {
        $event = $eventsRepository->find($id);

        if(count($event->getImages()) >= 4){
            $this->addFlash('warning', 'Vous ne pouvez pas ajouter plus de 4 images.');
            return $this->redirectToRoute('app_events_edit', [
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
                    return $this->render('admin/events/addImage.html.twig', [
                        'event' => $event,
                        'form' => $form->createView(),
                    ]);
                }

                $image->setEvents($event);
                $image->setIsHomePage(false);
                $image->setSrc($newFilename);
                $image->setAlt($originalFilename);

                $entityManager->persist($image);
                $entityManager->flush();

                $this->addFlash('success', 'L\'image a été ajoutée avec succès.');
                return $this->redirectToRoute('app_events_edit', [
                    'id' => $id,
                ]);
            }

        }

        return $this->render('admin/events/addImage.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }
}
