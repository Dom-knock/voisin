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
    // AFFICHER LA PAGE D'ACCUEIL
    // ==========================
    #[Route('/', name: 'app_accueil')]
    public function index(PublicationRepository $publicationRepository, DemandeAmitieRepository $demandeAmitieRepository): Response
    {
        // Récupération de l'utilisateur connecté.
        // La valeur sera null si le visiteur n'est pas connecté.
        $utilisateurConnecte = $this->getUser();
        // Tableau contenant les amis de l'utilisateur connecté
        $amis = [];

        // ==========================
        // RECUPERATON DES AMIS
        // ==========================

        // La recherche des amis est effectué uniquement
        // si un utilisateur est connecté
        if ($utilisateurConnecte) {

            // Amitiés acceptées où l'utilisateur
            // connecté avait envoyé la demande
            $amitieEnvoyees = $demandeAmitieRepository->findBy([
                'demandeur' => $utilisateurConnecte,
                'status' => 'acceptee',
            ]);
            // Dans ce cas l'ami est le destinataire
            foreach ($amitieEnvoyees as $amitie) {
                $amis[] = $amitie->getDestinataire();
            }

            // Amitiés acceptées où l'utilisateur
            // connecté avait reçu la demande
            $amitieRecues = $demandeAmitieRepository->findBy([
                'destinataire' => $utilisateurConnecte,
                'status' => 'acceptee',
            ]);

            // Dans ce cas, l'ami est le demandeur
            foreach ($amitieRecues as $amitie) {
                $amis[] = $amitie->getDemandeur();
            }
        }

        // ==========================
        // RECUPERATION DES PUBLICATIONS
        // ==========================

        // Recuperation de toutes les publications
        // de la plus récente a la plus ancienne
        $toutesLesPublications = $publicationRepository->findBy(
            [],
            ['datePublication' => 'DESC']
        );

        // Tableau final contenant uniquement
        // les publications que le visiteur peut voir
        $publications = [];

        // ==========================
        // GESTION DE LA VISIBILITE
        // ==========================

        foreach ($toutesLesPublications as $publication) {

            // Publication publique
            if ($publication->getVisibilite() === 'publique') {
                $publications[] = $publication;
            }

            // Publication de l'utilisateur connecté
            if (
                $utilisateurConnecte &&
                $publication->getAuteur() === $utilisateurConnecte &&
                !in_array($publication, $publications, true)
            ) {
                $publications[] = $publication;
            }

            // Publication réservée aux amis
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
