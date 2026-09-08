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

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $biographie = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateInscription = null;

    /**
     * @var Collection<int, Publication>
     */
    #[ORM\OneToMany(targetEntity: Publication::class, mappedBy: 'auteur')]
    private Collection $publications;

    /**
     * @var Collection<int, DemandeAmitie>
     */
    #[ORM\OneToMany(targetEntity: DemandeAmitie::class, mappedBy: 'demandeur')]
    private Collection $demandesEnvoyees;

    /**
     * @var Collection<int, DemandeAmitie>
     */
    #[ORM\OneToMany(targetEntity: DemandeAmitie::class, mappedBy: 'destinataire')]
    private Collection $demandesRecues;

    public function __construct()
    {
        $this->publications = new ArrayCollection();
        $this->demandesEnvoyees = new ArrayCollection();
        $this->demandesRecues = new ArrayCollection();
    }

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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
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
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

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

    /**
     * @return Collection<int, Publication>
     */
    public function getPublications(): Collection
    {
        return $this->publications;
    }

    public function addPublication(Publication $publication): static
    {
        if (!$this->publications->contains($publication)) {
            $this->publications->add($publication);
            $publication->setAuteur($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): static
    {
        if ($this->publications->removeElement($publication)) {
            // set the owning side to null (unless already changed)
            if ($publication->getAuteur() === $this) {
                $publication->setAuteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DemandeAmitie>
     */
    public function getDemandesEnvoyees(): Collection
    {
        return $this->demandesEnvoyees;
    }

    public function addDemandesEnvoyee(DemandeAmitie $demandesEnvoyee): static
    {
        if (!$this->demandesEnvoyees->contains($demandesEnvoyee)) {
            $this->demandesEnvoyees->add($demandesEnvoyee);
            $demandesEnvoyee->setDemandeur($this);
        }

        return $this;
    }

    public function removeDemandesEnvoyee(DemandeAmitie $demandesEnvoyee): static
    {
        if ($this->demandesEnvoyees->removeElement($demandesEnvoyee)) {
            // set the owning side to null (unless already changed)
            if ($demandesEnvoyee->getDemandeur() === $this) {
                $demandesEnvoyee->setDemandeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DemandeAmitie>
     */
    public function getDemandesRecues(): Collection
    {
        return $this->demandesRecues;
    }

    public function addDemandesRecue(DemandeAmitie $demandesRecue): static
    {
        if (!$this->demandesRecues->contains($demandesRecue)) {
            $this->demandesRecues->add($demandesRecue);
            $demandesRecue->setDestinataire($this);
        }

        return $this;
    }

    public function removeDemandesRecue(DemandeAmitie $demandesRecue): static
    {
        if ($this->demandesRecues->removeElement($demandesRecue)) {
            // set the owning side to null (unless already changed)
            if ($demandesRecue->getDestinataire() === $this) {
                $demandesRecue->setDestinataire(null);
            }
        }

        return $this;
    }
}
