<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

// ==========================
// ENTITE UTILISATEUR
// ==========================

// Cette classe représente un utilisateur enregistré en base de donnée
#[ORM\Entity(repositoryClass: UserRepository::class)]

// L'adresse email doit être unique
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(
    fields: ['email'],
    message: 'There is already an account with this email'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Id unique générer automatiquement par doctrine
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Email utilisé comme identifiant de connexion
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    // Rôles utilisés par le système de sécurité Symfony
    #[ORM\Column]
    private array $roles = [];

    // Le mot de passe stocké ici est le mot de passe hashé
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    // Seul le nom du fichier de la photo est enregistré en BDD
    #[ORM\Column(length: 255)]
    private ?string $photo = null;

    // La biographie est facultative
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $biographie = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateInscription = null;


    // ==========================
    // RELATIONS
    // ==========================

    // Un utilisateur peut avoir plusieur publications
    // Chaque publication possède un auteur
    #[ORM\OneToMany(targetEntity: Publication::class, mappedBy: 'auteur')]
    private Collection $publications;

    // Demande d'amitié envoyée par cet utilisateur
    #[ORM\OneToMany(targetEntity: DemandeAmitie::class, mappedBy: 'demandeur')]
    private Collection $demandesEnvoyees;

    // Demande d'amitié recues par cet utilisateur
    #[ORM\OneToMany(targetEntity: DemandeAmitie::class, mappedBy: 'destinataire')]
    private Collection $demandesRecues;


    public function __construct()
    {
        // Initialisation des relations contenant plusieurs objets
        $this->publications = new ArrayCollection();
        $this->demandesEnvoyees = new ArrayCollection();
        $this->demandesRecues = new ArrayCollection();
    }


    // ==========================
    // GETTERS / SETTERS
    // ==========================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }


    // ==========================
    // SECURITE
    // ==========================

    // L'email est utilisé comme identifiant unique de connexion
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // Tous les utilisateurs possèdent au minimum ROLE_USER
        $roles[] = 'ROLE_USER';

        // Évite d'avoir plusieurs fois le même rôle
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Gestion interne de sécurité générée par Symfony.
     * Évite de conserver directement le hash du mot de passe dans la session
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void {}


    // ==========================
    // INFORMATIONS DU PROFIL
    // ==========================

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getBiographie(): ?string
    {
        return $this->biographie;
    }

    public function setBiographie(?string $biographie): static
    {
        $this->biographie = $biographie;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeImmutable
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeImmutable $dateInscription): static
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }


    // ==========================
    // PUBLICATIONS
    // ==========================

    public function getPublications(): Collection
    {
        return $this->publications;
    }

    public function addPublication(Publication $publication): static
    {
        // On évite d'ajouter deux fois la meme publication
        if (!$this->publications->contains($publication)) {
            $this->publications->add($publication);

            // Mise a jour également du coté Publication
            $publication->setAuteur($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): static
    {
        if ($this->publications->removeElement($publication)) {

            // Mise a jour également du coté Publication
            if ($publication->getAuteur() === $this) {
                $publication->setAuteur(null);
            }
        }

        return $this;
    }


    // ==========================
    // DEMANDES D'AMITIE ENVOYES
    // ==========================

    public function getDemandesEnvoyees(): Collection
    {
        return $this->demandesEnvoyees;
    }

    public function addDemandesEnvoyee(
        DemandeAmitie $demandesEnvoyee
    ): static {
        if (!$this->demandesEnvoyees->contains($demandesEnvoyee)) {
            $this->demandesEnvoyees->add($demandesEnvoyee);

            // Cet utilisateur devient le demandeur
            $demandesEnvoyee->setDemandeur($this);
        }

        return $this;
    }

    public function removeDemandesEnvoyee(
        DemandeAmitie $demandesEnvoyee
    ): static {
        if ($this->demandesEnvoyees->removeElement($demandesEnvoyee)) {
            if ($demandesEnvoyee->getDemandeur() === $this) {
                $demandesEnvoyee->setDemandeur(null);
            }
        }

        return $this;
    }


    // ==========================
    // DEMANDES D'AMITIE RECUES
    // ==========================

    public function getDemandesRecues(): Collection
    {
        return $this->demandesRecues;
    }

    public function addDemandesRecue(
        DemandeAmitie $demandesRecue
    ): static {
        if (!$this->demandesRecues->contains($demandesRecue)) {
            $this->demandesRecues->add($demandesRecue);

            // Cet utilisateur devient le destinataire
            $demandesRecue->setDestinataire($this);
        }

        return $this;
    }

    public function removeDemandesRecue(
        DemandeAmitie $demandesRecue
    ): static {
        if ($this->demandesRecues->removeElement($demandesRecue)) {
            if ($demandesRecue->getDestinataire() === $this) {
                $demandesRecue->setDestinataire(null);
            }
        }

        return $this;
    }
}
