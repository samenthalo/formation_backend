<?php
// src/Repository/ReponseFormateurRepository.php
namespace App\Repository;

use App\Entity\ReponseFormateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReponseFormateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReponseFormateur::class);
    }

    // Exemple de méthode personnalisée pour récupérer les réponses d'un formateur donné
    public function findByFormateur(int $formateurId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.formateur = :formateurId')
            ->setParameter('formateurId', $formateurId)
            ->orderBy('r.dateReponse', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Tu peux ajouter d'autres méthodes personnalisées ici selon tes besoins
}
