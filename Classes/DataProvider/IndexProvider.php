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
use TYPO3\CMS\Core\Database\ConnectionPool;

class IndexProvider extends BaseDataProvider
{
    protected string $filename = 'index.rst';
    protected string $header = 'Content';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

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
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter('text'),
                ),
            )
            ->executeQuery()
            ->fetchOne();
        return ['Text-only content', $count];
    }
}
