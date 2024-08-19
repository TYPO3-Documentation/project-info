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

class SchedulerProvider extends BaseDataProvider implements TableDataProvider
{
    protected string $filename = '_scheduler.rst.txt';
    protected string $header = 'header.scheduler';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        LanguageService $languageService,
    ) {
        parent::__construct($languageService);
    }

    public function provide(): Table
    {
        $labels = ['uid', 'groupName', 'description'];

        $labels = array_map(fn($value) => isset($GLOBALS['TCA']['tx_scheduler_task']['columns'][$value]['label']) ?
            $this->languageService->translateLLL($GLOBALS['TCA']['tx_scheduler_task']['columns'][$value]['label']) :
            $this->languageService->translateLocalLLL('tx_scheduler_task.' . $value), $labels);
        $data = [$labels];
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_scheduler_task');
        $result = $queryBuilder
            ->select('tx_scheduler_task.uid', 'tx_scheduler_task_group.groupName', 'tx_scheduler_task.description')
            ->from('tx_scheduler_task')
            ->where(
                $queryBuilder->expr()->eq('tx_scheduler_task.disable', 0),
            )
            ->andWhere(
                $queryBuilder->expr()->eq('tx_scheduler_task.deleted', 0),
            )
            ->leftJoin(
                'tx_scheduler_task',
                'tx_scheduler_task_group',
                'tx_scheduler_task_group',
                $queryBuilder->expr()->eq('tx_scheduler_task.task_group', $queryBuilder->quoteIdentifier('tx_scheduler_task_group.uid')),
            )
            ->orderBy('tx_scheduler_task_group.sorting')
            ->executeQuery();
        while ($row = $result->fetchAssociative()) {
            // Do something with that single row
            $data[] = array_values($row);
        }
        return new Table($data);
    }
}
