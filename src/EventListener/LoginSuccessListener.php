<?php

namespace App\EventListener;

use DateTimeZone;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginSuccessListener
{
    public function __construct(private EntityManagerInterface $em) {}

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $user->setLastLogAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')));

        $this->em->persist($user);
        $this->em->flush();
    }
}