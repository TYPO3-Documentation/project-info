<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

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
