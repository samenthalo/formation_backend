<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\RappelEnvoyeRepository")]
#[ORM\Table(name: "rappel_envoye")]
class RappelEnvoye
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "integer")]
    private int $id_session;

    #[ORM\Column(type: "string", length: 255)]
    private string $type_rappel;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_envoi;

    #[ORM\Column(type: "string", length: 255)]
    private string $titre_formation;

    #[ORM\Column(type: "string", length: 255)]
    private string $destinataire;

    // --- Getters & Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdSession(): int
    {
        return $this->id_session;
    }

    public function setIdSession(int $id_session): self
    {
        $this->id_session = $id_session;
        return $this;
    }

    public function getTypeRappel(): string
    {
        return $this->type_rappel;
    }

    public function setTypeRappel(string $type_rappel): self
    {
        $this->type_rappel = $type_rappel;
        return $this;
    }

    public function getDateEnvoi(): \DateTimeInterface
    {
        return $this->date_envoi;
    }

    public function setDateEnvoi(\DateTimeInterface $date_envoi): self
    {
        $this->date_envoi = $date_envoi;
        return $this;
    }

    public function getTitreFormation(): string
    {
        return $this->titre_formation;
    }

    public function setTitreFormation(string $titre_formation): self
    {
        $this->titre_formation = $titre_formation;
        return $this;
    }

    public function getDestinataire(): string
    {
        return $this->destinataire;
    }

    public function setDestinataire(string $destinataire): self
    {
        $this->destinataire = $destinataire;
        return $this;
    }
}
