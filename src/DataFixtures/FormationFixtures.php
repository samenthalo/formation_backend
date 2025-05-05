<?php

namespace App\DataFixtures;

use App\Entity\Formation;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class FormationFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 200; $i++) {
            $formation = new Formation();
            $formation->setTitre($faker->words(3, true)) // Un titre généré avec 3 mots
                ->setDescription($faker->sentence(10)) // Une description d'une dizaine de mots
                ->setPrixUnitaireHt($faker->randomFloat(2, 50, 2000)) // Prix entre 50 et 2000
                ->setNbParticipantsMax($faker->numberBetween(10, 100)) // Nombre de participants max
                ->setEstActive($faker->boolean) // Actif ou non
                ->setTypeFormation($faker->randomElement(['Présentiel', 'Distanciel'])) // Type de formation
                ->setDureeHeures($faker->numberBetween(5, 40)) // Durée en heures
                ->setCategorie($faker->word) // Catégorie de la formation
                ->setProgramme($faker->paragraph) // Programme de la formation
                ->setMultiJour($faker->boolean) // Est-ce une formation multi-jours ?
                ->setCible($faker->sentence(3)) // Cible de la formation
                ->setMoyensPedagogiques($faker->sentence(6)) // Moyens pédagogiques
                ->setPreRequis($faker->sentence(4)) // Prérequis de la formation
                ->setDelaiAcces($faker->sentence(2)) // Délai d'accès à la formation
                ->setSupportsPedagogiques($faker->sentence(5)) // Supports pédagogiques
                ->setMethodesEvaluation($faker->sentence(5)) // Méthodes d'évaluation
                ->setAccessible($faker->boolean) // Formation accessible ?
                ->setTauxTva($faker->randomFloat(2, 5, 20)) // Taux de TVA
                ->setWelcomeBooklet($faker->sentence(8)); // Welcome booklet

            $manager->persist($formation);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['formations'];
    }
}
