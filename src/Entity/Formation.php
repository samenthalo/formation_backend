<?php

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Groups(['formation:read'])]
    private ?int $id_formation = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    #[Groups(['formation:read'])]
    private ?string $titre = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Groups(['formation:read'])]
    private ?string $description = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    #[Groups(['formation:read'])]
    private ?float $prix_unitaire_ht = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Groups(['formation:read'])]
    private ?int $nb_participants_max = null;

    #[ORM\Column(type: "boolean", nullable: true)]
    #[Groups(['formation:read'])]
    private ?bool $est_active = null;

    #[ORM\Column(type: "string", length: 10, nullable: true)]
    #[Groups(['formation:read'])]
    private ?string $type_formation = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Groups(['formation:read'])]
    private ?int $duree_heures = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    #[Groups(['formation:read'])]
    private ?string $categorie = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Groups(['formation:read'])]
    private ?string $programme = null;

    #[ORM\Column(type: "boolean", nullable: true)]
    #[Groups(['formation:read'])]
    private ?bool $multi_jour = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Groups(['formation:read'])]
    private ?string $cible = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Groups(['formation:read'])]
    private ?string $moyens_pedagogiques = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Groups(['formation:read'])]
    private ?string $pre_requis = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Groups(['formation:read'])]
    private ?string $delai_acces = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Groups(['formation:read'])]
    private ?string $supports_pedagogiques = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Groups(['formation:read'])]
    private ?string $methodes_evaluation = null;

    #[ORM\Column(name: '`accessible`', type: "boolean", nullable: true)]
    #[Groups(['formation:read'])]
    private ?bool $accessible = null;

    #[ORM\Column(type: "decimal", precision: 5, scale: 2, nullable: true)]
    #[Groups(['formation:read'])]
    private ?float $taux_tva = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['formation:read', 'formation:write'])] 
    private ?string $welcomeBooklet = null;

    // Getters & Setters

    public function getIdFormation(): ?int
    {
        return $this->id_formation;
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

    public function getPrixUnitaireHt(): ?float
    {
        return $this->prix_unitaire_ht;
    }

    public function setPrixUnitaireHt(?float $prixUnitaireHt): self
    {
        $this->prix_unitaire_ht = $prixUnitaireHt;
        return $this;
    }

    public function getNbParticipantsMax(): ?int
    {
        return $this->nb_participants_max;
    }

    public function setNbParticipantsMax(?int $nbParticipantsMax): self
    {
        $this->nb_participants_max = $nbParticipantsMax;
        return $this;
    }

    public function getEstActive(): ?bool
    {
        return $this->est_active;
    }

    public function setEstActive(?bool $estActive): self
    {
        $this->est_active = $estActive;
        return $this;
    }

    public function getTypeFormation(): ?string
    {
        return $this->type_formation;
    }

    public function setTypeFormation(?string $typeFormation): self
    {
        $this->type_formation = $typeFormation;
        return $this;
    }

    public function getDureeHeures(): ?int
    {
        return $this->duree_heures;
    }

    public function setDureeHeures(?int $dureeHeures): self
    {
        $this->duree_heures = $dureeHeures;
        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getProgramme(): ?string
    {
        return $this->programme;
    }

    public function setProgramme(?string $programme): self
    {
        $this->programme = $programme;
        return $this;
    }

    public function getMultiJour(): ?bool
    {
        return $this->multi_jour;
    }

    public function setMultiJour(?bool $multiJour): self
    {
        $this->multi_jour = $multiJour;
        return $this;
    }

    public function getCible(): ?string
    {
        return $this->cible;
    }

    public function setCible(?string $cible): self
    {
        $this->cible = $cible;
        return $this;
    }

    public function getMoyensPedagogiques(): ?string
    {
        return $this->moyens_pedagogiques;
    }

    public function setMoyensPedagogiques(?string $moyensPedagogiques): self
    {
        $this->moyens_pedagogiques = $moyensPedagogiques;
        return $this;
    }

    public function getPreRequis(): ?string
    {
        return $this->pre_requis;
    }

    public function setPreRequis(?string $preRequis): self
    {
        $this->pre_requis = $preRequis;
        return $this;
    }

    public function getDelaiAcces(): ?string
    {
        return $this->delai_acces;
    }

    public function setDelaiAcces(?string $delaiAcces): self
    {
        $this->delai_acces = $delaiAcces;
        return $this;
    }

    public function getSupportsPedagogiques(): ?string
    {
        return $this->supports_pedagogiques;
    }

    public function setSupportsPedagogiques(?string $supportsPedagogiques): self
    {
        $this->supports_pedagogiques = $supportsPedagogiques;
        return $this;
    }

    public function getMethodesEvaluation(): ?string
    {
        return $this->methodes_evaluation;
    }

    public function setMethodesEvaluation(?string $methodesEvaluation): self
    {
        $this->methodes_evaluation = $methodesEvaluation;
        return $this;
    }

    public function getAccessible(): ?bool
    {
        return $this->accessible;
    }

    public function setAccessible(?bool $accessible): self
    {
        $this->accessible = $accessible;
        return $this;
    }

    public function getTauxTva(): ?float
    {
        return $this->taux_tva;
    }

    public function setTauxTva(?float $tauxTva): self
    {
        $this->taux_tva = $tauxTva;
        return $this;
    }

    public function getWelcomeBooklet(): ?string
{
    return $this->welcomeBooklet;
}

public function setWelcomeBooklet(?string $welcomeBooklet): self
{
    $this->welcomeBooklet = $welcomeBooklet;

    return $this;
}
}
