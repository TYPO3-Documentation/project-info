<?php

declare(strict_types=1);

namespace T3docs\ProjectInfo\Command;

use B13\Make\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use T3docs\ProjectInfo\Component\TechnicalDocumentation;
use T3docs\ProjectInfo\Component\TechnicalDocumentation\RecordCount;
use T3docs\ProjectInfo\DataProvider\ContentCountProvider;
use T3docs\ProjectInfo\DataProvider\PagesCountProvider;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TechnicalDocumentationCommand extends AbstractCommand
{

    public function __construct(
        private readonly PagesCountProvider $pagesCountProvider,
        private readonly ContentCountProvider $contentCountProvider
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
        $projectTitle = (string)$this->io->ask(
            'Enter the project title',
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']??'My new site'
        );
        $version = (string)$this->io->ask(
            'Enter the version of this documentation',
            'main'
        );
        $description = (string)$this->io->ask(
            'Enter the description',
            null
        );

        $documentation = (new TechnicalDocumentation())
            ->setProjectName($projectTitle)
            ->setVersion($version)
            ->setDescription($description);

        try {
            $absoluteDocsPath = $this->getAbsoluteDocsPath($directory);
            $this->writeFile($absoluteDocsPath,'index.rst', $documentation->__toString());
            $recordCount = new RecordCount(
                [
                    $this->pagesCountProvider->getHeader() => $this->pagesCountProvider->provide(),
                    $this->contentCountProvider->getHeader() => $this->contentCountProvider->provide(),
                ]
            );
            $this->writeFile($absoluteDocsPath,'recordCount.rst', $recordCount->__toString());
        } catch (\Exception $exception) {
            $this->io->error($exception->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

    private function writeFile(string $absoluteDocsPath, string $fileName, string $content): void
    {
        $absoluteFileName = rtrim($absoluteDocsPath, '/') . '/' . $fileName;
        if (file_exists($absoluteFileName)
            && !$this->io->confirm('A ' . $fileName .' does already exist. Do you want to override it?', true)
        ) {
            $this->io->note('Creating '.$fileName.' skipped');
        } elseif (!GeneralUtility::writeFile($absoluteFileName, $content, true)) {
            throw new \Exception('Creating '.$fileName.' failed');
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
            } catch (\Exception $e) {
                throw new \Exception('Creating of directory ' . $absoluteDocsPath . ' failed');
            }
        }
        return $absoluteDocsPath;
    }

}
