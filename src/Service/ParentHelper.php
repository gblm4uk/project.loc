<?php

namespace App\Service;

class ParentHelper
{
    /**
     * @var string
     */
    private $letter = 'a';

    /**
     * @return string
     */
    public function getFourLetters(): string
    {
        return $this->letter.$this->letter.$this->letter.$this->letter;
    }

    /**
     * @param string $letter
     *
     * @return $this
     */
    public function setLetter(string $letter): self
    {
        if (strlen($letter) > 1) {
            throw new \Exception('Nizzja!!!');
        }

        if (!in_array($letter, ['a', 'b', 'd', 'e'])) {
            throw new \Exception('Atata!!!');
        }
        $this->letter = $letter;

        return $this;
    }
}