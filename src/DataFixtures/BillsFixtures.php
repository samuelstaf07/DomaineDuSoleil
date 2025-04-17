<?php

namespace App\DataFixtures;

use App\Entity\Bills;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use DateTimeImmutable;

class BillsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 300; $i++) {
            $bill = new Bills();
            $bill->setContent($faker->paragraph());
            $bill->setDate(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            $bill->setTotalPrice($faker->randomFloat(2, 10, 1000));
            $bill->setStatus($faker->numberBetween(0, 2)); // Ajustez les valeurs selon votre besoin

            $manager->persist($bill);
        }

        $manager->flush();
    }
}