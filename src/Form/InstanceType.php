<?php

namespace App\Form;

use App\Entity\Instance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Nom de votre instance :',
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Description de votre instance :',
            ])
            ->add('startAt', DateTimeType::class, [
                // renders it as a single text box
                'widget' => 'single_text',
                'label' => 'Cette instance débute le :',
                'input' => 'datetime_immutable',
            ])
            ->add('endAt', DateTimeType::class, [
                // renders it as a single text box
                'widget' => 'single_text',
                'label' => 'Cette instance se termine le :',
                'input' => 'datetime_immutable',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Instance::class,
        ]);
    }
}
