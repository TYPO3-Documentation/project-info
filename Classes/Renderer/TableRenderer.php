<?php

declare(strict_types=1);

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
use T3docs\ProjectInfo\DataProvider\TableDataProvider;

class TableRenderer implements Renderer
{
    public function render(DataProvider $dataProvider): string
    {
        if (!$dataProvider instanceof TableDataProvider) {
            throw new \Exception(self::class . ' cannot render ' . $dataProvider::class);
        }
        return $this->renderTable($dataProvider->provide());
    }

    public function canRender(DataProvider $dataProvider): bool
    {
        return $dataProvider instanceof TableDataProvider;
    }

    private function renderTable(Table $table): string
    {
        $tableCount = $this->getTableCount($table);
        $rst = self::tableDividerLine($tableCount);
        foreach ($table->getData() as $rowCount => $row) {
            $line = '';
            foreach ($row as $key => $field) {
                $line = ($line === '') ? '' : $line . '  ';
                $line .= (string)$field;
                if ($tableCount[$key] - mb_strlen((string)$field, 'UTF-8') > 0) {
                    $line .= str_repeat(' ', $tableCount[$key] - mb_strlen((string)$field, 'UTF-8'));
                }
            }
            $rst .= $line . "\n";
            if ($rowCount === 0) {
                $rst .= self::tableDividerLine($tableCount);
            }
        }
        if ((is_countable($table->getData()) ? count($table->getData()) : 0) > 1) {
            $rst .= self::tableDividerLine($tableCount);
        }
        return $rst;
    }

    private static function tableDividerLine(array $tableCount): string
    {
        $rst = '';
        foreach ($tableCount as $fieldCount) {
            $rst = ($rst === '') ? '' : $rst . '  ';
            if ($fieldCount > 0) {
                $rst .= str_repeat('=', (int)$fieldCount);
            }
        }
        $rst .= "\n";
        return $rst;
    }

    private function getTableCount(Table $table): array
    {
        $tableCount = [];
        foreach ($table->getData() as $row) {
            foreach (array_values($row) as $key => $field) {
                if (!isset($tableCount[$key])) {
                    $tableCount[$key] = 0;
                }
                $tableCount[$key] = max(strlen($field . ''), $tableCount[$key]);
            }
        }
        return $tableCount;
    }
}
