<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;

class MySlugger
{
    private $slugger;
    private $doctrine;

    public function __construct(SluggerInterface $slugger, ManagerRegistry $doctrine)
    {
        $this->slugger = $slugger;
        $this->doctrine = $doctrine;
    }

    /**
     * method slugify
     *
     * @param string $input
     * @return string
     */
    public function slugify(string $input, string $className, int $id = null): string
    {
        $slug = $this->slugger->slug($input)->lower();
        $respository = $this->doctrine->getRepository($className);
        $element = $respository->findBySlugWithoutId($slug, $id);

        $i = 1;
        while ($element)
        {

            $slug = $this->slugger->slug($input.'-'.$i)->lower();
            $element = $respository->findBySlugWithoutId($slug, $id);
        }
        return $slug;
    }
}