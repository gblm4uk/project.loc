<?php

namespace App\Service;

class Helper extends ParentHelper
{
    /**
     * @var string
     */
    protected $letter = 'b';

    /**
     * @return string
     */
    public function getFourLetters(): string
    {
        return $this->letter.$this->letter.$this->letter.$this->letter;
    }
}