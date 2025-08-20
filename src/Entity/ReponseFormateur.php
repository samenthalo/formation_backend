<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReponseFormateurRepository;

#[ORM\Entity(repositoryClass: ReponseFormateurRepository::class)]
#[ORM\Table(name: "reponse_formateur")]
class ReponseFormateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Question::class)]
    #[ORM\JoinColumn(name: "id_question", referencedColumnName: "id", nullable: false)]
    private ?Question $question = null;

    #[ORM\ManyToOne(targetEntity: Formateur::class)]
    #[ORM\JoinColumn(name: "id_formateur", referencedColumnName: "id_formateur", nullable: true)]
    private ?Formateur $formateur = null;

    #[ORM\ManyToOne(targetEntity: Reponse::class)]
    #[ORM\JoinColumn(name: "id_reponse", referencedColumnName: "id", nullable: true)]
    private ?Reponse $reponsePredefinie = null;

    #[ORM\Column(name: "reponse", type: "text", nullable: true)]
    private ?string $reponse = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $dateReponse = null;

    public function __construct()
    {
        $this->dateReponse = new \DateTime();
    }

    // --- Getters et setters ---

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

    public function getFormateur(): ?Formateur
    {
        return $this->formateur;
    }

    public function setFormateur(?Formateur $formateur): self
    {
        $this->formateur = $formateur;
        return $this;
    }

    public function getReponsePredefinie(): ?Reponse
    {
        return $this->reponsePredefinie;
    }

    public function setReponsePredefinie(?Reponse $reponsePredefinie): self
    {
        $this->reponsePredefinie = $reponsePredefinie;
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

    public function setDateReponse(?\DateTimeInterface $dateReponse): self
    {
        $this->dateReponse = $dateReponse;
        return $this;
    }
}
