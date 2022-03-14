<?php

namespace App\Form;

use App\Entity\Enigma;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnigmaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextareaType::class,[
                'label' => 'Votre énigme:',
                'attr' => ['placeholder' => 'Quel est la couleur du cheval blanc de Henry IV ?'],
            ])
            ->add('orderEnigma', NumberType::class,[
                'label' => 'Ordre de l\' énigme :',
                'html5' => true,
                // TODO The 3 next line need to be delete if we have more than 1 enigma
                'data' => 1,
                'attr' => ['style' => 'display:none'],
                'label_attr' => ['style' => 'display:none'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Enigma::class,
        ]);
    }
}
