<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\DemandeAmitie;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DemandeAmitieRepository;

// Seuls les utilisateurs connectés peuvent accéder à la gestion des amis
#[IsGranted('ROLE_USER')]
final class AmitieController extends AbstractController
{
    // ==========================
    // AFFICHER LA PAGE DES AMIS
    // ==========================
    #[Route('/amis', name: 'app_amis')]
    public function index(UserRepository $userRepository, DemandeAmitieRepository $demandeAmitieRepository): Response
    {
        // Récupèration de l'utilisateur connecté
        $utilisateurConnecte = $this->getUser();
        // Récupération de tous les utilisateurs du site
        $utilisateurs = $userRepository->findAll();

        // ==========================
        // DEMANDES RECUES
        // ==========================

        // Demandes d'amitié reçues et encore en attente
        $demandesRecues = $demandeAmitieRepository->findBy(
            [
                'destinataire' => $utilisateurConnecte,
                'status' => 'en_attente',
            ],
            [
                'dateDemande' => 'DESC',
            ]
        );
        // ==========================
        // RECUPERATION DES AMIS
        // ==========================

        // Amitiés acceptées ou l'utilisateur connecté
        // est celui qui avait envoyé la demande
        $amitieEnvoyees = $demandeAmitieRepository->findBy([
            'demandeur' => $utilisateurConnecte,
            'status' => 'acceptee',
        ]);

        // Amitiés acceptées ou l'utilisateur connecté
        // est celui qui avait reçu la demande
        $amitieRecues = $demandeAmitieRepository->findBy([
            'destinataire' => $utilisateurConnecte,
            'status' => 'acceptee',
        ]);

        // Tableau contenant les utilisateurs qui sont réellement amis
        $amis = [];

        // Si l'utilisateur avait envoyé la demande,
        // son ami est le destinataire
        foreach ($amitieEnvoyees as $amitie) {
            $amis[] = $amitie->getDestinataire();
        }

        // Si l'utilisateur avait reçu la demande,
        // son ami est le demandeur
        foreach ($amitieRecues as $amitie) {
            $amis[] = $amitie->getDemandeur();
        }

        // ==========================
        // IDENTIFIANTS DES AMIS
        // ==========================

        // Tableau contenant seulement les identifiants des amis
        // Il permet de ne pas proposer Ajouter en ami
        // a quelqu'un qui est deja ami
        $idsAmis = [];

        foreach ($amis as $ami) {
            $idsAmis[] = $ami->getId();
        }

        // ==========================
        // DEMANDES EN ATTENTE
        // ==========================

        // Tableau des identifiants des utilisateurs avec qui
        // une demande d'amitié est deja en attente
        $idsDemandesEnAttente = [];
        // Demandes en attente envoyées par l'utilisateur connecté
        $demandesEnvoyeesEnAttente = $demandeAmitieRepository->findBy([
            'demandeur' => $utilisateurConnecte,
            'status' => 'en_attente',
        ]);

        foreach ($demandesEnvoyeesEnAttente as $demande) {
            $idsDemandesEnAttente[] = $demande->getDestinataire()->getId();
        }
        // Demandes en attente reçues par l'utilisateur connecté
        $demandesRecuesEnAttente = $demandeAmitieRepository->findBy([
            'destinataire' => $utilisateurConnecte,
            'status' => 'en_attente',
        ]);

        foreach ($demandesRecuesEnAttente as $demande) {
            $idsDemandesEnAttente[] = $demande->getDemandeur()->getId();
        }

        // Envoi de toutes les informations
        return $this->render('amitie/index.html.twig', [
            'utilisateurs' => $utilisateurs,
            'utilisateurConnecte' => $utilisateurConnecte,
            'demandesRecues' => $demandesRecues,
            'amis' => $amis,
            'idsAmis' => $idsAmis,
            'idsDemandesEnAttente' => $idsDemandesEnAttente,
        ]);
    }



    // ==========================
    // ENVOYER UNE DEMANDE D'AMITIE
    // ==========================
    #[Route('/amis/ajouter/{id}', name: 'app_amis_ajouter')]
    public function ajouter(User $destinataire, EntityManagerInterface $entityManager, DemandeAmitieRepository $demandeAmitieRepository): Response
    {

        // Récuperation de l'utilisateur connecté
        $utilisateurConnecte = $this->getUser();

        // On ne peut pas s'ajouter soi-même
        if ($utilisateurConnecte === $destinataire) {
            return $this->redirectToRoute('app_amis');
        }

        // Vérifie si une demande existe deja dans le sens utilisateur connecté -> destinataire
        $demandeExistante = $demandeAmitieRepository->findOneBy([
            'demandeur' => $utilisateurConnecte,
            'destinataire' => $destinataire,
            'status' => 'en_attente',
        ]);

        // Vérifie aussi dans l'autre sens destinataire -> utilisateur connecté
        $demandeInverse = $demandeAmitieRepository->findOneBy([
            'demandeur' => $destinataire,
            'destinataire' => $utilisateurConnecte,
            'status' => 'en_attente',
        ]);

        // Vérifie si les deux utilisateurs sont deja amis
        $amitieExistante = $demandeAmitieRepository->findOneBy([
            'demandeur' => $utilisateurConnecte,
            'destinataire' => $destinataire,
            'status' => 'acceptee',
        ]);

        // Verifie egalement l'amitié dans le sens inverse
        $amitieInverse = $demandeAmitieRepository->findOneBy([
            'demandeur' => $destinataire,
            'destinataire' => $utilisateurConnecte,
            'status' => 'acceptee',
        ]);

        // Si une demande ou une amitié existe deja,
        // on ne crée pas de nouvelle demande

        if (
            $demandeExistante ||
            $demandeInverse ||
            $amitieExistante ||
            $amitieInverse
        ) {
            return $this->redirectToRoute('app_amis');
        }

        // ==========================
        // CRATION DE LA DEMANDE
        // ==========================
        $demande = new DemandeAmitie();

        $demande->setDemandeur($utilisateurConnecte);
        $demande->setDestinataire($destinataire);
        $demande->setStatus('en_attente');
        $demande->setDateDemande(new \DateTimeImmutable());
        // Doctrine prépare puis enregistre la nouvelle demande en base
        $entityManager->persist($demande);
        $entityManager->flush();

        return $this->redirectToRoute('app_amis');
    }

    // ==========================
    // ACCEPTER UNE DEMANDE
    // ==========================
    #[Route('/amis/accepter/{id}', name: 'app_amis_accepter')]
    public function accepter(DemandeAmitie $demande, EntityManagerInterface $entityManager): Response
    {

        // La demande passe de en_attente a accepter
        $demande->setStatus('acceptee');
        // L'objet existe déjà en base :
        // persist() n'est donc pas nécessaire
        $entityManager->flush();

        return $this->redirectToRoute('app_amis');
    }

    // ==========================
    // REFUSER UNE DEMANDE
    // ==========================
    #[Route('/amis/refuser/{id}', name: 'app_amis_refuser')]
    public function refuser(DemandeAmitie $demande, EntityManagerInterface $entityManager): Response
    {
        // La demande passe de en attente a refuser
        $demande->setStatus('refusee');
        // Enregistrement de la modification en base
        $entityManager->flush();

        return $this->redirectToRoute('app_amis');
    }
}
