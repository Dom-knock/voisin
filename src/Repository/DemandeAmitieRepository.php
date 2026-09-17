<?php

namespace App\Repository;

use App\Entity\DemandeAmitie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

// Repository permettant d'effectuer des recherches
// dans la table des demandes d'amitié
class DemandeAmitieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeAmitie::class);
    }
}
