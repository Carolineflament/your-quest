<?php

namespace App\Form;

use App\Entity\Checkpoint;
use App\Entity\Game;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CheckpointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Nom de votre checkpoint :',
            ])
            ->add('successMessage', TextareaType::class, [
                'label' => 'Message de félicitation :',
            ])
            ->add('successImage', FileType::class, [
                'required' => false,
                'label' => 'Image prochain checkpoint :',
                'mapped' => false,
            ])
            ->add('orderCheckpoint', NumberType::class, [
                'label' => 'Ordre du checkpoint :',
                'html5' => true,
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
