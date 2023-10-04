<?php

declare(strict_types=1);

namespace T3docs\ProjectInfo\Renderer;

use T3docs\ProjectInfo\DataProvider\DataProvider;

interface Renderer
{
    public function render(DataProvider $dataProvider): string;
    public function canRender(DataProvider $dataProvider): bool;
}
