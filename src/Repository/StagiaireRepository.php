<?php
// src/Repository/StagiaireRepository.php

namespace App\Repository;

use App\Entity\Stagiaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StagiaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stagiaire::class);
    }

    /**
     * Récupère tous les stagiaires.
     *
     * @return array Un tableau contenant tous les stagiaires.
     */
    public function findAllStagiaires()
    {
        return $this->findAll();
    }

    /**
     * Trouve les stagiaires inscrits à une session de formation spécifique.
     *
     * @param int $idSession L'ID de la session de formation.
     * @return array Un tableau de stagiaires inscrits à la session spécifiée.
     */
    public function findBySessionId(int $idSession): array
    {
        // Création d'une requête pour obtenir les stagiaires inscrits à une session de formation donnée
        return $this->createQueryBuilder('s')
            ->join('s.inscriptions', 'i')
            ->where('i.sessionFormation = :idSession')
            ->setParameter('idSession', $idSession)
            ->getQuery()
            ->getResult();
    }
}
