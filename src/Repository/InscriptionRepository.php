<?php

// src/Repository/InscriptionRepository.php
namespace App\Repository;

use App\Entity\Inscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }

    public function findAllStagiaires()
    {
        return $this->findAll();
    }
        /**
     * Find inscriptions by session ID.
     *
     * @param int $id_session
     * @return Inscription[]
     */
    public function findBySession(int $id_session): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.sessionFormation = :session')
            ->setParameter('session', $id_session)
            ->getQuery()
            ->getResult();
    }
}

