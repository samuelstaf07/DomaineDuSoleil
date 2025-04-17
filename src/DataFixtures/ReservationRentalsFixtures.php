<?php

namespace App\DataFixtures;

use App\Entity\ReservationsRentals;
use App\Entity\Bills;
use App\Entity\Users;
use App\Entity\Rentals;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ReservationRentalsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupérer les références des Bills, Users et Rentals créés dans d'autres fixtures
        $bills = $manager->getRepository(Bills::class)->findAll();
        $users = $manager->getRepository(Users::class)->findAll();
        $rentals = $manager->getRepository(Rentals::class)->findAll();

        $usedBillIds = []; // Tableau pour suivre les IDs de bills utilisés

        for ($i = 0; $i < 300; $i++) {
            $reservation = new ReservationsRentals();

            // Assurez-vous qu'il y a des bills, user et rentals avant de les utiliser
            if (!empty($bills) && !empty($users) && !empty($rentals)) {
                $bill = null;
                // Trouve un bill qui n'a pas été utilisé
                foreach ($bills as $b) {
                    if (!in_array($b->getId(), $usedBillIds)) {
                        $bill = $b;
                        break;
                    }
                }
                if ($bill) {
                    $reservation->setBillId($bill);
                    $usedBillIds[] = $bill->getId(); // Ajoute l'ID du bill utilisé au tableau
                    $reservation->setUser($faker->randomElement($users));
                    $reservation->setRentals($faker->randomElement($rentals));
                } else {
                    // Si tous les bills ont été utilisés, on sort de la boucle
                    break;
                }
            }

            $reservation->setHasCleaningDeposit($faker->boolean());
            $reservation->setTotalDepositReturned($faker->randomFloat(2, 0, 500));
            $reservation->setStatusBaseDeposit($faker->numberBetween(0, 2));
            $reservation->setDateReservation(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            $startDate = DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+1 year'));
            $endDate = $startDate->modify('+' . $faker->numberBetween(1, 14) . ' days');
            $reservation->setDateStart($startDate);
            $reservation->setDateEnd($endDate);
            $reservation->setStatusReservation($faker->numberBetween(0, 3));

            $manager->persist($reservation);
        }


        if (($i % 5) === 0) {
            $manager->flush();
            $manager->clear();
        }

        $manager->flush();
        $manager->clear();
    }

    public function getDependencies(): array
    {
        return [
            BillsFixtures::class,
            UsersFixtures::class,
            RentalsFixtures::class,
        ];
    }
}