<?php

namespace App\Entity;

use App\Repository\EmargementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmargementRepository::class)]
class Emargement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_participe = null;

    #[ORM\Column(type: "string", length: 100)]
    private string $nom_stagiaire;

    #[ORM\Column(type: "string", length: 100)]
    private string $prenom_stagiaire;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $numero_telephone = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_heure;

    #[ORM\Column(type: "blob", nullable: true)]
    private $signature;

    #[ORM\ManyToOne(targetEntity: SessionFormation::class)]
    #[ORM\JoinColumn(name: "session_formation", referencedColumnName: "id_session", nullable: false, onDelete: "CASCADE")]
    private SessionFormation $session_formation;

    // Getters & Setters
    public function getIdParticipe(): ?int
    {
        return $this->id_participe;
    }

    public function getNomStagiaire(): string
    {
        return $this->nom_stagiaire;
    }

    public function setNomStagiaire(string $nom): self
    {
        $this->nom_stagiaire = $nom;
        return $this;
    }

    public function getPrenomStagiaire(): string
    {
        return $this->prenom_stagiaire;
    }

    public function setPrenomStagiaire(string $prenom): self
    {
        $this->prenom_stagiaire = $prenom;
        return $this;
    }

    public function getNumeroTelephone(): ?string
    {
        return $this->numero_telephone;
    }

    public function setNumeroTelephone(?string $tel): self
    {
        $this->numero_telephone = $tel;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getDateHeure(): \DateTimeInterface
    {
        return $this->date_heure;
    }

    public function setDateHeure(\DateTimeInterface $dateHeure): self
    {
        $this->date_heure = $dateHeure;
        return $this;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function setSignature($signature): self
    {
        $this->signature = $signature;
        return $this;
    }

    public function getSessionFormation(): SessionFormation
    {
        return $this->session_formation;
    }

    public function setSessionFormation(SessionFormation $session): self
    {
        $this->session_formation = $session;
        return $this;
    }
}
