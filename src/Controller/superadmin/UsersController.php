<?php

namespace App\Controller\superadmin;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
class UsersController extends AbstractController
{

    #[Route('/superadmin/upgrade/{id}', name: 'superadmin_upgrade_role')]
    public function upgradeUserRole($id, UsersRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('danger', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_users_index');
        }

        if (!$user->isActive()) {
            $this->addFlash('danger', 'L\'utilisateur est inactif.');
            return $this->redirectToRoute('app_users_index');
        }

        $roles = $user->getRoles();

        if (!in_array('ROLE_ADMIN', $roles)) {
            $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            $this->addFlash('success', 'L\'utilisateur a été promu en tant qu\'administrateur.');
        } elseif (!in_array('ROLE_SUPER_ADMIN', $roles) && in_array('ROLE_ADMIN', $roles)) {
            $currentUser = $this->getUser();
            $targetRoles = $user->getRoles();
            $user->setRoles(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
            $currentUser->setRoles($targetRoles);
            $entityManager->persist($currentUser);
            $this->addFlash('success', 'Le transfert de rôle a été éffectué.');
        } else {
            $this->addFlash('info', 'L\'utilisateur a déjà le rôle le plus élevé.');
            return $this->redirectToRoute('app_users_index');
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_users_index');
    }


    #[Route('/superadmin/downgrade/{id}', name: 'superadmin_downgrade_role')]
    public function downgradeUserRole($id, UsersRepository $userRepository, EntityManagerInterface $entityManager ): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('danger', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_users_index');
        }

        if (!$user->isActive()) {
            $this->addFlash('danger', 'L\'utilisateur est inactif.');
            return $this->redirectToRoute('app_users_index');
        }

        $roles = $user->getRoles();

        if (!in_array('ROLE_SUPER_ADMIN', $roles) && in_array('ROLE_ADMIN', $roles)) {
            $user->setRoles(['ROLE_USER']);
            $this->addFlash('success', 'L\'utilisateur a été rétrogradé en tant qu\'utilisateur.');
        } else {
            $this->addFlash('info', 'L\'utilisateur a déjà le rôle le plus bas.');
            return $this->redirectToRoute('app_users_index');
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_users_index');
    }
}