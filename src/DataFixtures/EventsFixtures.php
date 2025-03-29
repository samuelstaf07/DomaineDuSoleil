<?php

namespace App\DataFixtures;

use App\Entity\Events;
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
            $event->setContent($faker->text(200));
            $event->setLocation($faker->words(5, true));
            $event->setPrice($faker->randomFloat(2, 10, 200));
            $event->setDate(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+1 year')));
            $event->setNbPlaces($faker->numberBetween(10, 500));
            $event->setIsActive($faker->boolean(90));
            $event->setAgeRequirement($faker->numberBetween(0, 18));
            $event->setCreatedAt(new DateTimeImmutable());

            $manager->persist($event);
            $this->addReference('event_' . $i, $event); // Ajout d'une référence
        }

        $manager->flush();
    }
}