<?php

namespace App\DataFixtures;

use App\Entity\Images;
use App\Entity\Events;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ImagesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupérer tous les événements existants
        $events = $manager->getRepository(Events::class)->findAll();

        if (empty($events)) {
            // Si aucun événement n'existe, vous pouvez en créer ici, ou simplement sortir.
            // Pour l'exemple, nous allons sortir.
            return;
        }

        foreach ($events as $event) {
            $numberOfImages = $faker->numberBetween(1, 3); // Nombre aléatoire d'images par événement

            for ($i = 0; $i < $numberOfImages; $i++) {
                $image = new Images();
                $image->setSrc($faker->imageUrl(640, 480, 'events', true));
                $image->setAlt($faker->sentence(3)); // Une courte phrase pour l'attribut alt
                $image->setEvents($event); // Lie l'image à l'événement courant

                $manager->persist($image);
            }
        }

        $manager->flush();
    }
}