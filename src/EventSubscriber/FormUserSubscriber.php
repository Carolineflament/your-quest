<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FormUserSubscriber implements EventDispatcherInterface
{
    public static function getSubscribedEvents(): array
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event): void
    {
        $user = $event->getData();
        $form = $event->getForm();

        if($user && null !== $user->getId())
        {
            // Meuhh si on mapped à false on peut pas modifier le mot de passe 
            $form->add('password', PasswordType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => ['placeholder' => 'Laissez vide si inchangé']
            ]);
        }
        else
        {
            $form->add('password', PasswordType::class, [
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(),
                    new Regex(
                        "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/",
                        "Le mot de passe doit contenir au minimum 8 caractères, une majuscule, un chiffre et un caractère spécial"
                    ),
                ]
            ]);
        }
    }
}