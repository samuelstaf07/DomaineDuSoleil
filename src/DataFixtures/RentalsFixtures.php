<?php

namespace App\DataFixtures;

use App\Entity\Rentals;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RentalsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $rental = new Rentals();
            $rental->setTitle($faker->words(3, true));
            $rental->setNbDoubleBed($faker->numberBetween(0, 3));
            $rental->setNbSimpleBed($faker->numberBetween(0, 5));
            $rental->setHasShower($faker->boolean);
            $rental->setHasToilet($faker->boolean);
            $rental->setHasKitchen($faker->boolean);
            $rental->setHasFridge($faker->boolean);
            $rental->setHasHeating($faker->boolean);
            $rental->setPetsAccepted($faker->boolean);
            $rental->setPricePerDay($faker->randomFloat(2, 50, 200));
            $rental->setIsOnPromotion($faker->boolean(90));
            $rental->setIsActive($faker->boolean(90));

            $manager->persist($rental);
        }

        $manager->flush();
    }
}
