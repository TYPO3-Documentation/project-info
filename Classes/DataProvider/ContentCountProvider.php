<?php

namespace T3docs\ProjectInfo\DataProvider;

use T3docs\ProjectInfo\Component\Table;
use T3docs\ProjectInfo\Utilities\LanguageService;
use TYPO3\CMS\Core\Database\ConnectionPool;

class ContentCountProvider extends BaseDataProvider implements TableDataProvider
{
    protected string $filename = '_contentCount.rst.txt';
    protected string $header = 'header.content';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        LanguageService $languageService
    ) {
        parent::__construct($languageService);
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
        return [$this->languageService->translateLocalLLL('content_total'), $count];
    }

    protected function getContentTextOnly(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $count = $queryBuilder
            ->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter('text')
                )
            )
            ->executeQuery()
            ->fetchOne();
        return [$this->languageService->translateLocalLLL('content_text'), $count];
    }
}
