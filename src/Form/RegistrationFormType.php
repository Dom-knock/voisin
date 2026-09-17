<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RegistrationFormType extends AbstractType
{
    // ==========================
    // formulaire d'inscription
    // ==========================
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // champs concernant l'entity user
            ->add('email')
            ->add('pseudo', TextType::class)
            ->add('photo', FileType::class, [
                'mapped' => false,
            ])
            // la bio est facultative
            ->add('biographie', TextareaType::class, [
                'required' => false,
            ])
            // case obligatoire pour accepter les conditions
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                // la case doit obligatoirement être cochée
                'constraints' => [
                    new IsTrue(
                        message: 'J accepte les conditions.',
                    ),
                ],
            ])
            // champs pour le mot de passe
            ->add('plainPassword', PasswordType::class, [
                // le mot de passe en clair n'est jamais enregistré
                // directement dans l'entity user
                'mapped' => false,
                // on indique qu'il s'agit d'un nouveau mot de passe
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    //le champs ne peut pas etre vide
                    new NotBlank(
                        message: 'Veuillez entrer un mot de passe',
                    ),
                    // le mot de passe doit contenir au minimum six caracteres
                    new Length(
                        min: 6,
                        minMessage: 'Votre mot de passe doit avoir {{ limit }} caracteres',
                        //la limite max de caractères
                        max: 4096,
                    ),
                ],
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
