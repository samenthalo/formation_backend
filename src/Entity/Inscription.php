<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_inscription = null;

    #[ORM\Column(type: "string", length: 100)]
    private ?string $nom_stagiaire = null;

    #[ORM\Column(type: "string", length: 100)]
    private ?string $prenom_stagiaire = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $telephone_stagiaire = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $email_stagiaire = null;

    #[ORM\Column(type: "integer")]
    private ?int $id_session = null;

    #[ORM\Column(type: "string", length: 50)]
    private ?string $statut = null;

    // Getters & Setters

    public function getIdInscription(): ?int
    {
        return $this->id_inscription;
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

    public function getIdSession(): ?int
    {
        return $this->id_session;
    }

    public function setIdSession(int $idSession): self
    {
        $this->id_session = $idSession;
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
