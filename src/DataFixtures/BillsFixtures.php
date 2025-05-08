<?php

namespace App\DataFixtures;

use App\Entity\Bills;
use App\Entity\users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BillsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $userRepository = $manager->getRepository(users::class);
        $users = $userRepository->findAll();

        if (empty($users)) {
            return;
        }

        foreach ($users as $user) {
            for ($i = 0; $i < 3; $i++) {
                $bill = new Bills();
                $bill->setDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
                $bill->setTotalPrice($faker->randomFloat(2, 50, 1000));
                $bill->setStatus($faker->numberBetween(0, 4));
                $bill->setUser($user);
                $manager->persist($bill);
            }
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
