<?php

namespace App\EventListener;

use App\Entity\Instance;
use App\Service\MySlugger;

class InstanceListener
{
    private $slugger;

    public function __construct(MySlugger $slugger)
    {
        $this->slugger = $slugger;    
    }

    public function createSlug(Instance $instance)
    {
        // calcul du slug
        $slug = $this->slugger->slugify($instance->getTitle(), Instance::class);
        // modification du slug dans l'entity
        $instance->setSlug($slug);

    }

    public function updateSlug(Instance $instance)
    {
        // calcul du slug
        $slug = $this->slugger->slugify($instance->getTitle(), Instance::class, $instance->getId());
        // modification du slug dans l'entity
        $instance->setSlug($slug);

    }
}