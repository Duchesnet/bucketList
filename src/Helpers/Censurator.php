<?php

namespace App\Helpers;

class Censurator
{
    private $censure = ['macron', 'politique', 'chat'];

    public function purify(string $string) : string
    {
        foreach ($this->censure as $censure) {
            $pattern = '/\b' . preg_quote($censure, '/') . '\b/i';
            $string = preg_replace_callback($pattern, function ($matches) {
                return str_repeat('*', strlen($matches[0]));
            }, $string);
        }
        return $string;
    }

}