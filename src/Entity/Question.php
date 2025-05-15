<?php
// src/Entity/Question.php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Evaluation::class, inversedBy: "questions")]
    #[ORM\JoinColumn(name: "id_evaluation", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?Evaluation $evaluation = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $type = null; // choix_unique, choix_multiple, note, etc.

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $options = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $minNote = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $maxNote = null;

    #[ORM\OneToMany(targetEntity: Reponse::class, mappedBy: "question", cascade: ["persist", "remove"])]
    private Collection $reponses;

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
    }

    // Getters et Setters

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

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function getMinNote(): ?int
    {
        return $this->minNote;
    }

    public function setMinNote(?int $minNote): self
    {
        $this->minNote = $minNote;
        return $this;
    }

    public function getMaxNote(): ?int
    {
        return $this->maxNote;
    }

    public function setMaxNote(?int $maxNote): self
    {
        $this->maxNote = $maxNote;
        return $this;
    }

    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): self
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses[] = $reponse;
            $reponse->setQuestion($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): self
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getQuestion() === $this) {
                $reponse->setQuestion(null);
            }
        }

        return $this;
    }
}
