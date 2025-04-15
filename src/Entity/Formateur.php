<?php

namespace App\Entity;

use App\Repository\FormateurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormateurRepository::class)]
class Formateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_formateur = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $specialites = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(type: "boolean", nullable: true)]
    private ?bool $est_actif = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $cree_le = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $mis_a_jour = null;

    // Getters & Setters

    public function getIdFormateur(): ?int
    {
        return $this->id_formateur;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getSpecialites(): ?string
    {
        return $this->specialites;
    }

    public function setSpecialites(?string $specialites): self
    {
        $this->specialites = $specialites;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;
        return $this;
    }

    public function getEstActif(): ?bool
    {
        return $this->est_actif;
    }

    public function setEstActif(?bool $estActif): self
    {
        $this->est_actif = $estActif;
        return $this;
    }

    public function getCreeLe(): ?\DateTimeInterface
    {
        return $this->cree_le;
    }

    public function setCreeLe(?\DateTimeInterface $creeLe): self
    {
        $this->cree_le = $creeLe;
        return $this;
    }

    public function getMisAJour(): ?\DateTimeInterface
    {
        return $this->mis_a_jour;
    }

    public function setMisAJour(?\DateTimeInterface $misAJour): self
    {
        $this->mis_a_jour = $misAJour;
        return $this;
    }
}
