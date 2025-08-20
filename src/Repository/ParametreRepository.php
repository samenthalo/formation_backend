<?php
// src/Repository/ParametreRepository.php
namespace App\Repository;

use App\Entity\Parametre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ParametreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parametre::class);
    }


    // Méthode pour trouver un paramètre par son nom et retourner sa valeur
    public function findValeurParNom(string $nom): ?string
    {
        $param = $this->findOneBy(['nom' => $nom]);
        return $param ? $param->getValeur() : null;
    }
}
