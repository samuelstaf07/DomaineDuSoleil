<?php

namespace App\DataFixtures;

use App\Entity\Comments;
use App\Entity\Users;
use App\Entity\Rentals;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CommentsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $users = $manager->getRepository(Users::class)->findAll();
        $rentals = $manager->getRepository(Rentals::class)->findAll();

        foreach ($rentals as $rental) {
            $numUsersToComment = $faker->numberBetween(1, count($users));
            $usersToComment = $faker->randomElements($users, $numUsersToComment, false);

            foreach ($usersToComment as $user) {
                $comment = new Comments();
                $comment->setUser($user);
                $comment->setContent($faker->paragraph);
                $comment->setCreatedAt(new \DateTimeImmutable('now'));
                $comment->setRating($faker->numberBetween(1, 5));
                $comment->setIsActive($faker->boolean(95));
                $comment->setDisabledAt(null);
                $comment->setChangedAt(null);
                $comment->setRentals($rental);

                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            RentalsFixtures::class,
        ];
    }
}