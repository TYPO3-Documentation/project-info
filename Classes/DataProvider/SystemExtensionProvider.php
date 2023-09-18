<?php

declare(strict_types=1);

namespace T3docs\ProjectInfo\DataProvider;

use T3docs\ProjectInfo\Component\Table;
use T3docs\ProjectInfo\Utilities\RenderRstUtility;
use TYPO3\CMS\Core\Package\PackageManager;

class SystemExtensionProvider extends BaseDataProvider implements TableDataProvider
{
    protected string $filename = '_sys_extensions.rst.txt';
    protected string $header = 'System Extensions';

    public function __construct(
        private readonly PackageManager $packageManager
    ) {
    }

    public function provide(): Table
    {
        $data = [[
            'Extension Key',
            'Version',
            'Title',
            'Description',
        ]];
        $packages = $this->packageManager->getActivePackages();
        usort($packages, fn ($a, $b) => strcmp((string)$a->getPackageKey(), (string)$b->getPackageKey()));
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
