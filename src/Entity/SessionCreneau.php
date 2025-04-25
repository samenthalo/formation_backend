<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SessionCreneau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "id_creneau")]
    private ?int $id = null;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $jour;

    #[ORM\Column(type: "time")]
    private \DateTimeInterface $heureDebut;

    #[ORM\Column(type: "time")]
    private \DateTimeInterface $heureFin;

    #[ORM\ManyToOne(targetEntity: "App\Entity\SessionFormation")]
    #[ORM\JoinColumn(name: "id_session", referencedColumnName: "id_session")]
    private ?SessionFormation $sessionFormation = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Formateur")]
    #[ORM\JoinColumn(name: "id_formateur", referencedColumnName: "id_formateur", nullable: true)]
    private ?Formateur $formateur = null;

    

    // Getters & setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour(): \DateTimeInterface
    {
        return $this->jour;
    }

    public function setJour(\DateTimeInterface $jour): self
    {
        $this->jour = $jour;
        return $this;
    }

    public function getHeureDebut(): \DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(\DateTimeInterface $heureDebut): self
    {
        $this->heureDebut = $heureDebut;
        return $this;
    }

    public function getHeureFin(): \DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(\DateTimeInterface $heureFin): self
    {
        $this->heureFin = $heureFin;
        return $this;
    }

    public function getSessionFormation(): ?SessionFormation
    {
        return $this->sessionFormation;
    }

    public function setSessionFormation(?SessionFormation $sessionFormation): self
    {
        $this->sessionFormation = $sessionFormation;
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
}
