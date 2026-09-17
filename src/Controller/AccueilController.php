<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PublicationRepository;
use App\Repository\DemandeAmitieRepository;

final class AccueilController extends AbstractController
{
    // ==========================
    // afficher la page d'accueil
    // ==========================
    #[Route('/', name: 'app_accueil')]
    public function index(PublicationRepository $publicationRepository, DemandeAmitieRepository $demandeAmitieRepository): Response
    {
        // recuperation de l'utilisateur connecté
        // La valeur sera null si le visiteur n'est pas connecté.
        $utilisateurConnecte = $this->getUser();
        // tableau contenant les amis de l'utilisateur connecté
        $amis = [];

        // ==========================
        // recuperation des amis
        // ==========================

        // la recherche des amis est effectué uniquement
        // si un utilisateur est connecté
        if ($utilisateurConnecte) {

            // amitiés acceptées ou l'utilisateur
            // connecté avait envoyé la demande
            $amitieEnvoyees = $demandeAmitieRepository->findBy([
                'demandeur' => $utilisateurConnecte,
                'status' => 'acceptee',
            ]);
            // dans ce cas l'ami est le destinataire
            foreach ($amitieEnvoyees as $amitie) {
                $amis[] = $amitie->getDestinataire();
            }

            // amitiés acceptées ou l'utilisateur
            // connecté avait reçu la demande
            $amitieRecues = $demandeAmitieRepository->findBy([
                'destinataire' => $utilisateurConnecte,
                'status' => 'acceptee',
            ]);

            // dans ce cas, l'ami est le demandeur
            foreach ($amitieRecues as $amitie) {
                $amis[] = $amitie->getDemandeur();
            }
        }

        // ==========================
        // recuperation des publication
        // ==========================

        // recuperation de toutes les publications
        // de la plus récente a la plus ancienne
        $toutesLesPublications = $publicationRepository->findBy(
            [],
            ['datePublication' => 'DESC']
        );

        // tableau final contenant uniquement
        // les publications que le visiteur peut voir
        $publications = [];

        // ==========================
        // gestion de la visibillité
        // ==========================

        foreach ($toutesLesPublications as $publication) {

            // publication publique
            if ($publication->getVisibilite() === 'publique') {
                $publications[] = $publication;
            }

            // publication de l'utilisateur connecté
            if (
                $utilisateurConnecte &&
                $publication->getAuteur() === $utilisateurConnecte &&
                !in_array($publication, $publications, true)
            ) {
                $publications[] = $publication;
            }

            // publication reservée aux amis
            if (
                $utilisateurConnecte &&
                $publication->getVisibilite() === 'amis' &&
                in_array($publication->getAuteur(), $amis, true)
            ) {
                $publications[] = $publication;
            }
        }
        //envoie des publication et retour sur la page d'accueil
        return $this->render('accueil/index.html.twig', [
            'publications' => $publications,
        ]);
    }
}
