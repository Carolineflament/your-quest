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
    public function slugify(string $input, string $className): string
    {
        $slug = $this->slugger->slug($input)->lower();
        $repository = $this->doctrine->getRepository($className)->findOneBy(['slug' => $slug]);

        $i = 1;
        while ($repository)
        {
            $slug = $this->slugger->slug($input.'-'.$i)->lower();
            $repository = $this->doctrine->getRepository($className)->findOneBy(['slug' => $slug]);
        }
        return $slug;
    }
}