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
use Symfony\Component\Validator\Constraints\File;

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
