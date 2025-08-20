<?php
// src/Entity/EvaluationFormateur.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\EvaluationFormateurRepository;

#[ORM\Entity(repositoryClass: EvaluationFormateurRepository::class)]
#[ORM\Table(name: "evaluation_formateur")]
class EvaluationFormateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    // Relation ManyToOne vers Evaluation
    #[ORM\ManyToOne(targetEntity: Evaluation::class)]
    #[ORM\JoinColumn( name: "id_evaluation", nullable: false, referencedColumnName: "id", onDelete:"CASCADE")]
    private ?Evaluation $evaluation = null;

    // Relation ManyToOne vers Formateur
    #[ORM\ManyToOne(targetEntity: Formateur::class)]
    #[ORM\JoinColumn(name: "id_formateur", referencedColumnName: "id_formateur", nullable: false, onDelete: "CASCADE")]
    private ?Formateur $formateur = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateAssignation;

    #[ORM\Column(type: "string", length: 50)]
    private string $statut = 'non commencé';

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $score = null;

    // --- Getters et setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvaluation(): ?Evaluation
    {
        return $this->evaluation;
    }

    public function setEvaluation(?Evaluation $evaluation): self
    {
        $this->evaluation = $evaluation;
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

    public function getDateAssignation(): \DateTimeInterface
    {
        return $this->dateAssignation;
    }

    public function setDateAssignation(\DateTimeInterface $dateAssignation): self
    {
        $this->dateAssignation = $dateAssignation;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(?float $score): self
    {
        $this->score = $score;
        return $this;
    }
}
