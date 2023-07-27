<?php

namespace T3docs\ProjectInfo\DataProvider;

abstract class BaseDataProvider implements DataProvider
{
    protected string $filename;
    protected string $header;

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getHeader(): string
    {
        return $this->header;
    }
}
