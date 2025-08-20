<?php

namespace App\Repository;

use App\Entity\RappelEnvoye;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RappelEnvoyeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RappelEnvoye::class);
    }
    
    // Méthode pour trouver les rappels non effectués dans la chronologie
    public function findRappelsNonDansChronologie(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT r.*
            FROM rappel_envoye r
            LEFT JOIN chronologie c ON c.id_session = r.id_session AND c.type_evenement LIKE CONCAT(\'%\', r.type_rappel, \'%\')
            WHERE c.id IS NULL
        ';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }


}
