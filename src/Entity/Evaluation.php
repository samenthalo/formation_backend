<?php
// src/Entity/Evaluation.php
namespace App\Entity;

use App\Repository\EvaluationRepository;
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
    private ?string $description = null;  // Nouveau champ ajouté

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $idFormation= null; // Peut être transformé en relation plus tard si nécessaire

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $tauxReussite = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $type = null; // 'quiz' ou 'questionnaire'

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

    public function getIdSession(): ?int
    {
        return $this->idFormation;
    }

    public function setIdSession(?int $idFormation): self
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
}
