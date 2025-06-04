<?php

// use "php bin/console app:promote-inactive-rentals" to execute this command

namespace App\Command;

use App\Entity\Rentals;
use App\Repository\RentalsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PromotionOldRentals extends Command
{
    private $entityManager;
    private RentalsRepository $rentalsRepository;

    public function __construct(EntityManagerInterface $entityManager, RentalsRepository $rentalsRepository)
    {
        $this->entityManager = $entityManager;
        $this->rentalsRepository = $rentalsRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:promote-inactive-rentals')
            ->setDescription('Promote rentals that have not had reservations in the last month and no upcoming reservations in the next two months');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rentals = $this->rentalsRepository->findAll();

        $promotedRentalsCount = 0;
        $unPromotedRentalsCount = 0;

        foreach ($rentals as $rental) {
            if($rental->needToBeOnPromotion() && !$rental->isOnPromotion()){
                $rental->setIsOnPromotion(true);
                $promotedRentalsCount++;
                $this->entityManager->persist($rental);
            }else{
                $rental->setIsOnPromotion(false);
                $unPromotedRentalsCount++;
                $this->entityManager->persist($rental);
            }
        }

        $this->entityManager->flush();

        $output->writeln($promotedRentalsCount . ' rentals have been promoted.');
        $output->writeln($unPromotedRentalsCount . ' rentals have been unpromoted.');

        return Command::SUCCESS;
    }
}
