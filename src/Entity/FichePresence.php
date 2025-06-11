<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\FichePresenceRepository")]
class FichePresence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "integer")]
    private int $id_session;

    #[ORM\Column(type: "string", length: 255)]
    private string $chemin_fichier;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_generation;

    // Getters and Setters

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

    public function getCheminFichier(): string
    {
        return $this->chemin_fichier;
    }

    public function setCheminFichier(string $chemin_fichier): self
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
