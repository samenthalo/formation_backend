<?php
// src/Repository/EvaluationFormateurRepository.php
namespace App\Repository;

use App\Entity\EvaluationFormateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvaluationFormateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluationFormateur::class);
    }

    // Exemple : trouver toutes les évaluations d'un formateur donné
    public function findByFormateur(int $formateurId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.formateur = :formateurId')
            ->setParameter('formateurId', $formateurId)
            ->orderBy('e.dateAssignation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Exemple : trouver toutes les évaluations avec un score supérieur à une valeur donnée
    public function findWithScoreGreaterThan(float $score): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.score > :score')
            ->setParameter('score', $score)
            ->orderBy('e.score', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Tu peux ajouter d'autres méthodes personnalisées ici selon tes besoins
}
