<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

// ==========================
// REPOSITORY UTILISATEUR
// ==========================

// Permet les recherches sur les utilisateurs
// et participe à la gestion des mots de passe
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // Permet de mettre automatiquement a jour
    // le hash d'un mot de passe
    public function upgradePassword(
        PasswordAuthenticatedUserInterface $user,
        string $newHashedPassword
    ): void {
        // Vérifie que l'utilisateur reçu est bien une entity user
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $user::class
                )
            );
        }

        // Remplace l'ancien hash par le nouveau
        $user->setPassword($newHashedPassword);

        // Enregistre la modification dans la base de donnée
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
