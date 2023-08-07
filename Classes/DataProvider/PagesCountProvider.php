<?php

declare(strict_types=1);

namespace T3docs\ProjectInfo\DataProvider;

use T3docs\ProjectInfo\Component\Table;
use TYPO3\CMS\Core\Database\ConnectionPool;

class PagesCountProvider extends BaseDataProvider implements TableDataProvider
{
    protected string $filename = '_pageCount.rst.txt';
    protected string $header = 'Pages';

    public function __construct(
        private readonly ConnectionPool $connectionPool
    ) {
    }

    public function provide(): Table
    {
        $data = [['Record', 'Count']];
        $data[] = $this->getPagesTotal();
        $data[] = $this->getPagesStandard();
        return new Table($data);
    }

    protected function getPagesTotal(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $count = $queryBuilder
            ->count('uid')
            ->from('pages')
            ->executeQuery()
            ->fetchOne();
        return ['Pages total', $count];
    }

    protected function getPagesStandard(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $count = $queryBuilder
            ->count('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('doktype', 1)
            )
            ->executeQuery()
            ->fetchOne();
        return ['Pages Standard', $count];
    }
}
