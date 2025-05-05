<?php

namespace App\Tests;

use App\Entity\Formation;
use App\Entity\Formateur;
use App\Entity\SessionCreneau;
use App\Entity\SessionFormation;
use App\Entity\Inscription;
use App\Entity\Stagiaire;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use PHPUnit\Framework\TestCase;

class SessionFormationTest extends KernelTestCase
{
    public function testCreateSessionFormationWithRelations(): void
    {
        // Lancement du kernel Symfony pour accéder au container
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // --- Création des entités de base ---
        $formation = new Formation();
        $formation->setTitre("Développement Web");
        $formation->setDescription("Formation complète en développement");
        $entityManager->persist($formation);

        $formateur = new Formateur();
        $formateur->setNom("Dupont");
        $formateur->setPrenom("Marie");
        $formateur->setEmail("marie.dupont@example.com");
        $entityManager->persist($formateur);

        // --- Création d'un stagiaire ---
        $stagiaire = new Stagiaire();
        $stagiaire->setNom("Durand");
        $stagiaire->setPrenom("Lucas");
        $stagiaire->setEmail("lucas.durand@example.com");
        $entityManager->persist($stagiaire);

        // --- Création d'une session ---
        $session = new SessionFormation();
        $session->setTitre("Session avril 2025")
            ->setDescription("Session intensive")
            ->setLieu("Paris")
            ->setNbHeures(40)
            ->setStatut("ouverte")
            ->setNbInscrits(2)
            ->setResponsableNom("Martin")
            ->setResponsablePrenom("Sophie")
            ->setResponsableTelephone("0600000000")
            ->setResponsableEmail("sophie.martin@example.com")
            ->setMode("présentiel")
            ->setLien(null)
            ->setFormation($formation)
            ->setFormateur($formateur);

        // --- Ajout d'un créneau ---
        $creneau = new SessionCreneau();
        $creneau->setJour("Lundi");
        $creneau->setHeureDebut("09:00");
        $creneau->setHeureFin("12:00");
        $creneau->setSessionFormation($session);
        $session->addCreneau($creneau);

        // --- Ajout d'une inscription ---
        $inscription = new Inscription();
        $inscription->setStagiaire($stagiaire);
        $inscription->setSessionFormation($session);
        $inscription->setStatut("inscrit"); // statut de l'inscription
        $session->addInscription($inscription);

        $entityManager->persist($creneau);
        $entityManager->persist($inscription);
        $entityManager->persist($session);

        // --- Flush en base (ou base test) ---
        $entityManager->flush();

        // --- Assertions ---
        $this->assertNotNull($session->getIdSession());
        $this->assertSame("Session avril 2025", $session->getTitre());
        $this->assertSame($formation, $session->getFormation());
        $this->assertSame($formateur, $session->getFormateur());
        $this->assertCount(1, $session->getCreneaux());
        $this->assertCount(1, $session->getInscriptions());
        $this->assertSame($stagiaire, $inscription->getStagiaire());  // Vérifie que l'inscription est bien liée au stagiaire
        $this->assertSame("inscrit", $inscription->getStatut());  // Vérifie que le statut de l'inscription est correct
    }
}
