<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\EventSubscriber\FormUserSubscriber;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Votre photo',
                'constraints' => [
                    new File([
                        'maxSize' => '512k',
                        'maxSizeMessage' => 'Le poids maximum de l\'image ne doit pas éxéder 512 Ko',
                        'mimeTypes' => [
                            'image/gif',
                            'image/png',
                            'image/webp',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'L\'image doit être au format PNG, GIF, JPG ou WEBP',
                    ])
                ]
            ])
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
                'label' => 'Mot de passe : ',
                'attr' => ['autocomplete' => 'new-password', 'placeholder' => 'Mot de passe'],
            ])
            ->add('pseudo', TextType::class, [
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
            ->add('postalCode', TextType::class, [
                'required' => false,
                'label' => 'Code postal :',
                'attr' => ['placeholder' => 'Code postal']
            ])
            ->add('city', TextType::class, [
                'required' => false,
                'label' => 'Votre ville :',
                'attr' => ['placeholder' => 'Ville']
            ])
            
        ;
        
        $builder->addEventSubscriber(new FormUserSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
