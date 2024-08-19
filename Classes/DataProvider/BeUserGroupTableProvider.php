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

namespace T3docs\ProjectInfo\DataProvider;

use T3docs\ProjectInfo\Component\Table;
use T3docs\ProjectInfo\Utilities\LanguageService;
use TYPO3\CMS\Core\Database\ConnectionPool;

class BeUserGroupTableProvider extends BaseDataProvider implements TableDataProvider
{
    protected string $filename = '_beUsergroupTables.rst.txt';
    protected string $header = 'header.beusergroups';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        LanguageService $languageService,
    ) {
        parent::__construct($languageService);
    }

    public function provide(): Table
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('be_groups');
        $result = $queryBuilder
            ->select('uid', 'title', 'tables_select', 'tables_modify')
            ->from('be_groups')
            ->executeQuery()
            ->fetchAllAssociative();
        $tables = [];
        foreach ($result as $row) {
            $tables = array_merge(
                $tables,
                array_map('trim', explode(',', (string)$row['tables_select'])),
            );
            $tables = array_merge(
                $tables,
                array_map('trim', explode(',', (string)$row['tables_modify'])),
            );
        }
        // Remove duplicates
        $tables = array_unique($tables);

        // Remove empty strings
        $tables = array_filter($tables, fn($value) => $value !== '' && isset($GLOBALS['TCA'][$value]));
        $tableLabels = array_map(fn($value) => isset($GLOBALS['TCA'][$value]['ctrl']['title']) ? $this->languageService->translateLLL($GLOBALS['TCA'][$value]['ctrl']['title']) : $value, $tables);
        $tables = array_values($tables);
        $data = [[$this->languageService->translateLocalLLL('tables'), ...$tableLabels]];
        foreach ($result as $row) {
            $rowData = [$row['title']];
            $selectTables = array_map('trim', explode(',', (string)$row['tables_select']));
            $modifyTables = array_map('trim', explode(',', (string)$row['tables_modify']));
            $hasRight = false;
            foreach ($tables as $table) {
                if (in_array($table, $modifyTables)) {
                    $rowData[] = 'W';
                    $hasRight = true;
                } elseif (in_array($table, $selectTables)) {
                    $rowData[] = 'R';
                    $hasRight = true;
                } else {
                    $rowData[] = '0';
                }
            }
            if ($hasRight) {
                $data[] = $rowData;
            }
        }
        $flippedArray = [];
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $flippedArray[$colIndex][$rowIndex] = $value;
            }
        }

        return new Table($flippedArray);
    }
}
