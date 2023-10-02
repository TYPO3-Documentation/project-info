<?php

namespace T3docs\ProjectInfo\DataProvider;

use T3docs\ProjectInfo\Component\Table;
use T3docs\ProjectInfo\ConfigurationManager;
use T3docs\ProjectInfo\Utilities\LanguageService;
use TYPO3\CMS\Core\Database\ConnectionPool;

class ContentCountProvider extends BaseDataProvider implements TableDataProvider
{
    protected string $filename = '_contentCount.rst.txt';
    protected string $header = 'header.content';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly ConfigurationManager $configurationManager,
        LanguageService $languageService
    ) {
        parent::__construct($languageService);
    }

    public function provide(): Table
    {
        $configuration = $this->configurationManager->getConfiguration();
        if (!isset($configuration['content'])) {
            $configuration['content'] = [
                ['pages'],
                ['pages','doktype', '1'],
                ['tt_content'],
                ['tt_content','CType', 'text'],
            ];
        }
        $data = [['Record', 'Count']];
        foreach ($configuration['content'] as $content) {
            if (count($content) > 0 && count($content) < 3) {
                $data[] = $this->getCountTotal($content[0], $content[1]??null);
            } else if (count($content) < 5) {
                if (str_contains($content[2], '%')) {
                    $data[] = $this->getCountWhereLike($content[0], $content[1], $content[2], $content[3]??null);
                } else {
                    $data[] = $this->getCountWhereEquals($content[0], $content[1], $content[2], $content[3]??null);
                }
            }
        }
        $this->configurationManager->setConfiguration($configuration);
        return new Table($data);
    }

    protected function getCountTotal(string $table, ?string $label = null): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $count = $queryBuilder
            ->count('uid')
            ->from($table)
            ->executeQuery()
            ->fetchOne();
        $label = $label??$this->languageService->translateLocalLLL($table . '.total');
        return [$label, $count];
    }

    protected function getCountWhereEquals(string $table, string $field, string $value, ?string $label = null): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $count = $queryBuilder
            ->count('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    $field,
                    $queryBuilder->createNamedParameter($value)
                )
            )
            ->executeQuery()
            ->fetchOne();
        $label = $label??$this->languageService->translateLocalLLL(sprintf('%s.%s.%s', $table,$field, $value));
        return [$label, $count];
    }
    protected function getCountWhereLike(string $table, string $field, string $value, ?string $label = null): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $count = $queryBuilder
            ->count('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->like(
                    $field,
                    $queryBuilder->createNamedParameter($value)
                )
            )
            ->executeQuery()
            ->fetchOne();
        $label = $label??$this->languageService->translateLocalLLL(sprintf('%s.%s.%s', $table,$field, $value));
        return [$label, $count];
    }
}
