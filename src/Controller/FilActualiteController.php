<?php

namespace App\Controller;

use App\Repository\PublicationRepository;
use App\Repository\DemandeAmitieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

// Seuls les utilisateurs connectés peuvent accéder au fil d'actualité
#[IsGranted('ROLE_USER')]
final class FilActualiteController extends AbstractController
{
    // ==========================
    // AFFICHER LE FIL D'ACTUALITE
    // ==========================
    #[Route('/fil-actualite', name: 'app_fil_actualite')]
    public function index(Request $request, PublicationRepository $publicationRepository, DemandeAmitieRepository $demandeAmitieRepository): Response
    {
        // Récupération de l'utilisateur connecté
        $utilisateurConnecte = $this->getUser();
        // Récupération du filtre présent dans l'URL
        // Par défaut, on affiche toutes les publications autorisées
        $filtre = $request->query->get('filtre', 'toutes');
        // Tableau qui contiendra les amis de l'utilisateur
        $amis = [];

        // ==========================
        // RECUPERATION DES AMIS
        // ==========================

        // Amitiés ou l'utilisateur connecté est demandeur
        $amitieEnvoyees = $demandeAmitieRepository->findBy([
            'demandeur' => $utilisateurConnecte,
            'status' => 'acceptee',
        ]);

        // Dans ce cas, l'ami est le destinataire
        foreach ($amitieEnvoyees as $amitie) {
            $amis[] = $amitie->getDestinataire();
        }

        // Amitiés accepter ou l'utilisateur connecté est destinataire
        $amitieRecues = $demandeAmitieRepository->findBy([
            'destinataire' => $utilisateurConnecte,
            'status' => 'acceptee',
        ]);

        foreach ($amitieRecues as $amitie) {
            $amis[] = $amitie->getDemandeur();
        }

        // ==========================
        // RECUPERATION DES PUBLICATIONS
        // ==========================

        // Toutes les publications, de la plus récente à la plus ancienne
        $toutesLesPublications = $publicationRepository->findBy(
            [],
            ['datePublication' => 'DESC']
        );
        // Tableau final des publications
        $publications = [];

        // ==========================
        // VISIBILITE ET FILTRES
        // ==========================

        foreach ($toutesLesPublications as $publication) {
            // Par défaut, la publication n'est pas visible
            $visible = false;

            // Publication publique est visible
            if ($publication->getVisibilite() === 'publique') {
                $visible = true;
            }

            // Publication réservée aux amis est visible
            // seulement si son auteur fait partie des amis
            if (
                $publication->getVisibilite() === 'amis'
                && in_array($publication->getAuteur(), $amis, true)
            ) {
                $visible = true;
            }

            // // L'utilisateur voit toujours ses propres publications
            if ($publication->getAuteur() === $utilisateurConnecte) {
                $visible = true;
            }
            // Si la publication n'est pas autorisée, on passe à la suivante
            if (!$visible) {
                continue;
            }

            // Filtre publiques uniquement
            if (
                $filtre === 'publiques'
                && $publication->getVisibilite() !== 'publique'
            ) {
                continue;
            }

            // Filtre amis uniquement
            if (
                $filtre === 'amis'
                && $publication->getVisibilite() !== 'amis'
            ) {
                continue;
            }
            // La publication respecte la visibilité et le filtre choisi
            $publications[] = $publication;
        }

        // Affiche dans le fil d'actualité
        return $this->render('fil_actualite/index.html.twig', [
            'publications' => $publications,
            'filtre' => $filtre,
        ]);
    }
}
