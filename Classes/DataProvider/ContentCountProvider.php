<?php

namespace T3docs\ProjectInfo\DataProvider;

use T3docs\ProjectInfo\Component\Table;
use TYPO3\CMS\Core\Database\ConnectionPool;

class ContentCountProvider implements DataProvider
{
    public function __construct(
        private readonly ConnectionPool $connectionPool
    ) {
    }

    public function getHeader(): string
    {
        return 'Content';
    }

    public function provide(): Table
    {
        $data = [['Record', 'Count']];
        $data[] = $this->getContentTotal();
        $data[] = $this->getContentTextOnly();
        return new Table($data);
    }

    protected function getContentTotal(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $count = $queryBuilder
            ->count('uid')
            ->from('tt_content')
            ->executeQuery()
            ->fetchOne();
        return ['Content total', $count];
    }

    protected function getContentTextOnly(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $count = $queryBuilder
            ->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType',
                        $queryBuilder->createNamedParameter('text')
                )
            )
            ->executeQuery()
            ->fetchOne();
        return ['Text-only content', $count];
    }
}
