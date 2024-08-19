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

use T3docs\ProjectInfo\Component\Table;
use T3docs\ProjectInfo\DataProvider\DataProvider;
use T3docs\ProjectInfo\DataProvider\ExtensionProvider;
use T3docs\ProjectInfo\DataProvider\TableDataProvider;
use T3docs\ProjectInfo\Utilities\RenderRstUtility;

class ExtensionRenderer implements Renderer
{
    public function render(DataProvider $dataProvider): string
    {
        if (!$dataProvider instanceof TableDataProvider) {
            throw new \Exception(self::class . ' cannot render ' . $dataProvider::class);
        }
        return $this->renderTable($dataProvider->provide());
    }

    private function renderTable(Table $table): string
    {
        $rst = '';
        $data = $table->getData();
        $header = array_shift($data);
        foreach ($data as $extension) {
            $rst .=  RenderRstUtility::renderHeadLine($extension[0], 2) . "\n\n";
            for ($i = 1; $i < (is_countable($extension) ? count($extension) : 0); $i++) {
                if (isset($header[$i])) {
                    $rst .= sprintf('*   **%s**: ', $header[$i]);
                }
                $rst .= $extension[$i] . "\n";
            }
            $rst .= "\n\n";
        }
        return $rst . "\n";
    }

    public function canRender(DataProvider $dataProvider): bool
    {
        return $dataProvider instanceof ExtensionProvider;
    }
}
