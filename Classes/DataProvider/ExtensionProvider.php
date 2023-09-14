<?php

declare(strict_types=1);

namespace T3docs\ProjectInfo\DataProvider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use T3docs\ProjectInfo\Component\Table;
use T3docs\ProjectInfo\Utilities\RenderRstUtility;
use TYPO3\CMS\Core\Package\PackageManager;

class ExtensionProvider extends BaseDataProvider implements TableDataProvider
{
    protected string $filename = '_extensions.rst.txt';
    protected string $header = 'Extensions';

    public function __construct(
        private readonly PackageManager $packageManager
    )
    {
    }

    public function provide(): Table
    {
        $packagistBaseUrl = 'https://packagist.org';
        $data = [[
            'Extension Key',
            'Composer Name',
            'Version',
            'Title',
            'Description',
            'Source'
        ]];
        $packages = $this->packageManager->getActivePackages();
        usort($packages, function($a, $b){
            return strcmp($a->getPackageKey(), $b->getPackageKey());
        });
        foreach ($packages as $package) {
            if (
                !$package->getPackageMetaData()->isFrameworkType()
                && $this->packageManager->isPackageActive($package->getPackageKey())
                && $package->getPackageMetaData()->isExtensionType()
            ) {
                $composerName = (string)$package->getValueFromComposerManifest('name');
                $version = $package->getPackageMetaData()->getVersion();
                $client = new Client();
                try {
                    $response = $client->get("$packagistBaseUrl/packages/$composerName.json");
                    if ($response->getStatusCode() !== 200) {
                        $source = 'other / local';
                    } else {
                        $packageData = json_decode($response->getBody()->getContents(), true);
                        if (isset($packageData['package']['versions'][$version])) {
                            $source = 'https://packagist.org/packages/' . $composerName . '#' . $version;
                        } else if (isset($packageData['package']['versions']['v' . $version])) {
                            $source = 'https://packagist.org/packages/' . $composerName . '#v' . $version;
                        } else {
                            $source = 'version not in packagist';
                        }
                    }
                } catch (RequestException $e) {
                    $source = 'other / local';
                }
                $data[] = [
                    RenderRstUtility::escape ($package->getPackageKey()),
                    RenderRstUtility::escape ($source),
                    RenderRstUtility::escape ($package->getPackageMetaData()->getVersion()),
                    RenderRstUtility::escape ((string)$package->getPackageMetaData()->getTitle()),
                    RenderRstUtility::escape ((string)$package->getPackageMetaData()->getDescription()),
                    $source,
                ];
            }
        }
        return new Table($data);
    }
}
