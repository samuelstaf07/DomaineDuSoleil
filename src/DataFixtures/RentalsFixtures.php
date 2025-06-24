<?php

namespace App\DataFixtures;

use App\Entity\Images;
use App\Entity\Rentals;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RentalsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 100; $i++) {
            $rental = new Rentals();
            $rental->setTitle($faker->words(3, true));
            $rental->setNbDoubleBed($faker->numberBetween(0, 3));
            $rental->setNbSimpleBed($faker->numberBetween(0, 5));
            $rental->setContent($faker->text(400));
            $rental->setHasShower($faker->boolean);
            $rental->setHasToilet($faker->boolean);
            $rental->setHasKitchen($faker->boolean);
            $rental->setHasFridge($faker->boolean);
            $rental->setHasHeating($faker->boolean);
            $rental->setPetsAccepted($faker->boolean);
            $rental->setPricePerDay($faker->randomFloat(2, 50, 200));
            $rental->setIsOnPromotion($faker->boolean(90));
            $rental->setIsActive($faker->boolean(90));

            $numberOfImages = $faker->numberBetween(1, 4);
            for ($j = 0; $j < $numberOfImages; $j++) {
                $image = new Images();
                if($j == 0){
                    $image->setIsHomePage(true);
                    $image->setSrc('rental' . ($i % 10) . '.jpg');
                }else{
                    $image->setIsHomePage(false);
                    $image->setSrc('Chalet.jpg');
                }
                $image->setAlt($faker->sentence(3));
                $image->setRentals($rental);
                $rental->addImage($image);

                $manager->persist($image);
            }
            $manager->persist($rental);
        }

        if (($i % 5) === 0) {
            $manager->flush();
            $manager->clear();
        }

        $manager->flush();
        $manager->clear();
    }
}
