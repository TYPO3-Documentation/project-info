<?php

declare(strict_types=1);

namespace T3docs\ProjectInfo\Command;

use B13\Make\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use T3docs\ProjectInfo\Component\TechnicalDocumentation;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TechnicalDocumentationCommand extends AbstractCommand
{
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

        // Create extension directory
        $absoluteDocsPath = Environment::getProjectPath() . '/' . $directory;
        if (!file_exists($absoluteDocsPath)) {
            try {
                GeneralUtility::mkdir_deep($absoluteDocsPath);
            } catch (\Exception $e) {
                $this->io->error('Creating of directory ' . $absoluteDocsPath . ' failed');
                return Command::FAILURE;
            }
        }
        $indexFile = rtrim($absoluteDocsPath, '/') . '/index.rst';
        if (file_exists($indexFile)
            && !$this->io->confirm('A index.rst does already exist. Do you want to override it?', true)
        ) {
            $this->io->note('Creating index.rst skipped');
        } elseif (!GeneralUtility::writeFile($indexFile, $documentation->__toString(), true)) {
            $this->io->error('Creating composer.json failed');
            return 1;
        }
        return Command::SUCCESS;
    }
}
