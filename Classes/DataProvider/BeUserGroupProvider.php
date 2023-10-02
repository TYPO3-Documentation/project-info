<?php

declare(strict_types=1);

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
        LanguageService $languageService
    ) {
        parent::__construct($languageService);
    }

    public function provide(): Table
    {
        $labels = ['uid', 'title', 'subgroup', 'description', 'count'];

        $labels = array_map(function ($value) {
            return isset($GLOBALS['TCA']['be_groups']['columns'][$value]['label'])?
                $this->languageService->translateLLL($GLOBALS['TCA']['be_groups']['columns'][$value]['label']) :
                $this->languageService->translateLocalLLL('be_groups.' . $value);
        }, $labels);
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
                $queryBuilder->expr()->eq('be_groups.uid', $queryBuilder->quoteIdentifier('be_users.usergroup'))
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
