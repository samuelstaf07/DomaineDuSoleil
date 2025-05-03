<?php

namespace App\DataFixtures;

use App\Entity\Bills;
use App\Entity\Events;
use App\Entity\ReservationsEvents;
use App\Entity\Users;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReservationsEventsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_BE');
        $bills = $manager->getRepository(Bills::class)->findAll();
        $users = $manager->getRepository(Users::class)->findAll();
        $events = $manager->getRepository(Events::class)->findAll();

        for ($i = 0; $i < 30; $i++) {
            $reservationEvent = new ReservationsEvents();
            $reservationEvent->setBill($faker->randomElement($bills));
            $reservationEvent->setUser($faker->randomElement($users));
            $reservationEvent->setEvent($faker->randomElement($events));
            $reservationEvent->setDateReservation(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            $reservationEvent->setIsActive($faker->boolean(80));
            $reservationEvent->setNbPlaces($faker->numberBetween(1, 5));
            $reservationEvent->setTotalDeposit($faker->randomFloat(2, 10, 100));

            $manager->persist($reservationEvent);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            BillsFixtures::class,
            UsersFixtures::class,
            EventsFixtures::class,
        ];
    }
}