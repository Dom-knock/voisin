<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ProfilType extends AbstractType
{
    // ==========================
    // formulaire du profil
    // ==========================
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // champs concernant les propriétés de l'utlisateur
            ->add('pseudo')
            ->add('biographie')
            // champs permettant de modifier la photo de profil
            ->add('photo', FileType::class, [
                'mapped' => false,
                'required' => false,
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        // ce formulaire travaille avec l'entity user
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
