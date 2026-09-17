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
    // inscription d'un utilisateur
    // ==========================
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        // creation d'un nouvel utilisateur
        $user = new User();
        // la date d'inscription est ajoutée automatiquement
        $user->setDateInscription(new \DateTimeImmutable());
        // creation du formulaire d'inscription lié au nouvel utilisateur
        $form = $this->createForm(RegistrationFormType::class, $user);
        // recuperation des données envoyées par le formulaire
        $form->handleRequest($request);

        // traitement uniquement si le formulaire est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // recuperation du mot de passe saisi en clair dans le formulaire
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Le mot de passe est hashé avant d'être enregistré dans l'utilisateur
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // ==========================
            // Pphoto de profil
            // ==========================

            // recuperation de la photo envoyée dans le formulaire
            $photo = $form->get('photo')->getData();

            if ($photo) {
                // creation d'un nom unique pour éviter les problemes entre fichiers
                $nomPhoto = uniqid() . '.' . $photo->guessExtension();

                // déplacement de la photo dans le dossier des profils
                $photo->move(
                    $this->getParameter('profils_directory'),
                    $nomPhoto
                );
                // seul le nom du fichier est enregistré dans la base
                $user->setPhoto($nomPhoto);
            }

            // ==========================
            // enregistrement
            // ==========================

            // persist indique qu'il s'agit d'une nouvelle entité
            $entityManager->persist($user);
            // flush enregistre l'utilisateur dans la base
            $entityManager->flush();

            // ==========================
            // connexion automatique
            // ==========================

            // après l'inscription, l'utilisateur est automatiquement connecté
            return $security->login($user, LoginFormAuthenticator::class, 'main');
        }
        // affichage du formulaire d'inscription
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
