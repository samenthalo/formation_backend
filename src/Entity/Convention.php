<?php
// src/Entity/Convention.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\ConventionRepository")]
class Convention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "stagiaires", type: "text")]
    private string $stagiaires;

    #[ORM\Column(name: "idSessionFormation", type: "integer", nullable: true)]
    private ?int $idSessionFormation = null;

    #[ORM\Column(name: "nomOrganisme", type: "string", length: 255)]
    private string $nomOrganisme;

    #[ORM\Column(name: "adresseOrganisme", type: "string", length: 255)]
    private string $adresseOrganisme;

    #[ORM\Column(name: "declarationActivite", type: "string", length: 255)]
    private string $declarationActivite;

    #[ORM\Column(name: "siretOrganisme", type: "string", length: 255)]
    private string $siretOrganisme;

    #[ORM\Column(name: "representantOrganisme", type: "string", length: 255)]
    private string $representantOrganisme;

    #[ORM\Column(name: "nomSocieteBeneficiaire", type: "string", length: 255)]
    private string $nomSocieteBeneficiaire;

    #[ORM\Column(name: "adresseSocieteBeneficiaire", type: "string", length: 255)]
    private string $adresseSocieteBeneficiaire;

    #[ORM\Column(name: "siretSocieteBeneficiaire", type: "string", length: 255)]
    private string $siretSocieteBeneficiaire;

    #[ORM\Column(name: "representantSocieteBeneficiaire", type: "string", length: 255)]
    private string $representantSocieteBeneficiaire;

    #[ORM\Column(name: "objetFormation", type: "string", length: 255, nullable: true)]
    private ?string $objetFormation;

    #[ORM\Column(name: "natureFormation", type: "string", length: 255, nullable: true)]
    private ?string $natureFormation;

    #[ORM\Column(name: "dureeFormation", type: "string", length: 255, nullable: true)]
    private ?string $dureeFormation;

    #[ORM\Column(name: "typeActionFormation", type: "string", length: 255, nullable: true)]
    private ?string $typeActionFormation;

    #[ORM\Column(name: "modaliteFormation", type: "string", length: 255, nullable: true)]
    private ?string $modaliteFormation;

    #[ORM\Column(name: "nomFormation", type: "string", length: 255)]
    private string $nomFormation;

    #[ORM\Column(name: "programmeFormation", type: "string", length: 255, nullable: true)]
    private ?string $programmeFormation;

    #[ORM\Column(name: "prixFormation", type: "string", length: 255, nullable: true)]
    private ?string $prixFormation;

    #[ORM\Column(name: "dureePrixFormation", type: "string", length: 255, nullable: true)]
    private ?string $dureePrixFormation;

    #[ORM\Column(name: "modalitesReglement", type: "string", length: 255, nullable: true)]
    private ?string $modalitesReglement;

    #[ORM\Column(name: "moyensAppreciationResultats", type: "string", length: 255, nullable: true)]
    private ?string $moyensAppreciationResultats;

    #[ORM\Column(name: "dateLieu", type: "string", length: 255, nullable: true)]
    private ?string $dateLieu;

    #[ORM\Column(name: "destinataires", type: "string", length: 255, nullable: true)]
    private ?string $destinataires;

    #[ORM\Column(name: "dateGeneration", type: "datetime")]
    private \DateTimeInterface $dateGeneration;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStagiaires(): string
    {
        return $this->stagiaires;
    }

    public function setStagiaires(string $stagiaires): self
    {
        $this->stagiaires = $stagiaires;
        return $this;
    }

    public function getIdSessionFormation(): int
    {
        return $this->idSessionFormation;
    }

    public function setIdSessionFormation(int $idSessionFormation): self
    {
        $this->idSessionFormation = $idSessionFormation;
        return $this;
    }

    public function getNomOrganisme(): string
    {
        return $this->nomOrganisme;
    }

    public function setNomOrganisme(string $nomOrganisme): self
    {
        $this->nomOrganisme = $nomOrganisme;
        return $this;
    }

    public function getAdresseOrganisme(): string
    {
        return $this->adresseOrganisme;
    }

    public function setAdresseOrganisme(string $adresseOrganisme): self
    {
        $this->adresseOrganisme = $adresseOrganisme;
        return $this;
    }

    public function getDeclarationActivite(): string
    {
        return $this->declarationActivite;
    }

    public function setDeclarationActivite(string $declarationActivite): self
    {
        $this->declarationActivite = $declarationActivite;
        return $this;
    }

    public function getSiretOrganisme(): string
    {
        return $this->siretOrganisme;
    }

    public function setSiretOrganisme(string $siretOrganisme): self
    {
        $this->siretOrganisme = $siretOrganisme;
        return $this;
    }

    public function getRepresentantOrganisme(): string
    {
        return $this->representantOrganisme;
    }

    public function setRepresentantOrganisme(string $representantOrganisme): self
    {
        $this->representantOrganisme = $representantOrganisme;
        return $this;
    }

    public function getNomSocieteBeneficiaire(): string
    {
        return $this->nomSocieteBeneficiaire;
    }

    public function setNomSocieteBeneficiaire(string $nomSocieteBeneficiaire): self
    {
        $this->nomSocieteBeneficiaire = $nomSocieteBeneficiaire;
        return $this;
    }

    public function getAdresseSocieteBeneficiaire(): string
    {
        return $this->adresseSocieteBeneficiaire;
    }

    public function setAdresseSocieteBeneficiaire(string $adresseSocieteBeneficiaire): self
    {
        $this->adresseSocieteBeneficiaire = $adresseSocieteBeneficiaire;
        return $this;
    }

    public function getSiretSocieteBeneficiaire(): string
    {
        return $this->siretSocieteBeneficiaire;
    }

    public function setSiretSocieteBeneficiaire(string $siretSocieteBeneficiaire): self
    {
        $this->siretSocieteBeneficiaire = $siretSocieteBeneficiaire;
        return $this;
    }

    public function getRepresentantSocieteBeneficiaire(): string
    {
        return $this->representantSocieteBeneficiaire;
    }

    public function setRepresentantSocieteBeneficiaire(string $representantSocieteBeneficiaire): self
    {
        $this->representantSocieteBeneficiaire = $representantSocieteBeneficiaire;
        return $this;
    }

    public function getObjetFormation(): ?string
    {
        return $this->objetFormation;
    }

    public function setObjetFormation(?string $objetFormation): self
    {
        $this->objetFormation = $objetFormation;
        return $this;
    }

    public function getNatureFormation(): ?string
    {
        return $this->natureFormation;
    }

    public function setNatureFormation(?string $natureFormation): self
    {
        $this->natureFormation = $natureFormation;
        return $this;
    }

    public function getDureeFormation(): ?string
    {
        return $this->dureeFormation;
    }

    public function setDureeFormation(?string $dureeFormation): self
    {
        $this->dureeFormation = $dureeFormation;
        return $this;
    }

    public function getTypeActionFormation(): ?string
    {
        return $this->typeActionFormation;
    }

    public function setTypeActionFormation(?string $typeActionFormation): self
    {
        $this->typeActionFormation = $typeActionFormation;
        return $this;
    }

    public function getModaliteFormation(): ?string
    {
        return $this->modaliteFormation;
    }

    public function setModaliteFormation(?string $modaliteFormation): self
    {
        $this->modaliteFormation = $modaliteFormation;
        return $this;
    }

    public function getNomFormation(): string
    {
        return $this->nomFormation;
    }

    public function setNomFormation(string $nomFormation): self
    {
        $this->nomFormation = $nomFormation;
        return $this;
    }

    public function getProgrammeFormation(): ?string
    {
        return $this->programmeFormation;
    }

    public function setProgrammeFormation(?string $programmeFormation): self
    {
        $this->programmeFormation = $programmeFormation;
        return $this;
    }

    public function getPrixFormation(): ?string
    {
        return $this->prixFormation;
    }

    public function setPrixFormation(?string $prixFormation): self
    {
        $this->prixFormation = $prixFormation;
        return $this;
    }

    public function getDureePrixFormation(): ?string
    {
        return $this->dureePrixFormation;
    }

    public function setDureePrixFormation(?string $dureePrixFormation): self
    {
        $this->dureePrixFormation = $dureePrixFormation;
        return $this;
    }

    public function getModalitesReglement(): ?string
    {
        return $this->modalitesReglement;
    }

    public function setModalitesReglement(?string $modalitesReglement): self
    {
        $this->modalitesReglement = $modalitesReglement;
        return $this;
    }

    public function getMoyensAppreciationResultats(): ?string
    {
        return $this->moyensAppreciationResultats;
    }

    public function setMoyensAppreciationResultats(?string $moyensAppreciationResultats): self
    {
        $this->moyensAppreciationResultats = $moyensAppreciationResultats;
        return $this;
    }

    public function getDateLieu(): ?string
    {
        return $this->dateLieu;
    }

    public function setDateLieu(?string $dateLieu): self
    {
        $this->dateLieu = $dateLieu;
        return $this;
    }

    public function getDestinataires(): ?string
    {
        return $this->destinataires;
    }

    public function setDestinataires(?string $destinataires): self
    {
        $this->destinataires = $destinataires;
        return $this;
    }

    public function getDateGeneration(): \DateTimeInterface
    {
        return $this->dateGeneration;
    }

    public function setDateGeneration(\DateTimeInterface $dateGeneration): self
    {
        $this->dateGeneration = $dateGeneration;
        return $this;
    }
}
