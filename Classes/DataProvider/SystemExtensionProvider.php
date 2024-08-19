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
use T3docs\ProjectInfo\Utilities\RenderRstUtility;
use TYPO3\CMS\Core\Package\PackageManager;

class SystemExtensionProvider extends BaseDataProvider implements TableDataProvider
{
    protected string $filename = '_sys_extensions.rst.txt';
    protected string $header = 'System Extensions';

    public function __construct(
        private readonly PackageManager $packageManager,
        LanguageService $languageService,
    ) {
        parent::__construct($languageService);
    }

    public function provide(): Table
    {
        $labels = [
            'key',
            'version',
            'title',
            'description',
        ];
        $labels = array_map(fn($value) => $this->languageService->translateLocalLLL('extensions.' . $value), $labels);
        $data = [$labels];
        $packages = $this->packageManager->getActivePackages();
        usort($packages, fn($a, $b) => strcmp((string)$a->getPackageKey(), (string)$b->getPackageKey()));
        foreach ($packages as $package) {
            if (
                $package->getPackageMetaData()->isFrameworkType()
                && $this->packageManager->isPackageActive($package->getPackageKey())
                && $package->getPackageMetaData()->isExtensionType()
            ) {
                $data[] = [
                    RenderRstUtility::escape($package->getPackageKey()),
                    RenderRstUtility::escape($package->getPackageMetaData()->getVersion()),
                    RenderRstUtility::escape((string)$package->getPackageMetaData()->getTitle()),
                    RenderRstUtility::escape((string)$package->getPackageMetaData()->getDescription()),
                ];
            }
        }
        return new Table($data);
    }
}
