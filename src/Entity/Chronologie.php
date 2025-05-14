<?php

namespace App\Entity;

use App\Repository\ChronologieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChronologieRepository::class)]
class Chronologie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $id_session = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $date_evenement = null;

    #[ORM\Column(length: 100)]
    private ?string $type_evenement = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    // Getters et setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdSession(): ?int
    {
        return $this->id_session;
    }

    public function setIdSession(int $id_session): self
    {
        $this->id_session = $id_session;

        return $this;
    }

    public function getDateEvenement(): ?\DateTimeInterface
    {
        return $this->date_evenement;
    }

    public function setDateEvenement(\DateTimeInterface $date_evenement): self
    {
        $this->date_evenement = $date_evenement;

        return $this;
    }

    public function getTypeEvenement(): ?string
    {
        return $this->type_evenement;
    }

    public function setTypeEvenement(string $type_evenement): self
    {
        $this->type_evenement = $type_evenement;

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
}
