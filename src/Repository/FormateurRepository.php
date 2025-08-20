<?php

namespace App\Repository;

use App\Entity\Formateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FormateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formateur::class);
    }

    // Méthode pour trouver un formateur par son email
    public function findOneByEmail(string $email): ?Formateur
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

public function findBySessionId(int $idSession): array
{
    return $this->createQueryBuilder('f')
        ->join('f.sessions', 's')
        ->andWhere('s.id_session = :idSession')
        ->setParameter('idSession', $idSession)
        ->getQuery()
        ->getResult();
}



}
