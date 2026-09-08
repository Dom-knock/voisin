<?php

namespace App\Form;

use App\Entity\Publication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PublicationType extends AbstractType
{
    // ==========================
    // FORMULAIRE PUBLICATION
    // ==========================
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //Champs pour le texte de la publication
            ->add('texte')
            // Champs permettant l'envoie d'une image
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false,
            ])
            // Liste des choix pour la visibilité
            ->add('visibilite', ChoiceType::class, [
                'choices' => [
                    'Publique' => 'publique',
                    'Amis uniquement' => 'amis',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Ce formulaire travaille avec l'entity publication
        $resolver->setDefaults([
            'data_class' => Publication::class,
        ]);
    }
}
