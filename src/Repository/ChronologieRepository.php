<?php

namespace App\Repository;

use App\Entity\Chronologie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChronologieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chronologie::class);
    }

    // Ajoute ici des méthodes personnalisées si nécessaire
}
