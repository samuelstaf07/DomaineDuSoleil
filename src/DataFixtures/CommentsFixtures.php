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

        for ($i = 0; $i < 40; $i++) {
            $comment = new Comments();
            $comment->setUser($faker->randomElement($users));
            $comment->setContent($faker->paragraph);
            $comment->setCreatedAt(new \DateTimeImmutable('now'));
            $comment->setRating($faker->numberBetween(1, 5));
            $comment->setIsActive($faker->boolean(90));
            $comment->setRentals($faker->randomElement($rentals));

            $manager->persist($comment);
        }

        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
        ];
    }
}
