<?php

declare(strict_types=1);

namespace T3docs\ProjectInfo\Command;

use B13\Make\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use T3docs\ProjectInfo\Component\TechnicalDocumentation;
use T3docs\ProjectInfo\ConfigurationManager;
use T3docs\ProjectInfo\DataProvider\BeUserGroupProvider;
use T3docs\ProjectInfo\DataProvider\BeUserGroupTableProvider;
use T3docs\ProjectInfo\DataProvider\ContentCountProvider;
use T3docs\ProjectInfo\DataProvider\ExtensionProvider;
use T3docs\ProjectInfo\DataProvider\SchedulerProvider;
use T3docs\ProjectInfo\DataProvider\SystemExtensionProvider;
use T3docs\ProjectInfo\Renderer\ExtensionRenderer;
use T3docs\ProjectInfo\Renderer\TableRenderer;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TechnicalDocumentationCommand extends AbstractCommand
{
    private bool $overrideAll = false;
    public function __construct(
        private readonly ContentCountProvider $contentCountProvider,
        private readonly BeUserGroupProvider $beUserGroupProvider,
        private readonly BeUserGroupTableProvider $beUserGroupTableProvider,
        private readonly ExtensionProvider $extensionProvider,
        private readonly SystemExtensionProvider $systemExtensionProvider,
        private readonly SchedulerProvider $schedulerProvider,
        private readonly ConfigurationManager $configurationManager
    ) {
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
            $config = json_decode($config, true, 512, JSON_THROW_ON_ERROR);

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
        $config['settings']['language'] ??= (string)$this->io->ask(
            'In what language?',
            'en-US'
        );
        $config['settings']['projectTitle'] ??= (string)$this->io->ask(
            'Enter the project title',
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] ?? 'My new site'
        );
        $config['settings']['company'] ??= (string)$this->io->ask(
            'Enter the name of the company providing the documentation',
            'My Company'
        );
        $config['settings']['author'] ??= (string)$this->io->ask(
            'Enter the name of the author providing the documentation',
            'Me, myself and I'
        );
        $config['settings']['version'] ??= (string)$this->io->ask(
            'Enter the version of this documentation',
            'main'
        );
        $config['settings']['description'] ??= (string)$this->io->ask(
            'Enter the description',
            null
        );

        $documentation = (new TechnicalDocumentation());

        $this->configurationManager->setConfiguration($config);
        $dataProviders = [
            $this->beUserGroupProvider,
            $this->beUserGroupTableProvider,
            $this->contentCountProvider,
            $this->schedulerProvider,
            $this->extensionProvider,
            $this->systemExtensionProvider,
        ];
        $files = [
            'Includes.rst.txt' => [],
            'Index.rst' => [],
            '01_project.rst' => [],
            '02_frameworks.rst' => [],
            '03_system.rst' => [],
            '04_extensions.rst' => [],
            '05_apis.rst' => [],
            '06_userrights.rst' => [],
            '07_cronjobs.rst' => [],
            '08_serverpaths.rst' => [],
            '09_contentcount.rst' => [],
            '10_other.rst' => [],
        ];
        $renderers = [
            new ExtensionRenderer(),
            new TableRenderer(),
        ];
        // Specify our Twig templates location
        $loader = new FilesystemLoader(__DIR__.'/../../Resources/Private/Templates/' . str_replace('-', '_', $config['settings']['language']));

        $globalData = $config;
        $globalData['global']['year'] = date('Y');
        $globalData['global']['date'] = date('d.m.Y');

        // Instantiate our Twig
        $twig = new TwigEnvironment($loader);
        try {
            $absoluteDocsPath = $this->getAbsoluteDocsPath($directory);
            //$this->writeFile($absoluteDocsPath, 'index.rst', $documentation->__toString());
            $absoluteIncludesPath = $this->getAbsoluteDocsPath($directory . '/_includes');
            $absolutePath = $this->getAbsoluteDocsPath($directory);
            foreach ($dataProviders as $dataProvider) {
                foreach ($renderers as $renderer) {
                    if ($renderer->canRender($dataProvider)) {
                        $this->writeFile($absoluteIncludesPath, $dataProvider->getFilename(), $renderer->render($dataProvider));
                        break;
                    }
                }
            }
            foreach ($files as $key => $fileConfig) {
                $data = array_merge($globalData, $fileConfig['data'] ?? []);
                $this->writeFile($absolutePath, $key, $twig->render($fileConfig['template']??$key . '.twig', $data));
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
        $options = ['Override', 'Skip', 'All'];
        if (file_exists($absoluteFileName)
        ) {
            if (!$this->overrideAll) {
                $choice = strtolower($this->io->choice('A ' . $fileName . ' does already exist. Do you want to override it?', $options, 'Override'));
                $this->overrideAll = $choice === 'all' || $choice === 'a';
                if ($choice === 'skip' || $choice === 's') {
                    $this->io->note('Creating ' . $fileName . ' skipped');
                    return;
                }
            }
        }
        if (!GeneralUtility::writeFile($absoluteFileName, $content, true)) {
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
