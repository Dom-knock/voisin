<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // ==========================
    // CONNEXION
    // ==========================
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupération d'une éventuelle erreur de connexion
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupération du dernier identifiant saisi
        // pour pouvoir le réafficher dans le formulaire
        $lastUsername = $authenticationUtils->getLastUsername();
        // Envoi des informations au formulaire de connexion
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    // ==========================
    // DÉCONNEXION
    // ==========================
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // La deconnexion est intercepté automatiquement
        // par le firewall configuré dans security.yaml
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
