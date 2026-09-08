<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    // ==========================
    // INSCRIPTION D'UN UTILISATEUR
    // ==========================
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        // Création d'un nouvel utilisateur
        $user = new User();
        // La date d'inscription est ajoutée automatiquement
        $user->setDateInscription(new \DateTimeImmutable());
        // Création du formulaire d'inscription lié au nouvel utilisateur
        $form = $this->createForm(RegistrationFormType::class, $user);
        // Récupération des données envoyées par le formulaire
        $form->handleRequest($request);

        // Traitement uniquement si le formulaire est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du mot de passe saisi en clair dans le formulaire
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Le mot de passe est hashé avant d'être enregistré dans l'utilisateur
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // ==========================
            // PHOTO DE PROFIL
            // ==========================

            // Récupération de la photo envoyée dans le formulaire
            $photo = $form->get('photo')->getData();

            if ($photo) {
                // Création d'un nom unique pour éviter les problemes entre fichiers
                $nomPhoto = uniqid() . '.' . $photo->guessExtension();

                // Déplacement de la photo dans le dossier des profils
                $photo->move(
                    $this->getParameter('profils_directory'),
                    $nomPhoto
                );
                // Seul le nom du fichier est enregistré dans la base
                $user->setPhoto($nomPhoto);
            }

            // ==========================
            // ENREGISTREMENT
            // ==========================

            // persist indique qu'il s'agit d'une nouvelle entité
            $entityManager->persist($user);
            // flush enregistre l'utilisateur dans la base
            $entityManager->flush();

            // ==========================
            // CONNEXION AUTOMATIQUE
            // ==========================

            // Après l'inscription, l'utilisateur est automatiquement connecté
            return $security->login($user, LoginFormAuthenticator::class, 'main');
        }
        // Affichage du formulaire d'inscription
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
