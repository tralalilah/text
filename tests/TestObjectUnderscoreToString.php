<?php

namespace Lilah\TextTests;

class TestObjectUnderscoreToString
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
