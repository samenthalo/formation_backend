<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\FichePresenceRepository;
use App\Entity\Formateur;
use App\Entity\SessionFormation;  // N'oublie pas d'importer l'entité

#[ORM\Entity(repositoryClass: FichePresenceRepository::class)]
class FichePresence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    // On remplace id_session par une relation ManyToOne vers SessionFormation
    #[ORM\ManyToOne(targetEntity: SessionFormation::class)]
    #[ORM\JoinColumn(name: "id_session", referencedColumnName: "id_session", nullable: false)]
    private ?SessionFormation $sessionFormation = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $chemin_fichier;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_generation;

    #[ORM\ManyToOne(targetEntity: Formateur::class, inversedBy: 'fichesPresence')]
    #[ORM\JoinColumn(name: 'id_formateur', referencedColumnName: 'id_formateur', nullable: false)]
    private ?Formateur $formateur = null;


    // --- Getters and Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    // Nouveau getter pour la session formation (objet)
    public function getSessionFormation(): ?SessionFormation
    {
        return $this->sessionFormation;
    }

    // Nouveau setter pour la session formation (objet)
    public function setSessionFormation(?SessionFormation $sessionFormation): self
    {
        $this->sessionFormation = $sessionFormation;
        return $this;
    }

    public function getCheminFichier(): string
    {
        return $this->chemin_fichier;
    }

    public function setCheminFichier(string $chemin_fichier): self
    {
        $this->chemin_fichier = $chemin_fichier;
        return $this;
    }

    public function getDateGeneration(): \DateTimeInterface
    {
        return $this->date_generation;
    }

    public function setDateGeneration(\DateTimeInterface $date_generation): self
    {
        $this->date_generation = $date_generation;
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
