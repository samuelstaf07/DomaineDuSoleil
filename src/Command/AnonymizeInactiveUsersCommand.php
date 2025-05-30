<?php

// use "php bin/console app:anonymize-inactive-users" to execute this command

namespace App\Command;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnonymizeInactiveUsersCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:anonymize-inactive-users')
            ->setDescription('Anonymize inactive users');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $oneMonthAgo = new \DateTime('-1 month');

        $inactiveUsers = $this->entityManager->getRepository(Users::class)->createQueryBuilder('u')
            ->where('u.is_active = :isActive')
            ->andWhere('u.last_log_at <= :oneMonthAgo')
            ->setParameter('isActive', 0)
            ->setParameter('oneMonthAgo', $oneMonthAgo)
            ->getQuery()
            ->getResult();

        foreach ($inactiveUsers as $user) {
            $user->setEmail('anonymized-' . $user->getId() . '@null.com');
            $user->setFirstname('Anonymized');
            $user->setLastname('User');
        }

        $this->entityManager->flush();

        $output->writeln(count($inactiveUsers) . ' users have been anonymized.');

        return Command::SUCCESS;
    }
}