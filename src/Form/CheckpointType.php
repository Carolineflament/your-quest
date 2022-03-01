<?php

namespace App\Form;

use App\Entity\Checkpoint;
use App\Entity\Game;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckpointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Nom de votre checkpoint :',
            ])
            ->add('successMessage', TextType::class, [
                'label' => 'Message de félicitation:',
            ])
            ->add('orderCheckpoint', TextType::class, [
                'label' => 'Ordre du checkpoint :',
            ])
            ->add('createdAt', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Ce checkpoint est créé le :',
                'input' => 'datetime_immutable',
            ])
            ->add('game', EntityType::class, [
                'class' => Game::class,
                'label' => 'Nom du jeu',
                'choice_label' => 'slug',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Checkpoint::class,
        ]);
    }
}
