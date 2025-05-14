<?php
// src/Entity/ReponseUtilisateur.php
namespace App\Entity;

use App\Repository\ReponseUtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseUtilisateurRepository::class)]
class ReponseUtilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Question::class)]
    #[ORM\JoinColumn(name: "id_question", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Question $question = null;

    #[ORM\ManyToOne(targetEntity: Stagiaire::class)]
    #[ORM\JoinColumn(name: "id_stagiaire", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?Stagiaire $stagiaire = null;

    #[ORM\ManyToOne(targetEntity: Formateur::class)]
    #[ORM\JoinColumn(name: "id_formateur", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?Formateur $formateur = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $reponse = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $dateReponse = null;

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;
        return $this;
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

    public function getFormateur(): ?Formateur
    {
        return $this->formateur;
    }

    public function setFormateur(?Formateur $formateur): self
    {
        $this->formateur = $formateur;
        return $this;
    }

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(?string $reponse): self
    {
        $this->reponse = $reponse;
        return $this;
    }

    public function getDateReponse(): ?\DateTimeInterface
    {
        return $this->dateReponse;
    }

    public function setDateReponse(\DateTimeInterface $dateReponse): self
    {
        $this->dateReponse = $dateReponse;
        return $this;
    }
}
