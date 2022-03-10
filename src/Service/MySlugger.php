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
     * method slugify for create
     *
     * @param string $input
     * @return string
     */
    public function slugifyCreate(string $input, string $className): string
    {
        $slug = $this->slugger->slug($input)->lower();
        $respository = $this->doctrine->getRepository($className);
        $element = $respository->findBySlugWithoutId($slug);

        $i = 1;
        while ($element)
        {

            $slug = $this->slugger->slug($input.'-'.$i)->lower();
            $element = $respository->findBySlugWithoutId($slug);
            $i++;
        }
        return $slug;
    }

    /**
     * method slugify for update
     *
     * @param string $input
     * @return string
     */
    public function slugifyUpdate(string $input, string $className, int $id = null): string
    {
        $slug = $this->slugger->slug($input)->lower();
        $respository = $this->doctrine->getRepository($className);
        $element = $respository->findBySlugWithId($slug, $id);

        $i = 1;
        while ($element)
        {

            $slug = $this->slugger->slug($input.'-'.$i)->lower();
            $element = $respository->findBySlugWithId($slug, $id);
            $i++;
        }
        return $slug;
    }

}