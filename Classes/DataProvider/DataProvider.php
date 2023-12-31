<?php

namespace T3docs\ProjectInfo\DataProvider;

use T3docs\ProjectInfo\Component\Data;

interface DataProvider
{
    public function getFilename(): string;
    public function getHeader(): string;
    public function provide(): Data;
}
