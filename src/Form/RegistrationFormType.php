<?php

namespace App\Form;

use App\Entity\User;
use App\EventSubscriber\FormUserSubscriber;
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
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $current_user = $this->security->getUser();
        
        if ($current_user === null) {
            $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'E-mail : ',
                'attr' => ['placeholder' => 'Email']
            ]);
        }
            $builder->add('password', PasswordType::class, [
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
                        'minMessage' => 'Le mot de passe doit faire minimum {{ limit }} caract??res',
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
                'label' => 'Votre pr??nom : ',
                'attr' => ['placeholder' => 'Pr??nom']
            ]);
        if (($current_user !== null && $this->security->isGranted('ROLE_ORGANISATEUR')) || $current_user === null) {
            $builder->add('address', TextareaType::class, [
                'required' => false,
                'label' => 'Votre adresse :',
                'attr' => ['placeholder' => 'Adresse']
            ])
            ->add('postalCode', TextType::class, [
                'required' => false,
                'label' => 'Code postal :',
                'attr' => ['placeholder' => 'Code postal']
            ])
            ->add('city', TextType::class, [
                'required' => false,
                'label' => 'Votre ville :',
                'attr' => ['placeholder' => 'Ville']
            ]);
        }
        
        if ($current_user === null) {
            $builder->add('beOrganisateur', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Vous ??tes organisateur.',
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Vous devez accepter les conditions d\'utilisation.',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions d\'utilisation.',
                    ]),
                ],
            ]);
        }

        $builder->addEventSubscriber(new FormUserSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
