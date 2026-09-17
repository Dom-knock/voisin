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
    // afficher un profil
    // ==========================

    #[Route('/profil/{id}', name: 'app_profil', requirements: ['id' => '\d+'])]
    public function index(User $utilisateur, PublicationRepository $publicationRepository, DemandeAmitieRepository $demandeAmitieRepository): Response
    {

        // recuperation de l'utilisateur actuellement connecté
        // peut etre null si le visiteur n'est pas connecté
        $utilisateurConnecte = $this->getUser();

        // ==========================
        // publication du profil
        // ==========================

        // recuperation de toutes les publications de l'utilisateur
        // de la plus récente à la plus ancienne
        $toutesLesPublications = $publicationRepository->findBy(
            ['auteur' => $utilisateur],
            ['datePublication' => 'DESC']
        );

        // tableau qui contiendra uniquement les publications
        // que le visiteur a le droit de voir
        $publications = [];

        foreach ($toutesLesPublications as $publication) {

            // publication publique
            if ($publication->getVisibilite() === 'publique') {
                $publications[] = $publication;
            }

            // l'utilisateur consulte son propre profil il peut voir ses publications
            if (
                $utilisateurConnecte &&
                $utilisateur === $utilisateurConnecte &&
                !in_array($publication, $publications, true)
            ) {
                $publications[] = $publication;
            }

            // pour une publication réservée aux amis
            // on doit verifier qu'une amitié existe
            if (
                $utilisateurConnecte &&
                $publication->getVisibilite() === 'amis'
            ) {
                // verification de l'amitié
                $amitie = $demandeAmitieRepository->findOneBy([
                    'demandeur' => $utilisateurConnecte,
                    'destinataire' => $utilisateur,
                    'status' => 'acceptee',
                ]);
                // verification également dans le sens inverse
                $amitieInverse = $demandeAmitieRepository->findOneBy([
                    'demandeur' => $utilisateur,
                    'destinataire' => $utilisateurConnecte,
                    'status' => 'acceptee',
                ]);
                // si une amitié existe dans l'un des deux sens
                // la publication réservé aux amis peut etre affichée
                if ($amitie || $amitieInverse) {
                    $publications[] = $publication;
                }
            }
        }

        // ==========================
        // liste des amis du profil
        // ==========================
        $amis = [];

        // recherche des amitiés acceptées ou l'utilisateur
        // du profil avait envoyé la demande
        $amitieEnvoyees = $demandeAmitieRepository->findBy([
            'demandeur' => $utilisateur,
            'status' => 'acceptee',
        ]);

        // dans ce cas, l'ami est le destinataire
        foreach ($amitieEnvoyees as $amitie) {
            $amis[] = $amitie->getDestinataire();
        }

        // recherche des amitiés acceptées où l'utilisateur
        // du profil avait reçu la demande
        $amitieRecues = $demandeAmitieRepository->findBy([
            'destinataire' => $utilisateur,
            'status' => 'acceptee',
        ]);

        // dans ce cas, l'ami est le demandeur
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
    // modifier son profil
    // ==========================

    // cette page est réservée aux utilisateurs connectés
    #[Route('/profil/modifier', name: 'app_profil_modifier')]
    // cette page est réservée aux utilisateurs connectés
    #[IsGranted('ROLE_USER')]
    public function modifier(Request $request, EntityManagerInterface $entityManager): Response
    {
        // recuperation de l'utilisateur connecté
        /** @var User $utilisateur */
        $utilisateur = $this->getUser();

        // creation du formulaire ProfilType à partir
        // des informations actuelles de l'utilisateur
        $form = $this->createForm(ProfilType::class, $utilisateur);
        // on recupere les données envoyées par le formulaire
        $form->handleRequest($request);

        // on traite le formulaire uniquement s'il a été envoyé
        // et si toutes les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
            // recupeartion de la nouvelle photo
            $photo = $form->get('photo')->getData();
            // une nouvelle photo n'est pas obligatoire
            if ($photo) {

                $nomPhoto = uniqid() . '.' . $photo->guessExtension();

                // deplacement de l'image dans le dossier
                // configuré pour les photos de profil
                $photo->move(
                    $this->getParameter('profils_directory'),
                    $nomPhoto
                );
                // enregistrement du nom du fichier dans l'utilisateur
                $utilisateur->setPhoto($nomPhoto);
            }
            // l'utilisateur existe deja en base de données.
            // persist() n'est donc pas nécessaire.
            $entityManager->flush();

            // retour vers le profil de l'utilisateur après modification
            return $this->redirectToRoute('app_profil', [
                'id' => $utilisateur->getId(),
            ]);
        }

        // affichage du formulaire de modification
        return $this->render('profil/modifier.html.twig', [
            'form' => $form,
        ]);
    }
}
