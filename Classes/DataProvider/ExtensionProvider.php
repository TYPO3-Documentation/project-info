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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use T3docs\ProjectInfo\Component\Table;
use T3docs\ProjectInfo\ConfigurationManager;
use T3docs\ProjectInfo\Utilities\LanguageService;
use T3docs\ProjectInfo\Utilities\RenderRstUtility;
use TYPO3\CMS\Core\Package\PackageManager;

class ExtensionProvider extends BaseDataProvider implements TableDataProvider
{
    protected string $filename = '_extensions.rst.txt';
    protected string $header = 'Extensions';

    public function __construct(
        private readonly PackageManager $packageManager,
        private readonly ConfigurationManager $configurationManager,
        LanguageService $languageService,
    ) {
        parent::__construct($languageService);
    }

    public function provide(): Table
    {
        $packagistBaseUrl = 'https://packagist.org';
        $labels = [
            'key',
            'composer',
            'version',
            'title',
            'description',
            'source',
        ];
        $labels = array_map(fn($value) => $this->languageService->translateLocalLLL('extensions.' . $value), $labels);
        $data = [$labels];
        $packages = $this->packageManager->getActivePackages();
        usort($packages, fn($a, $b) => strcmp((string)$a->getPackageKey(), (string)$b->getPackageKey()));
        $configuration = $this->configurationManager->getConfiguration();
        foreach ($packages as $package) {
            if (!$package->getPackageMetaData()->isExtensionType()) {
                // ignore packages that are no extensions
            }
            if ($package->getPackageMetaData()->isFrameworkType()) {
                // ignore system extensions
                continue;
            }
            if (!$this->packageManager->isPackageActive($package->getPackageKey())) {
                // ignore disabled extensions
                continue;
            }
            if ($configuration['extensions'][$package->getPackageKey()]['ignore'] ?? 0 === 1) {
                // ignore extensions configured to be ignored
                continue;
            }
            $composerName = (string)$package->getValueFromComposerManifest('name');
            $version = $package->getPackageMetaData()->getVersion();
            if (isset($configuration['extensions'][$package->getPackageKey()]['source'])) {
                $source = $configuration['extensions'][$package->getPackageKey()]['source'];
            } else {
                $client = new Client();
                try {
                    $response = $client->get("$packagistBaseUrl/packages/$composerName.json");
                    if ($response->getStatusCode() !== 200) {
                        $source = 'other / local';
                    } else {
                        $packageData = json_decode((string)$response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
                        if (isset($packageData['package']['versions'][$version])) {
                            $source = 'https://packagist.org/packages/' . $composerName . '#' . $version;
                        } elseif (isset($packageData['package']['versions']['v' . $version])) {
                            $source = 'https://packagist.org/packages/' . $composerName . '#v' . $version;
                        } else {
                            $source = 'version not in packagist';
                        }
                    }
                } catch (RequestException) {
                    $source = 'other / local';
                }
            }
            $data[] = [
                RenderRstUtility::escape($package->getPackageKey()),
                RenderRstUtility::escape($composerName),
                RenderRstUtility::escape($configuration['extensions'][$package->getPackageKey()]['version'] ?? $package->getPackageMetaData()->getVersion()),
                RenderRstUtility::escape($configuration['extensions'][$package->getPackageKey()]['title'] ?? (string)$package->getPackageMetaData()->getTitle()),
                RenderRstUtility::escape($configuration['extensions'][$package->getPackageKey()]['description'] ?? (string)$package->getPackageMetaData()->getDescription()),
                $source,
            ];

            if (!isset($configuration['extensions'][$package->getPackageKey()])) {
                $configuration['extensions'][$package->getPackageKey()] = [];
            }
        }
        $this->configurationManager->setConfiguration($configuration);
        return new Table($data);
    }
}
