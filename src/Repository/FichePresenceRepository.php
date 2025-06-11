<?php

namespace App\Repository;

use App\Entity\FichePresence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FichePresenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FichePresence::class);
    }

    // Ajoute ici des méthodes personnalisées si nécessaire
}
