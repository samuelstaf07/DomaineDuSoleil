<?php

namespace App\DataFixtures;

use App\Entity\Images;
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
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 20; $i++) {
            $user = new Users();
            $user->setEmail($faker->email);
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $createdAt = new \DateTimeImmutable('now');
            $user->setCreatedAt($createdAt);
            $user->setUpdatedAt($createdAt);
            $user->setLastLogAt($createdAt);
            $user->setIsActive($faker->boolean);
            $user->setIsEmailAuthentificated($faker->boolean);
            $user->setRoles(['ROLE_USER']);
            $user->setBirthDate(new \DateTimeImmutable('now'));

            // Hash the password
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'password' // Default password for all users
            );
            $user->setPassword($hashedPassword);

            // Create and associate an image
            $image = new Images();
            $image->setAlt('blabla');
            $image->setSrc("null"); // Example image path
            $manager->persist($image);
            $image->setIsHomePage(1);

            $user->setImage($image);

            $manager->persist($user);
        }

        $manager->flush();
    }
}