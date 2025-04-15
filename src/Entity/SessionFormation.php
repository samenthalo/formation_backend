<?php

namespace App\Entity;

use App\Repository\SessionFormationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionFormationRepository::class)]
#[ORM\Table(name: "sessionformation")] 
class SessionFormation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_session = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $titre;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_debut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_fin;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: "integer")]
    private int $nb_heures;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: "integer")]
    private int $nb_inscrits;

    #[ORM\ManyToOne(targetEntity: Formation::class)]
    #[ORM\JoinColumn(name: "id_formation", referencedColumnName: "id_formation", nullable: false, onDelete: "CASCADE")]
    private Formation $formation;

    // Getters & Setters

    public function getIdSession(): ?int
    {
        return $this->id_session;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateDebut(): \DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->date_debut = $dateDebut;
        return $this;
    }

    public function getDateFin(): \DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->date_fin = $dateFin;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getNbHeures(): int
    {
        return $this->nb_heures;
    }

    public function setNbHeures(int $nbHeures): self
    {
        $this->nb_heures = $nbHeures;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getNbInscrits(): int
    {
        return $this->nb_inscrits;
    }

    public function setNbInscrits(int $nbInscrits): self
    {
        $this->nb_inscrits = $nbInscrits;
        return $this;
    }

    public function getFormation(): Formation
    {
        return $this->formation;
    }

    public function setFormation(Formation $formation): self
    {
        $this->formation = $formation;
        return $this;
    }
}
