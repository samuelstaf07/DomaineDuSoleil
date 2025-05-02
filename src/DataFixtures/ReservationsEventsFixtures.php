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

        // Récupérer tous les Bills existants
        $bills = $manager->getRepository(Bills::class)->findAll();
        $users = $manager->getRepository(Users::class)->findAll();
        $events = $manager->getRepository(Events::class)->findAll();

        if (empty($bills) || empty($users) || empty($events)) {
            throw new \Exception('Assurez-vous que des Bills, Users et Events existent avant de charger les ReservationsEvents.');
        }

        $numBills = count($bills);

        for ($i = 0; $i < 200; $i++) {
            $reservation = new ReservationsEvents();

            // Assigner une facture unique si possible
            if ($numBills > 0) {
                $billIndex = $i % $numBills; // Permet de réutiliser les factures si moins de factures que de réservations
                $reservation->setBillId($bills[$billIndex]);
            } else {
                throw new \Exception('Pas de Bills disponibles pour lier aux ReservationsEvents.');
            }

            $reservation->setUserId($faker->randomElement($users));
            $event = $faker->randomElement($events);
            $reservation->setEventId($event);
            $reservation->setDateReservation(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            $reservation->setDateStart(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            $reservation->setIsActive($faker->boolean(80));

            // Générer un nombre de réservations entre 1 et 5
            $nbPlaces = $faker->numberBetween(1, 5);

            $reservation->setNbPlaces($nbPlaces);

            $manager->persist($reservation);
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
