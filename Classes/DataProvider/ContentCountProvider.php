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
        LanguageService $languageService,
    ) {
        parent::__construct($languageService);
    }

    public function provide(): Table
    {
        $configuration = $this->configurationManager->getConfiguration();
        if (!isset($configuration['content'])) {
            $configuration['content'] = [
                ['pages'],
                ['pages', 'doktype', '1'],
                ['tt_content'],
                ['tt_content', 'CType', 'text'],
                [
                    'sys_file',
                    'type',
                    '4',
                ],
                [
                    'sys_file',
                    'type',
                    '2',
                ],
                [
                    'sys_file',
                    'mime_type',
                    "application\/pdf",
                ],
            ];
        }
        $data = [['Record', 'Count']];
        foreach ($configuration['content'] as $content) {
            if ((is_countable($content) ? count($content) : 0) > 0 && (is_countable($content) ? count($content) : 0) < 3) {
                $data[] = $this->getCountTotal($content[0], $content[1] ?? null);
            } elseif ((is_countable($content) ? count($content) : 0) < 5) {
                if (str_contains((string)$content[2], '%')) {
                    $data[] = $this->getCountWhereLike($content[0], $content[1], $content[2], $content[3] ?? null);
                } else {
                    $data[] = $this->getCountWhereEquals($content[0], $content[1], $content[2], $content[3] ?? null);
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
        $label ??= $this->languageService->translateLocalLLL($table . '.total');
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
                    $queryBuilder->createNamedParameter($value),
                ),
            )
            ->executeQuery()
            ->fetchOne();
        $label ??= $this->languageService->translateLocalLLL(sprintf('%s.%s.%s', $table, $field, $value));
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
                    $queryBuilder->createNamedParameter($value),
                ),
            )
            ->executeQuery()
            ->fetchOne();
        $label ??= $this->languageService->translateLocalLLL(sprintf('%s.%s.%s', $table, $field, $value));
        return [$label, $count];
    }
}
