<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

class MySlugger
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * method slugify
     *
     * @param string $input
     * @return string
     */
    public function slugify(string $input): string
    {   
        return $this->slugger->slug($input)->lower();
    }
}