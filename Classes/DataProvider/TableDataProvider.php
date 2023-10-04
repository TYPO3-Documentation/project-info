<?php

namespace T3docs\ProjectInfo\DataProvider;

use T3docs\ProjectInfo\Component\Table;

interface TableDataProvider extends DataProvider
{
    public function getFilename(): string;
    public function getHeader(): string;
    public function provide(): Table;
}
