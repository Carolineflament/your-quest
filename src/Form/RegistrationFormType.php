<?php

namespace App\Form;

use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'E-mail : ',
                'attr' => ['placeholder' => 'Email']
            ])
            ->add('password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => 'Votre mot de passe : ',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit faire minimum {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'attr' => ['autocomplete' => 'new-password', 'placeholder' => 'Mot de passe'],
            ])
            ->add('username', TextType::class, [
                'required' => true,
                'label' => 'Votre pseudo : ',
                'attr' => ['placeholder' => 'Pseudo']
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
                'label' => 'Votre nom : ',
                'attr' => ['placeholder' => 'Nom']
            ])
            ->add('firstname', TextType::class, [
                'required' => true,
                'label' => 'Votre prénom : ',
                'attr' => ['placeholder' => 'Prénom']
            ])
            ->add('address', TextareaType::class, [
                'required' => false,
                'label' => 'Votre adresse :',
                'attr' => ['placeholder' => 'Adresse']
            ])
            ->add('postal_code', NumberType::class, [
                'required' => false,
                'label' => 'Code postal :',
                'html5' => true,
                'attr' => ['placeholder' => 'Code postal']
            ])
            ->add('city', TextType::class, [
                'required' => false,
                'label' => 'Votre ville :',
                'attr' => ['placeholder' => 'Ville']
            ])
            ->add('beOrganisateur', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Vous êtes organisateur.',
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Vous devez accepter les conditions d\'utilisation.',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions d\'utilisation.',
                    ]),
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            // On récupère le form depuis l'event (pour travailler avec)
            $form = $event->getForm();
            // On récupère le user mappé sur le form depuis l'event
            $user = $event->getData();

            // On conditionne le champ "password"
            // Si user existant, il a id non null
            if ($user->getId() !== null) {
                // Edit
                $form->add('password', PasswordType::class, [
                    // Pour le form d'édition, on n'associe pas le password à l'entité
                    // @link https://symfony.com/doc/current/reference/forms/types/form.html#mapped
                    'mapped' => false,
                    'attr' => [
                        'placeholder' => 'Laissez vide si inchangé'
                    ]
                ]);
            } else {
                // New
                $form->add('password', PasswordType::class, [
                    // En cas d'erreur du type
                    // Expected argument of type "string", "null" given at property path "password".
                    // (notamment à l'edit en cas de passage d'une valeur existante à vide)
                    'empty_data' => '',
                    'mapped' => false,
                    'label' => 'Votre mot de passe : ',
                    
                    'attr' => ['autocomplete' => 'new-password', 'placeholder' => 'Mot de passe'],
                    // On déplace les contraintes de l'entité vers le form d'ajout
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez renseigner un mot de passe',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Le mot de passe doit faire minimum {{ limit }} caractères',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                        /*new Regex(
                            "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/",
                            "Le mot de passe doit contenir au minimum 8 caractères, une majuscule, un chiffre et un caractère spécial"
                        ),*/
                    ],
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
