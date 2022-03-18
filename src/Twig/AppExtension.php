<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('sha1', [$this, 'formatSha1']),
        ];
    }

    public function formatSha1(string $title): string
    {
        return sha1($title);
    }
}