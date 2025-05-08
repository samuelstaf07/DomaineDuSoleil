<?php

namespace App\DataFixtures;

use App\Entity\Events;
use App\Entity\Images;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use DateTimeImmutable;

class EventsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 20; $i++) {
            $event = new Events();
            $event->setContent($faker->paragraphs(5, true));
            $event->setTitle($faker->sentence(10));
            $event->setLocation($faker->words(5, true));
            $event->setPrice($faker->randomFloat(2, 10, 200));
            $event->setDate(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-3 days', '+1 week')));
            $event->setNbPlaces($faker->numberBetween(10, 50));
            $event->setIsActive($faker->boolean(90));
            $event->setAgeRequirement($faker->numberBetween(0, 18));
            $event->setCreatedAt(new DateTimeImmutable());

            $numberOfImages = $faker->numberBetween(1, 4);
            for ($j = 0; $j < $numberOfImages; $j++) {
                $image = new Images();

                if($j == 0){
                    $image->setIsHomePage(1);
                }else{
                    $image->setIsHomePage(0);
                }
                $image->setSrc('Chalet.jpg');
                $image->setAlt($faker->sentence(3));
                $image->setEvents($event);
                $event->addImage($image);

                $manager->persist($image);
            }

            $manager->persist($event);

            if (($i % 5) === 0) {
                $manager->flush();
                $manager->clear();
            }
        }

        $manager->flush();
        $manager->clear();
    }
}