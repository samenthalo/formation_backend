<?php

namespace App\Repository;

use App\Entity\SessionFormation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SessionFormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionFormation::class);
    }

    // Ajoute ici des méthodes personnalisées si nécessaire
    public function findAllSessions()
    {
        return $this->createQueryBuilder('s')
            ->getQuery()
            ->getResult();
    }
}
