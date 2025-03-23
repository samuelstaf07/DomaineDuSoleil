<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 20; $i++) {
            $user = new Users();
            $user->setEmail($faker->unique()->safeEmail);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password')); // Hash the password
            $user->setRoles(['ROLE_USER']);
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setNbPoints($faker->numberBetween(0, 100));
            $user->setAccountNumber($faker->unique()->bankAccountNumber);
            $user->setCreatedAt(new \DateTimeImmutable('now'));
            $user->setUpdatedAt(new \DateTimeImmutable('now'));
            $user->setLastLogAt(new \DateTimeImmutable('now'));
            $user->setIsActive($faker->boolean);
            $user->setIsEmailAuthentificated($faker->boolean);

            $manager->persist($user);
        }

        $manager->flush();
    }
}