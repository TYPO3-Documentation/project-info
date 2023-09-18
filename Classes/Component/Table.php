<?php

namespace T3docs\ProjectInfo\Component;

class Table implements Data
{
    public function __construct(private readonly array $data)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }
}
