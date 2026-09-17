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
    // afficher le fil d'actualité
    // ==========================
    #[Route('/fil-actualite', name: 'app_fil_actualite')]
    public function index(Request $request, PublicationRepository $publicationRepository, DemandeAmitieRepository $demandeAmitieRepository): Response
    {
        // recuperation de l'utilisateur connecté
        $utilisateurConnecte = $this->getUser();
        //recuperation du filtre présent dans l'URL
        // par défaut, on affiche toutes les publications autorisées
        $filtre = $request->query->get('filtre', 'toutes');
        // tableau qui contiendra les amis de l'utilisateur
        $amis = [];

        // ==========================
        // recuperartion des amis
        // ==========================

        // amitiés ou l'utilisateur connecté est demandeur
        $amitieEnvoyees = $demandeAmitieRepository->findBy([
            'demandeur' => $utilisateurConnecte,
            'status' => 'acceptee',
        ]);

        // dans ce cas, l'ami est le destinataire
        foreach ($amitieEnvoyees as $amitie) {
            $amis[] = $amitie->getDestinataire();
        }

        // amitiés accepter ou l'utilisateur connecté est destinataire
        $amitieRecues = $demandeAmitieRepository->findBy([
            'destinataire' => $utilisateurConnecte,
            'status' => 'acceptee',
        ]);

        foreach ($amitieRecues as $amitie) {
            $amis[] = $amitie->getDemandeur();
        }

        // ==========================
        // recuperation des publications
        // ==========================

        // toutes les publications, de la plus recente à la plus ancienne
        $toutesLesPublications = $publicationRepository->findBy(
            [],
            ['datePublication' => 'DESC']
        );
        // tableau final des publications
        $publications = [];

        // ==========================
        // visibillité et filtres
        // ==========================

        foreach ($toutesLesPublications as $publication) {
            // par defaut, la publication n'est pas visible
            $visible = false;

            // publication publique est visible
            if ($publication->getVisibilite() === 'publique') {
                $visible = true;
            }

            // publication réservée aux amis est visible
            // seulement si son auteur fait partie des amis
            if (
                $publication->getVisibilite() === 'amis'
                && in_array($publication->getAuteur(), $amis, true)
            ) {
                $visible = true;
            }

            // l'utilisateur voit toujours ses propres publications
            if ($publication->getAuteur() === $utilisateurConnecte) {
                $visible = true;
            }
            // si la publication n'est pas autorisée, on passe à la suivante
            if (!$visible) {
                continue;
            }

            // filtre publiques uniquement
            if (
                $filtre === 'publiques'
                && $publication->getVisibilite() !== 'publique'
            ) {
                continue;
            }

            // filtre amis uniquement
            if (
                $filtre === 'amis'
                && $publication->getVisibilite() !== 'amis'
            ) {
                continue;
            }
            // la publication respecte la visibilité et le filtre choisi
            $publications[] = $publication;
        }

        // affiche dans le fil d'actualité
        return $this->render('fil_actualite/index.html.twig', [
            'publications' => $publications,
            'filtre' => $filtre,
        ]);
    }
}
