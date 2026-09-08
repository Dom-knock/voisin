<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Repository\PublicationRepository;
use App\Repository\DemandeAmitieRepository;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProfilController extends AbstractController
{    // ==========================
    // AFFICHER UN PROFIL
    // ==========================

    #[Route('/profil/{id}', name: 'app_profil', requirements: ['id' => '\d+'])]
    public function index(User $utilisateur, PublicationRepository $publicationRepository, DemandeAmitieRepository $demandeAmitieRepository): Response
    {

        // Récupération de l'utilisateur actuellement connecté
        // peut etre null si le visiteur n'est pas connecté
        $utilisateurConnecte = $this->getUser();

        // ==========================
        // PUBLICATIONS DU PROFIL
        // ==========================

        // Récupération de toutes les publications de l'utilisateur
        // de la plus récente à la plus ancienne
        $toutesLesPublications = $publicationRepository->findBy(
            ['auteur' => $utilisateur],
            ['datePublication' => 'DESC']
        );

        // Tableau qui contiendra uniquement les publications
        // que le visiteur a le droit de voir
        $publications = [];

        foreach ($toutesLesPublications as $publication) {

            // Publication publique
            if ($publication->getVisibilite() === 'publique') {
                $publications[] = $publication;
            }

            // L'utilisateur consulte son propre profil il peut voir ses publications
            if (
                $utilisateurConnecte &&
                $utilisateur === $utilisateurConnecte &&
                !in_array($publication, $publications, true)
            ) {
                $publications[] = $publication;
            }

            // Pour une publication réservée aux amis
            // on doit vérifier qu'une amitié existe
            if (
                $utilisateurConnecte &&
                $publication->getVisibilite() === 'amis'
            ) {
                // Vérification de l'amitié
                $amitie = $demandeAmitieRepository->findOneBy([
                    'demandeur' => $utilisateurConnecte,
                    'destinataire' => $utilisateur,
                    'status' => 'acceptee',
                ]);
                // Vérification également dans le sens inverse
                $amitieInverse = $demandeAmitieRepository->findOneBy([
                    'demandeur' => $utilisateur,
                    'destinataire' => $utilisateurConnecte,
                    'status' => 'acceptee',
                ]);
                // Si une amitié existe dans l'un des deux sens
                // la publication réservé aux amis peut etre affichée
                if ($amitie || $amitieInverse) {
                    $publications[] = $publication;
                }
            }
        }

        // ==========================
        // LISTE DES AMIS DU PROFIL
        // ==========================
        $amis = [];

        // Recherche des amitiés acceptées où l'utilisateur
        // du profil avait envoyé la demande
        $amitieEnvoyees = $demandeAmitieRepository->findBy([
            'demandeur' => $utilisateur,
            'status' => 'acceptee',
        ]);

        // Dans ce cas, l'ami est le destinataire
        foreach ($amitieEnvoyees as $amitie) {
            $amis[] = $amitie->getDestinataire();
        }

        // Recherche des amitiés acceptées où l'utilisateur
        // du profil avait reçu la demande
        $amitieRecues = $demandeAmitieRepository->findBy([
            'destinataire' => $utilisateur,
            'status' => 'acceptee',
        ]);

        // Dans ce cas, l'ami est le demandeur
        foreach ($amitieRecues as $amitie) {
            $amis[] = $amitie->getDemandeur();
        }

        return $this->render('profil/index.html.twig', [
            'utilisateur' => $utilisateur,
            'publications' => $publications,
            'amis' => $amis,
        ]);
    }

    // ==========================
    // MODIFIER SON PROFIL
    // ==========================

    // Cette page est réservée aux utilisateurs connectés
    #[Route('/profil/modifier', name: 'app_profil_modifier')]
    // Cette page est réservée aux utilisateurs connectés
    #[IsGranted('ROLE_USER')]
    public function modifier(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'utilisateur connecté
        /** @var User $utilisateur */
        $utilisateur = $this->getUser();

        // Création du formulaire ProfilType à partir
        // des informations actuelles de l'utilisateur
        $form = $this->createForm(ProfilType::class, $utilisateur);
        // On récupère les données envoyées par le formulaire
        $form->handleRequest($request);

        // On traite le formulaire uniquement s'il a été envoyé
        // et si toutes les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération de la nouvelle photo
            $photo = $form->get('photo')->getData();
            // Une nouvelle photo n'est pas obligatoire
            if ($photo) {

                $nomPhoto = uniqid() . '.' . $photo->guessExtension();

                // Deplacement de l'image dans le dossier
                // configuré pour les photos de profil
                $photo->move(
                    $this->getParameter('profils_directory'),
                    $nomPhoto
                );
                // Enregistrement du nom du fichier dans l'utilisateur
                $utilisateur->setPhoto($nomPhoto);
            }
            // L'utilisateur existe déjà en base de données.
            // persist() n'est donc pas nécessaire.
            $entityManager->flush();

            // Retour vers le profil de l'utilisateur après modification
            return $this->redirectToRoute('app_profil', [
                'id' => $utilisateur->getId(),
            ]);
        }

        // Affichage du formulaire de modification
        return $this->render('profil/modifier.html.twig', [
            'form' => $form,
        ]);
    }
}
