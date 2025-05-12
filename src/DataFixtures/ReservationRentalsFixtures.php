<?php

namespace App\DataFixtures;

use App\Entity\Bills;
use App\Entity\Rentals;
use App\Entity\ReservationsRentals;
use App\Entity\Users;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReservationRentalsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_BE');
        $bills = $manager->getRepository(Bills::class)->findAll();
        $users = $manager->getRepository(Users::class)->findAll();
        $rentals = $manager->getRepository(Rentals::class)->findAll();

        for ($i = 0; $i < 30; $i++) {
            $reservationRental = new ReservationsRentals();
            $reservationRental->setBill($faker->randomElement($bills));
            $reservationRental->setUser($faker->randomElement($users));
            $reservationRental->setRentals($faker->randomElement($rentals));
            $reservationRental->setStatusBaseDeposit($faker->randomElement([0, 1, 2, 3, 4]));
            $reservationRental->setDateReservation(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            $startDate = DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+1 year'));
            $endDate = $startDate->modify('+' . $faker->numberBetween(1, 14) . ' days');
            $reservationRental->setDateStart($startDate);
            $reservationRental->setDateEnd($endDate);
            $reservationRental->setStatusReservation($faker->boolean(90));
            $reservationRental->setTotalDepositReturned($faker->randomFloat(0, 10, 250));

            $nbDay = $reservationRental->getDateStart()->diff($reservationRental->getDateEnd())->days + 1;

            $reservationRental->setTotalPrice(floor($nbDay * $reservationRental->getRentals()->getPricePerDay() * 100) / 100);

            $manager->persist($reservationRental);
        }

        $manager->flush();
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