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

        $totalReservations = $faker->numberBetween(200, 250);

        for ($i = 0; $i < $totalReservations; $i++) {
            $reservationRental = new ReservationsRentals();
            $reservationRental->setBill($faker->randomElement($bills));

            $user = $faker->randomElement($users);
            $rental = $faker->randomElement($rentals);
            $reservationRental->setUser($user);
            $reservationRental->setRentals($rental);

            $reservationRental->setDateReservation(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            $startDate = DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+1 year'));
            $endDate = $startDate->modify('+' . $faker->numberBetween(1, 14) . ' days');
            $reservationRental->setDateStart($startDate);
            $reservationRental->setDateEnd($endDate);

            $nbDay = $reservationRental->getDateStart()->diff($reservationRental->getDateEnd())->days + 1;
            $pricePerDay = $rental->getPricePerDay();
            $totalPrice = round($nbDay * $pricePerDay, 2);
            $reservationRental->setTotalPrice($totalPrice);

            $reservationRental->setStatusReservation($faker->boolean(90));

            $rand = $faker->numberBetween(1, 100);

            if ($rand <= 80) {
                $statusBaseDeposit = 0;
            } elseif ($rand <= 85) {
                $statusBaseDeposit = 1;
            } elseif ($rand <= 90) {
                $statusBaseDeposit = 2;
            } elseif ($rand <= 95) {
                $statusBaseDeposit = 3;
            } else {
                $statusBaseDeposit = 4;
            }

            $reservationRental->setStatusBaseDeposit($statusBaseDeposit);

            switch ($statusBaseDeposit) {
                case 0:
                    $reservationRental->setIsRefund(false);
                    $reservationRental->setTotalDepositReturned(0);
                    break;

                case 1:
                    $reservationRental->setIsRefund(true);
                    $reservationRental->setTotalDepositReturned(
                        round($faker->randomFloat(2, 1, $totalPrice), 2)
                    );
                    break;

                case 2:
                    $reservationRental->setIsRefund(true);
                    $reservationRental->setTotalDepositReturned(round(2 * $pricePerDay, 2));
                    break;

                case 3:
                    $reservationRental->setIsRefund(true);
                    $reservationRental->setTotalDepositReturned(0);
                    break;

                case 4:
                    $reservationRental->setIsRefund(true);
                    $reservationRental->setTotalDepositReturned($totalPrice);
                    break;
            }

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
