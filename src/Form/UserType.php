<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'E-mail : ',
                'attr' => ['placeholder' => 'Email']
            ])
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'label' => 'Rôle :',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                        ->orderBy('r.name', 'ASC');
                },
            ])
            ->add('password', PasswordType::class, [
                
                'label' => 'Votre mot de passe : ',
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
            ->add('postalCode', NumberType::class, [
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
            
        ;
        
        //TODO faire un event listener car meme dans les 2 form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();
            $user = $event->getData();

            if ($user->getId() !== null) {
                // Edit
                $form->add('password', PasswordType::class, [
                    'required' => false,
                    'label' => 'Votre mot de passe : Laissez vide si inchangé',
                    'mapped' => false,
                    'attr' => [
                        'placeholder' => 'Laissez vide si inchangé'
                    ]
                ]);
            } else {
                // New
                $form->add('password', PasswordType::class, [
                    'empty_data' => '',
                    'mapped' => true,
                    'label' => 'Votre mot de passe : ',

                    'attr' => ['autocomplete' => 'new-password', 'placeholder' => 'Mot de passe'],
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
