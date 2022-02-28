<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

class MySlugger
{
    private $slugger;
    private $toLower;

    public function __construct(SluggerInterface $slugger, bool $toLower)
    {
        $this->slugger = $slugger;
        $this->toLower = $toLower;
    }

    /**
     * method slugify
     *
     * @param string $input
     * @return string
     */
    public function slugify(string $input): string
    {
        if ($this->toLower) {
            $slug = $this->slugger->slug($input)->lower();
        }
        else {
            $slug = $this->slugger->slug($input);
        }
        
        return $slug;
    }
}