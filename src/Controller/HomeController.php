<?php

namespace App\Controller;

use App\Entity\Rentals;
use App\Repository\CommentsRepository;
use App\Repository\EventsRepository;
use App\Repository\PostsRepository;
use App\Repository\RentalsRepository;
use App\Services\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RentalsRepository $rentalsRepository, PostsRepository $postsRepository, EventsRepository $eventsRepository, MailerService $mailerService): Response
    {
        $rentals = $rentalsRepository->findAllRentalsWithDiscountAndActive();
        $posts = $postsRepository->findLatestActivePosts();
        $events = $eventsRepository->findLatestActiveEvents();
        $user = $this->getUser();
        $commentUser = [];

        if($user){
            if(!$user->isEmailAuthentificated()){
                $this->addFlash('warning', 'Votre email n\'est pas vérifié, veuillez la vérifié dans vos paramètres d\'utilisateur.');
            }

            foreach ($user->getComments() as $comment) {
                if(!$comment->getIsActive()){
                    $commentUser[] = $comment;
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'rentals' => $rentals,
            'posts' => $posts,
            'events' => $events,
            'InactivCommentUser' => $commentUser,
        ]);
    }

    #[Route('/policyAndPrivacy', name: 'app_policyandprivacy')]
    public function policyAndPrivacy(RentalsRepository $rentalsRepository, PostsRepository $postsRepository, EventsRepository $eventsRepository): Response
    {
        return $this->render('policyandprivacy/index.html.twig');
    }

    #[Route('/generalConditions', name: 'app_generalconditions')]
    public function generalConditions(RentalsRepository $rentalsRepository, PostsRepository $postsRepository, EventsRepository $eventsRepository): Response
    {
        return $this->render('generalConditions/index.html.twig');
    }

    #[Route('/legalNotices', name: 'app_legalnotices')]
    public function legalNotices(RentalsRepository $rentalsRepository, PostsRepository $postsRepository, EventsRepository $eventsRepository): Response
    {
        return $this->render('legalnotices/index.html.twig');
    }

    #[Route('/informations', name: 'app_informations')]
    public function informations(RentalsRepository $rentalsRepository, PostsRepository $postsRepository, EventsRepository $eventsRepository): Response
    {
        return $this->render('informations/index.html.twig');
    }
}
