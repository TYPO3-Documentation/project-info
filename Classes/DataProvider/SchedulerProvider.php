<?php

declare(strict_types=1);

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
        LanguageService $languageService
    ) {
        parent::__construct($languageService);
    }

    public function provide(): Table
    {
        $labels = ['uid', 'groupName', 'description'];

        $labels = array_map(function ($value) {
            return isset($GLOBALS['TCA']['tx_scheduler_task']['columns'][$value]['label'])?
                $this->languageService->translateLLL($GLOBALS['TCA']['tx_scheduler_task']['columns'][$value]['label']) :
                $this->languageService->translateLocalLLL('tx_scheduler_task.' . $value);
        }, $labels);
        $data = [$labels];
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_scheduler_task');
        $result = $queryBuilder
            ->select('tx_scheduler_task.uid', 'tx_scheduler_task_group.groupName', 'tx_scheduler_task.description')
            ->from('tx_scheduler_task')
            ->where(
                $queryBuilder->expr()->eq('tx_scheduler_task.disable', 0)
            )
            ->andWhere(
                $queryBuilder->expr()->eq('tx_scheduler_task.deleted', 0)
            )
            ->leftJoin(
                'tx_scheduler_task',
                'tx_scheduler_task_group',
                'tx_scheduler_task_group',
                $queryBuilder->expr()->eq('tx_scheduler_task.task_group', $queryBuilder->quoteIdentifier('tx_scheduler_task_group.uid'))
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
