<?php

namespace T3docs\ProjectInfo\Renderer;

use T3docs\ProjectInfo\DataProvider\DataProvider;

class TechnicalDocumentationRenderer implements Renderer
{

    public function render(DataProvider $dataProvider): string
    {
        // TODO: Implement render() method.
    }

    public function canRender(DataProvider $dataProvider): bool
    {
        return false;
    }
}
