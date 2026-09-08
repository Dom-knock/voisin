<?php

namespace App\Entity;

use App\Repository\DemandeAmitieRepository;
use Doctrine\ORM\Mapping as ORM;

// ==========================
// ENTITE DEMANDE D'AMITIE
// ==========================

// Cette classe représente une demande d'amitié entre deux utilisateurs
#[ORM\Entity(repositoryClass: DemandeAmitieRepository::class)]
class DemandeAmitie
{
    // Ide unique généré automatiquement par doctrine
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Date à laquelle la demande d'amitié a été faite
    #[ORM\Column]
    private ?\DateTimeImmutable $dateDemande = null;

    // Etat de la demande en_attente, acceptee ou refusee
    #[ORM\Column(length: 50)]
    private ?string $status = null;


    // ==========================
    // RELATIONS AVEC USER
    // ==========================

    // Utilisateur qui envoie la demande d'amitié
    #[ORM\ManyToOne(inversedBy: 'demandesEnvoyees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $demandeur = null;

    // Utilisateur qui recoit la demande d'amitié
    #[ORM\ManyToOne(inversedBy: 'demandesRecues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $destinataire = null;


    // ==========================
    // GETTERS / SETTERS
    // ==========================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDemande(): ?\DateTimeImmutable
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeImmutable $dateDemande): static
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDemandeur(): ?User
    {
        return $this->demandeur;
    }

    public function setDemandeur(?User $demandeur): static
    {
        $this->demandeur = $demandeur;

        return $this;
    }

    public function getDestinataire(): ?User
    {
        return $this->destinataire;
    }

    public function setDestinataire(?User $destinataire): static
    {
        $this->destinataire = $destinataire;

        return $this;
    }
}
