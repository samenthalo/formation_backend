<?php
// src/Entity/Reponse.php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: "reponses")]
    #[ORM\JoinColumn(name: "id_question", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?Question $question = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(type: "boolean", nullable: true)]
    private ?bool $estCorrect = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $note = null;

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

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function isEstCorrect(): ?bool
    {
        return $this->estCorrect;
    }

    public function setEstCorrect(?bool $estCorrect): self
    {
        $this->estCorrect = $estCorrect;
        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;
        return $this;
    }
}
