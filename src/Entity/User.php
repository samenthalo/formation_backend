<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $nom;

    #[ORM\Column(length: 100)]
    private string $prenom;

    #[ORM\Column(length: 191, unique: true)]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $motDePasse;

    #[ORM\Column(length: 20)]
    private string $role;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $dateCreation;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    // Getters et setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getDateCreation(): \DateTimeImmutable
    {
        return $this->dateCreation;
    }

    // --- Méthodes obligatoires UserInterface & PasswordAuthenticatedUserInterface ---

    // Retourne l'identifiant unique, ici l'email
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // Retourne les rôles sous forme de tableau
    public function getRoles(): array
    {
        // Pour que Symfony attende un tableau, on retourne toujours un tableau,
        // tu peux adapter la gestion si tu as plusieurs rôles par utilisateur.
        return [$this->role];
    }

    // Méthode pour PasswordAuthenticatedUserInterface (qui remplace getPassword)
    public function getPassword(): string
    {
        return $this->motDePasse;
    }

    // Efface les données sensibles temporaires (pas nécessaire si pas de données temporaires)
    public function eraseCredentials(): void
    {
        // rien à faire ici pour le moment
    }
}
