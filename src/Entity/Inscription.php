<?php
// src/Entity/Inscription.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\InscriptionRepository;
use App\Entity\Stagiaire;
use App\Entity\SessionFormation;


#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id_inscription;

    #[ORM\ManyToOne(targetEntity: Stagiaire::class)]
    #[ORM\JoinColumn(name: "id_stagiaire", referencedColumnName: "id_stagiaire", nullable: false)]
    private $stagiaire;

    #[ORM\ManyToOne(targetEntity: SessionFormation::class)]
    #[ORM\JoinColumn(name: "id_session", referencedColumnName: "id_session", nullable: false, onDelete: "CASCADE")]
    private $sessionFormation;
    

    #[ORM\Column(type: "string", length: 255)]
    private $statut;

    public function getIdInscription(): ?int
    {
        return $this->id_inscription;
    }

    public function getStagiaire(): ?Stagiaire
    {
        return $this->stagiaire;
    }

    public function setStagiaire(?Stagiaire $stagiaire): self
    {
        $this->stagiaire = $stagiaire;

        return $this;
    }

    public function getSessionFormation(): ?SessionFormation
    {
        return $this->sessionFormation;
    }

    public function setSessionFormation(?SessionFormation $session): self
    {
        $this->sessionFormation = $session;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }
}
