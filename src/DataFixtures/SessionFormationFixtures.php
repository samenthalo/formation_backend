<?php

namespace App\DataFixtures;

use App\Entity\SessionFormation;
use App\Entity\Formation;
use App\Entity\Formateur;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Faker\Factory;

#[\Doctrine\Bundle\FixturesBundle\Fixture\Group(['sessions'])]
class SessionFormationFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $formations = $manager->getRepository(Formation::class)->findAll();
        $formateurs = $manager->getRepository(Formateur::class)->findAll();
        
        // Vérification que les formations et formateurs existent
        // avant de créer des sessions
        if (empty($formations) || empty($formateurs)) {
            throw new \Exception("Il faut des formations et formateurs dans la base avant de charger des sessions.");
        }

        for ($i = 0; $i < 1000; $i++) {
            $session = new SessionFormation();
            $session->setTitre($faker->word)
                ->setDescription($faker->sentence(5))
                ->setLieu($faker->city)
                ->setNbHeures(rand(10, 40))
                ->setStatut($faker->randomElement(['Planifiée', 'En cours', 'Terminée']))
                ->setNbInscrits(rand(0, 50))
                ->setResponsableNom($faker->lastName)
                ->setResponsablePrenom($faker->firstName)
                ->setResponsableTelephone($faker->phoneNumber)
                ->setResponsableEmail($faker->email)
                ->setMode($faker->randomElement(['Présentiel', 'Distanciel']))
                ->setLien($faker->url)
                ->setFormation($faker->randomElement($formations))
                ->setFormateur($faker->randomElement($formateurs));

            $manager->persist($session);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['sessions'];
    }
}
