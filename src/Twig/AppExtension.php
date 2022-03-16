<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('sha1', [$this, 'formatSha1']),
        ];
    }

    public function formatSha1($title)
    {
        return sha1($title);
    }
}