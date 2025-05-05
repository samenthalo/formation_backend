<?php

namespace App\Entity;

use App\Repository\StagiaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: StagiaireRepository::class)]
class Stagiaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_stagiaire = null;

    #[ORM\Column(type: "string", length: 100)]
    private ?string $nom_stagiaire = null;

    #[ORM\Column(type: "string", length: 100)]
    private ?string $prenom_stagiaire = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $telephone_stagiaire = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $email_stagiaire = null;

    #[ORM\Column(type: "string", length: 250, nullable: true)]
    private ?string $entreprise_stagiaire = null;

    #[ORM\Column(type: "string", length: 250, nullable: true)]
    private ?string $fonction_stagiaire = null;

    // Relation avec Inscription (un stagiaire peut avoir plusieurs inscriptions)
    #[ORM\OneToMany(mappedBy: 'stagiaire', targetEntity: Inscription::class)]
    private Collection $inscriptions;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
    }

    // Getters & Setters pour les autres propriétés
    public function getIdStagiaire(): ?int
    {
        return $this->id_stagiaire;
    }

    public function getNomStagiaire(): ?string
    {
        return $this->nom_stagiaire;
    }

    public function setNomStagiaire(string $nomStagiaire): self
    {
        $this->nom_stagiaire = $nomStagiaire;
        return $this;
    }

    public function getPrenomStagiaire(): ?string
    {
        return $this->prenom_stagiaire;
    }

    public function setPrenomStagiaire(string $prenomStagiaire): self
    {
        $this->prenom_stagiaire = $prenomStagiaire;
        return $this;
    }

    public function getTelephoneStagiaire(): ?string
    {
        return $this->telephone_stagiaire;
    }

    public function setTelephoneStagiaire(?string $telephoneStagiaire): self
    {
        $this->telephone_stagiaire = $telephoneStagiaire;
        return $this;
    }

    public function getEmailStagiaire(): ?string
    {
        return $this->email_stagiaire;
    }

    public function setEmailStagiaire(?string $emailStagiaire): self
    {
        $this->email_stagiaire = $emailStagiaire;
        return $this;
    }

    public function getEntrepriseStagiaire(): ?string
    {
        return $this->entreprise_stagiaire;
    }

    public function setEntrepriseStagiaire(?string $entrepriseStagiaire): self
    {
        $this->entreprise_stagiaire = $entrepriseStagiaire;
        return $this;
    }

    public function getFonctionStagiaire(): ?string
    {
        return $this->fonction_stagiaire;
    }

    public function setFonctionStagiaire(?string $fonctionStagiaire): self
    {
        $this->fonction_stagiaire = $fonctionStagiaire;
        return $this;
    }

    /**
     * @return Collection|Inscription[]
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }
}
