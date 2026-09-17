<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Form\PublicationType;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\DemandeAmitieRepository;

#[Route('/publication')]
#[IsGranted('ROLE_USER')]
final class PublicationController extends AbstractController
{
    // ==========================
    // afficher les publication
    // ==========================

    #[Route(name: 'app_publication_index', methods: ['GET'])]
    public function index(
        PublicationRepository $publicationRepository,
        DemandeAmitieRepository $demandeAmitieRepository
    ): Response {
        // recuperation de l'utilisateur connecté
        $utilisateurConnecte = $this->getUser();

        // On recupere les amitiés acceptées ou l'utilisateur est demandeur
        $demandesEnvoyees = $demandeAmitieRepository->findBy([
            'demandeur' => $utilisateurConnecte,
            'status' => 'acceptee',
        ]);

        // On recupere les amitiés acceptées ou l'utilisateur est destinataire
        $demandesRecues = $demandeAmitieRepository->findBy([
            'destinataire' => $utilisateurConnecte,
            'status' => 'acceptee',
        ]);

        // construction de la liste des amis
        $amis = [];

        foreach ($demandesEnvoyees as $demande) {
            $amis[] = $demande->getDestinataire();
        }

        foreach ($demandesRecues as $demande) {
            $amis[] = $demande->getDemandeur();
        }

        // toutes les publications classées de la plus récente à la plus ancienne
        $toutesLesPublications = $publicationRepository->findBy(
            [],
            ['datePublication' => 'DESC']
        );

        $publications = [];

        // filtrage des publications selon leur visibilité
        foreach ($toutesLesPublications as $publication) {

            // Une publication publique est visible
            if ($publication->getVisibilite() === 'publique') {
                $publications[] = $publication;
            }

            // une publication amis est visible si son auteur est un ami
            if (
                $publication->getVisibilite() === 'amis'
                && in_array($publication->getAuteur(), $amis, true)
            ) {
                $publications[] = $publication;
            }

            // l'utilisateur voit toujours ses propres publications
            if (
                $publication->getAuteur() === $utilisateurConnecte
                && !in_array($publication, $publications, true)
            ) {
                $publications[] = $publication;
            }
        }

        return $this->render('publication/index.html.twig', [
            'publications' => $publications,
        ]);
    }

    // ==========================
    // créer une publication
    // ==========================

    #[Route('/new', name: 'app_publication_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // creation d'un nouvel objet Publication
        $publication = new Publication();

        // creation du formulaire lié à la publication
        $form = $this->createForm(PublicationType::class, $publication);
        // recuperation des données envoyées par le formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // on recupère l'utilisateur connecté comme auteur
            $publication->setAuteur($this->getUser());

            // On ajoute automatiquement la date de création
            $publication->setDatePublication(new \DateTimeImmutable());

            // on recupère l'image envoyée dans le formulaire
            $image = $form->get('image')->getData();

            if ($image) {

                // on crée un nom unique pour éviter d'écraser une autre image
                $nomImage = uniqid() . '.' . $image->guessExtension();

                // on déplace l'image dans public/uploads/publications
                $image->move(
                    $this->getParameter('publications_directory'),
                    $nomImage
                );

                // on enregistre uniquement le nom du fichier dans la BDD
                $publication->setImage($nomImage);
            }
            // persist prépare la nouvelle publication pour Doctrine
            $entityManager->persist($publication);
            // flush enregistre réellement les modifications dans la BDD
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_publication_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('publication/new.html.twig', [
            'publication' => $publication,
            'form' => $form,
        ]);
    }

    // ==========================
    // voir une publication
    // ==========================
    #[Route('/{id}', name: 'app_publication_show', methods: ['GET'])]
    public function show(Publication $publication): Response
    {
        return $this->render('publication/show.html.twig', [
            'publication' => $publication,
        ]);
    }

    // ==========================
    // modifier une publication
    // ==========================
    #[Route('/{id}/edit', name: 'app_publication_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        // seul l'auteur peut modifier sa publication
        if ($publication->getAuteur() !== $this->getUser()) {
            throw $this->createAccessDeniedException(
                'Vous ne pouvez pas modifier cette publication.'
            );
        }
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);
        // le formulaire est rempli avec les données actuelles de la publication
        if ($form->isSubmitted() && $form->isValid()) {

            // on recupère la nouvelle image choisie dans le formulaire
            $image = $form->get('image')->getData();

            // si une nouvelle image a été choisie
            if ($image) {

                // on crée un nom unique pour l'image
                $nomImage = uniqid() . '.' . $image->guessExtension();

                // on déplace l'image dans le dossier des publications
                $image->move(
                    $this->getParameter('publications_directory'),
                    $nomImage
                );

                // on remplace le nom de l'ancienne image dans la BDD
                $publication->setImage($nomImage);
            }

            // l'objet existe deja pas besoin de persist flush suffit
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_publication_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('publication/edit.html.twig', [
            'publication' => $publication,
            'form' => $form,
        ]);
    }
    // ==========================
    // supprimer un publication
    // ==========================
    #[Route('/{id}', name: 'app_publication_delete', methods: ['POST'])]
    public function delete(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        // seul l'auteur peut supprimer sa publication
        if ($publication->getAuteur() !== $this->getUser()) {
            throw $this->createAccessDeniedException(
                'Vous ne pouvez pas supprimer cette publication.'
            );
        }
        // verification du token CSRF avant la suppression
        if ($this->isCsrfTokenValid('delete' . $publication->getId(), $request->getPayload()->getString('_token'))) {
            // suppression de la publication
            $entityManager->remove($publication);
            // mise a jour de la BDD
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_publication_index', [], Response::HTTP_SEE_OTHER);
    }
}
