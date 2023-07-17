<?php

namespace T3docs\ProjectInfo\DataProvider;

use T3docs\ProjectInfo\Component\Table;

interface DataProvider
{
    public function getHeader(): string;
    public function provide(): Table;
}
