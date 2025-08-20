<?php
// src/Entity/EvaluationStagiaire.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "evaluation_stagiaire")]
class EvaluationStagiaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Evaluation::class)]
    #[ORM\JoinColumn(nullable: false, name: "id_evaluation", referencedColumnName: "id")]
    private ?Evaluation $evaluation = null;

    #[ORM\ManyToOne(targetEntity: Stagiaire::class)]
    #[ORM\JoinColumn(nullable: false, name: "id_stagiaire", referencedColumnName: "id_stagiaire")]
    private ?Stagiaire $stagiaire = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $dateAssignation = null;

    #[ORM\Column(type: "string", length: 20)]
    private string $statut = 'non commencé';

    #[ORM\Column(type: "decimal", precision: 5, scale: 2, nullable: true)]
    private ?float $score = null;

    // Getters et setters

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

    public function getStagiaire(): ?Stagiaire
    {
        return $this->stagiaire;
    }

    public function setStagiaire(?Stagiaire $stagiaire): self
    {
        $this->stagiaire = $stagiaire;
        return $this;
    }

    public function getDateAssignation(): ?\DateTimeInterface
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
