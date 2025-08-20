<?php

namespace App\Entity;

use App\Entity\SessionFormation;
use App\Entity\Stagiaire;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "attestations")]
class Attestation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: SessionFormation::class)]
    #[ORM\JoinColumn(name: "id_session", referencedColumnName: "id_session", nullable: false)]
    private $session;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $cheminFichier;

    #[ORM\Column(type: "datetime")]
    private $dateGeneration;

    #[ORM\ManyToOne(targetEntity: Stagiaire::class)]
    #[ORM\JoinColumn(name: "id_stagiaire", referencedColumnName: "id_stagiaire", nullable: false)]
    private $stagiaire;

    // Getters & setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?SessionFormation
    {
        return $this->session;
    }

    public function setSession(SessionFormation $session): self
    {
        $this->session = $session;
        return $this;
    }

    public function getCheminFichier(): ?string
    {
        return $this->cheminFichier;
    }

    public function setCheminFichier(?string $cheminFichier): self
    {
        $this->cheminFichier = $cheminFichier;
        return $this;
    }

    public function getDateGeneration(): ?\DateTimeInterface
    {
        return $this->dateGeneration;
    }

    public function setDateGeneration(\DateTimeInterface $dateGeneration): self
    {
        $this->dateGeneration = $dateGeneration;
        return $this;
    }

    public function getStagiaire(): ?Stagiaire
    {
        return $this->stagiaire;
    }

    public function setStagiaire(Stagiaire $stagiaire): self
    {
        $this->stagiaire = $stagiaire;
        return $this;
    }
}
