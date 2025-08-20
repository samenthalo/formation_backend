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
    // Ajouter une méthode pour récupérer les sessions liées à une formation spécifique via id_formation
    public function findByFormationId(int $formationId)
    {
        return $this->createQueryBuilder('s')
            ->where('s.formation = :formation') // Utilise l'objet Formation lié
            ->setParameter('formation', $formationId) // Passer l'ID de la formation ici
            ->getQuery()
            ->getResult();
    }

    public function findFormateurBySessionId(int $sessionId)
{
    return $this->createQueryBuilder('s')
        ->join('s.formateur', 'f')
        ->andWhere('s.id = :sessionId')
        ->setParameter('sessionId', $sessionId)
        ->select('f')
        ->getQuery()
        ->getOneOrNullResult();
}


    
}
