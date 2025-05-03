<?php

namespace App\DataFixtures;

use App\Entity\Posts;
use App\Entity\Images;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PostsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupérer tous les utilisateurs existants
        $users = $manager->getRepository(Users::class)->findAll();

        if (empty($users)) {
            // Si aucun utilisateur n'existe, on ne peut pas créer de posts.
            return;
        }

        for ($i = 0; $i < 20; $i++) {
            $post = new Posts();
            $post->setTitle($faker->sentence(5));
            $post->setContent($faker->paragraphs(7, true));
            $post->setUserId($faker->randomElement($users));
            $post->setCreatedAt(new DateTimeImmutable());
            $post->setIsActive($faker->boolean(80));

            $numberOfImages = $faker->numberBetween(1, 3);
            for ($j = 0; $j < $numberOfImages; $j++) {
                $image = new Images();
                if($j == 0){
                    $image->setIsHomePage(true);
                }else{
                    $image->setIsHomePage(false);
                }
                $image->setSrc('Chalet.jpg');
                $image->setAlt($faker->sentence(3));
                $image->setPosts($post);
                $post->addImage($image);

                $manager->persist($image);
            }

            $manager->persist($post);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
        ];
    }
}