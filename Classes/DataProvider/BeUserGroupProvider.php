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

class BeUserGroupProvider extends BaseDataProvider implements TableDataProvider
{
    protected string $filename = '_beUsergroups.rst.txt';
    protected string $header = 'header.beusergroups';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        LanguageService $languageService,
    ) {
        parent::__construct($languageService);
    }

    public function provide(): Table
    {
        $labels = ['uid', 'title', 'subgroup', 'description', 'count'];

        $labels = array_map(fn($value) => isset($GLOBALS['TCA']['be_groups']['columns'][$value]['label']) ?
            $this->languageService->translateLLL($GLOBALS['TCA']['be_groups']['columns'][$value]['label']) :
            $this->languageService->translateLocalLLL('be_groups.' . $value), $labels);
        $data = [$labels];
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('be_groups');
        $result = $queryBuilder
            ->select('be_groups.uid', 'be_groups.title', 'be_groups.subgroup', 'be_groups.description')
            ->addSelectLiteral('COUNT(be_users.uid) as userCount')
            ->from('be_groups')
            ->leftJoin(
                'be_groups',
                'be_users',
                'be_users',
                $queryBuilder->expr()->eq('be_groups.uid', $queryBuilder->quoteIdentifier('be_users.usergroup')),
            )
            ->groupBy('be_groups.uid')
            ->executeQuery();
        while ($row = $result->fetchAssociative()) {
            // Do something with that single row
            $data[] = array_values($row);
        }
        return new Table($data);
    }
}
