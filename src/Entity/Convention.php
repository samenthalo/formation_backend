<?php

namespace App\Entity;

use App\Repository\ConventionRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Template;

#[ORM\Entity(repositoryClass: ConventionRepository::class)]
class Convention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $id_session;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $chemin_fichier = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $date_generation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdSession(): int
    {
        return $this->id_session;
    }

    public function setIdSession(int $id_session): self
    {
        $this->id_session = $id_session;
        return $this;
    }

    public function getCheminFichier(): ?string
    {
        return $this->chemin_fichier;
    }

    public function setCheminFichier(?string $chemin_fichier): self
    {
        $this->chemin_fichier = $chemin_fichier;
        return $this;
    }

    public function getDateGeneration(): \DateTimeInterface
    {
        return $this->date_generation;
    }

    public function setDateGeneration(\DateTimeInterface $date_generation): self
    {
        $this->date_generation = $date_generation;
        return $this;
    }
}
