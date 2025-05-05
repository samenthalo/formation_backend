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

    // Exemple de méthode pour récupérer tous les stagiaires
    public function findAllStagiaires()
    {
        return $this->findAll();
    }
}
