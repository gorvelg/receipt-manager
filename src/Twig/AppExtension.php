<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('abs', [$this, 'absFilter']),
        ];
    }

    public function absFilter($number)
    {
        return abs($number);
    }
}
