<?php

namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Nom du jeu :',
                'attr' => ['placeholder' => 'Dans la peau de Louis Pasteur']
            ])
            ->add('address', TextareaType::class, [
                'required' => true,
                'label' => 'Adresse du jeu :',
                'attr' => ['placeholder' => '11 rue des camélias']
            ])
            ->add('postalCode', TextType::class, [
                'required' => true,
                'label' => 'Code postal :',
                'attr' => ['placeholder' => '59000']
            ])
            ->add('city', TextType::class, [
                'required' => true,
                'label' => 'Ville :',
                'attr' => ['placeholder' => 'Lille']
            ])
            ->add('image', FileType::class, [
                'required' => false,
                'mapped' => false,
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
                ],
            ])
            ->add('summary', TextareaType::class, [
                'required' => false,
                'label' => 'Description du jeu :',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
