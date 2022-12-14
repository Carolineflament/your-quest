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

    public function updateSlug(Instance $instance)
    {
        // calcul du slug
        $slug = $this->slugger->slugify($instance->getTitle());
        // modification du slug dans l'entity
        $instance->setSlug($slug);

    }
}