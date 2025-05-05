<?php
// src/DataFixtures/StagiaireFixtures.php

namespace App\DataFixtures;

use App\Entity\Stagiaire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class StagiaireFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Générer 3000 stagiaires
        for ($i = 0; $i < 3000; $i++) {
            $stagiaire = new Stagiaire();
            $stagiaire->setNomStagiaire($faker->lastName)
                      ->setPrenomStagiaire($faker->firstName)
                      ->setTelephoneStagiaire($faker->phoneNumber)
                      ->setEmailStagiaire($faker->email)
                      ->setEntrepriseStagiaire($faker->company)
                      ->setFonctionStagiaire($faker->jobTitle);

            $manager->persist($stagiaire);
        }

        $manager->flush();
    }
}
