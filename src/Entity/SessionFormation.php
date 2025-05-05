<?php

namespace App\Entity;

use App\Repository\SessionFormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionFormationRepository::class)]
#[ORM\Table(name: "sessionformation")]
class SessionFormation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "id_session")]
    private ?int $id_session = null;
    

    #[ORM\Column(type: "string", length: 255)]
    private string $titre;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: "integer")]
    private int $nb_heures;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: "integer")]
    private int $nb_inscrits;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $responsable_nom = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $responsable_prenom = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $responsable_telephone = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $responsable_email = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $mode = null;
    
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $lien = null;


    #[ORM\ManyToOne(targetEntity: Formation::class)]
    #[ORM\JoinColumn(name: "id_formation", referencedColumnName: "id_formation", nullable: false, onDelete: "CASCADE")]
    private ?Formation $formation = null;

    #[ORM\ManyToOne(targetEntity: Formateur::class)]
    #[ORM\JoinColumn(name: "id_formateur", referencedColumnName: "id_formateur", nullable: true, onDelete: "SET NULL")]
    private ?Formateur $formateur = null;

    #[ORM\OneToMany(mappedBy: "sessionFormation", targetEntity: SessionCreneau::class, cascade: ["persist", "remove"])]
    private Collection $creneaux;

    #[ORM\OneToMany(mappedBy: "sessionFormation", targetEntity: Inscription::class, cascade: ["persist", "remove"])]
    private Collection $inscriptions;
    

    public function __construct()
    {
        $this->creneaux = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
    }

    // Getters & Setters

    public function getIdSession(): ?int
    {
        return $this->id_session;
    }

    public function getResponsableNom(): ?string
{
    return $this->responsable_nom;
}

public function setResponsableNom(?string $responsableNom): self
{
    $this->responsable_nom = $responsableNom;
    return $this;
}

public function getResponsablePrenom(): ?string
{
    return $this->responsable_prenom;
}

public function setResponsablePrenom(?string $responsablePrenom): self
{
    $this->responsable_prenom = $responsablePrenom;
    return $this;
}

public function getResponsableTelephone(): ?string
{
    return $this->responsable_telephone;
}

public function setResponsableTelephone(?string $responsableTelephone): self
{
    $this->responsable_telephone = $responsableTelephone;
    return $this;
}

public function getResponsableEmail(): ?string
{
    return $this->responsable_email;
}

public function setResponsableEmail(?string $responsableEmail): self
{
    $this->responsable_email = $responsableEmail;
    return $this;
}

public function getMode(): ?string
{
    return $this->mode;
}

public function setMode(?string $mode): self
{
    $this->mode = $mode;
    return $this;
}

public function getLien(): ?string
{
    return $this->lien;
}

public function setLien(?string $lien): self
{
    $this->lien = $lien;
    return $this;
}

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
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

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getNbHeures(): int
    {
        return $this->nb_heures;
    }

    public function setNbHeures(int $nbHeures): self
    {
        $this->nb_heures = $nbHeures;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getNbInscrits(): int
    {
        return $this->nb_inscrits;
    }

    public function setNbInscrits(int $nbInscrits): self
    {
        $this->nb_inscrits = $nbInscrits;
        return $this;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(Formation $formation): self
    {
        $this->formation = $formation;
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

    /**
     * @return Collection|SessionCreneau[]
     */
    public function getCreneaux(): Collection
    {
        return $this->creneaux;
    }

    public function addCreneau(SessionCreneau $creneau): self
    {
        if (!$this->creneaux->contains($creneau)) {
            $this->creneaux[] = $creneau;
            $creneau->setSessionFormation($this);
        }

        return $this;
    }

    public function removeCreneau(SessionCreneau $creneau): self
    {
        if ($this->creneaux->removeElement($creneau)) {
            // set the owning side to null (unless already changed)
            if ($creneau->getSessionFormation() === $this) {
                $creneau->setSessionFormation(null);
            }
        }

        return $this;
    }

    // Méthodes pour gérer la relation avec Inscription

    /**
     * @return Collection|Inscription[]
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): self
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions[] = $inscription;
            $inscription->setSessionFormation($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): self
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getSessionFormation() === $this) {
                $inscription->setSessionFormation(null);
            }
        }

        return $this;
    }
}

