<?php

namespace App\DataFixtures;

use App\Entity\Formateur;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;


class FormateurFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 200; $i++) {
            $formateur = new Formateur();
            $formateur->setNom($faker->lastName)
                      ->setPrenom($faker->firstName)
                      ->setEmail($faker->email)
                      ->setTelephone($faker->phoneNumber)
                      ->setSpecialites($faker->sentence(5))
                      ->setBio($faker->paragraph(2))
                      ->setEstActif($faker->boolean)
                      ->setCreeLe($faker->dateTimeThisDecade)
                      ->setMisAJour($faker->dateTimeThisYear)
                      ->setLinkedin($faker->url)
                      ->setCvPath($faker->url);

            $manager->persist($formateur);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['formateurs'];
    }
}
