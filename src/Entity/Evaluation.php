<?php
// src/Entity/Evaluation.php
namespace App\Entity;

use App\Repository\EvaluationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(type: "string", length: 250, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $idFormation = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $tauxReussite = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $type = null;

    // Définir la relation OneToMany avec l'entité Question
    #[ORM\OneToMany(mappedBy: 'evaluation', targetEntity: Question::class, cascade: ['persist', 'remove'])]
    private Collection $questions;

    public function __construct()
    {
        // Initialiser la collection de questions
        $this->questions = new ArrayCollection();
    }

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
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

    public function getIdFormation(): ?int
    {
        return $this->idFormation;
    }

    public function setIdFormation(?int $idFormation): self
    {
        $this->idFormation = $idFormation;
        return $this;
    }

    public function getTauxReussite(): ?float
    {
        return $this->tauxReussite;
    }

    public function setTauxReussite(?float $tauxReussite): self
    {
        $this->tauxReussite = $tauxReussite;
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

    // Méthodes pour accéder et gérer les questions

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setEvaluation($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getEvaluation() === $this) {
                $question->setEvaluation(null);
            }
        }

        return $this;
    }
}
