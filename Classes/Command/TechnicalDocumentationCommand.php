<?php

declare(strict_types=1);

namespace T3docs\ProjectInfo\Command;

use B13\Make\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use T3docs\ProjectInfo\Component\TechnicalDocumentation;
use T3docs\ProjectInfo\ConfigurationManager;
use T3docs\ProjectInfo\DataProvider\ContentCountProvider;
use T3docs\ProjectInfo\DataProvider\ExtensionProvider;
use T3docs\ProjectInfo\DataProvider\PagesCountProvider;
use T3docs\ProjectInfo\DataProvider\SystemExtensionProvider;
use T3docs\ProjectInfo\Renderer\ExtensionRenderer;
use T3docs\ProjectInfo\Renderer\TableRenderer;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TechnicalDocumentationCommand extends AbstractCommand
{
    public function __construct(
        private readonly PagesCountProvider $pagesCountProvider,
        private readonly ContentCountProvider $contentCountProvider,
        private readonly ExtensionProvider $extensionProvider,
        private readonly SystemExtensionProvider $systemExtensionProvider,
        private readonly ConfigurationManager $configurationManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $directory = (string)$this->io->ask(
            'Where should the documentation be created?',
            $this->getProposalFromEnvironment('EXTENSION_DIR', 'docs/')
        );

        $filePath = $directory . 'config.json';

        if (file_exists($filePath)) {
            // Read the JSON file contents
            $config = file_get_contents($filePath);

            // Attempt to decode the JSON data into a PHP array
            $config = json_decode($config, true);

            // Check if the JSON decoding was successful
            if ($config === null) {
                echo "JSON decoding of $filePath failed.";
                $config = [];
            }
        } else {
            echo "The JSON file $filePath does not exist.";
            $config = [];
        }

        $config['settings']['directory'] = $directory;
        $config['settings']['language'] = $config['settings']['language'] ?? (string)$this->io->ask(
            'In what language?',
            'en-US'
        );
        $config['settings']['projectTitle'] = $config['settings']['projectTitle'] ?? (string)$this->io->ask(
            'Enter the project title',
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']??'My new site'
        );
        $config['settings']['version'] = $config['settings']['version'] ?? (string)$this->io->ask(
            'Enter the version of this documentation',
            'main'
        );
        $config['settings']['description'] = $config['settings']['description'] ?? (string)$this->io->ask(
            'Enter the description',
            null
        );

        $documentation = (new TechnicalDocumentation());

        $this->configurationManager->setConfiguration($config);
        $dataProviders = [
            $this->pagesCountProvider,
            $this->contentCountProvider,
            $this->extensionProvider,
            $this->systemExtensionProvider,
        ];
        $renderers = [
            new ExtensionRenderer(),
            new TableRenderer(),
        ];

        try {
            $absoluteDocsPath = $this->getAbsoluteDocsPath($directory);
            //$this->writeFile($absoluteDocsPath, 'index.rst', $documentation->__toString());
            $absoluteIncludesPath = $this->getAbsoluteDocsPath($directory . '/_includes');
            foreach ($dataProviders as $dataProvider) {
                foreach ($renderers as $renderer) {
                    if ($renderer->canRender($dataProvider)) {
                        $this->writeFile($absoluteIncludesPath, $dataProvider->getFilename(), $renderer->render($dataProvider));
                        break;
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->io->error($exception->getMessage());
            return Command::FAILURE;
        }
        $updatedConfigJsonData = json_encode($this->configurationManager->getConfiguration(), JSON_PRETTY_PRINT);
        if (file_put_contents($filePath, $updatedConfigJsonData) !== false) {
            echo "JSON data has been updated and written to the file $filePath. You can override the settings here. \n";
        } else {
            echo "Failed to write JSON data to the file $config.";
        }
        return Command::SUCCESS;
    }

    private function writeFile(string $absoluteDocsPath, string $fileName, string $content): void
    {
        $absoluteFileName = rtrim($absoluteDocsPath, '/') . '/' . $fileName;
        if (file_exists($absoluteFileName)
            && !$this->io->confirm('A ' . $fileName . ' does already exist. Do you want to override it?', true)
        ) {
            $this->io->note('Creating ' . $fileName . ' skipped');
        } elseif (!GeneralUtility::writeFile($absoluteFileName, $content, true)) {
            throw new \Exception('Creating ' . $fileName . ' failed');
        }
    }

    /**
     * @throws \Exception
     */
    protected function getAbsoluteDocsPath(string $directory): string
    {
        $absoluteDocsPath = Environment::getProjectPath() . '/' . $directory;
        if (!file_exists($absoluteDocsPath)) {
            try {
                GeneralUtility::mkdir_deep($absoluteDocsPath);
            } catch (\Exception) {
                throw new \Exception('Creating of directory ' . $absoluteDocsPath . ' failed');
            }
        }
        return $absoluteDocsPath;
    }
}
