<?php

namespace App\Entity;

use App\Repository\PublicationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

// ==========================
// ENTITE PUBLICATION
// ==========================

// Cette classe représente une publication enregistrée en base de donnée
#[ORM\Entity(repositoryClass: PublicationRepository::class)]
class Publication
{
    // Identifiant unique généré automatiquement par Doctrine
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Le texte de la publication est facultatif
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $texte = null;

    // Seul le nom du fichier image est enregistré en base
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    // Définit qui peut voir la publication publique ou amis
    #[ORM\Column(length: 255)]
    private ?string $visibilite = null;

    // Date de création de la publication
    #[ORM\Column]
    private ?\DateTimeImmutable $datePublication = null;


    // ==========================
    // RELATION AVEC L'AUTEUR
    // ==========================

    // Plusieurs publications peuvent appartenir a un meme utilisateur
    #[ORM\ManyToOne(inversedBy: 'publications')]

    // Une publication doit obligatoirement avoir un auteur
    #[ORM\JoinColumn(nullable: false)]
    private ?User $auteur = null;


    // ==========================
    // GETTERS / SETTERS
    // ==========================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(?string $texte): static
    {
        $this->texte = $texte;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getVisibilite(): ?string
    {
        return $this->visibilite;
    }

    public function setVisibilite(string $visibilite): static
    {
        $this->visibilite = $visibilite;

        return $this;
    }

    public function getDatePublication(): ?\DateTimeImmutable
    {
        return $this->datePublication;
    }

    public function setDatePublication(
        \DateTimeImmutable $datePublication
    ): static {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }
}
