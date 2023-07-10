<?php

declare(strict_types=1);

namespace T3docs\ProjectInfo\Component;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * TechnicalDocumentation component
 */
class TechnicalDocumentation
{
    protected string $projectName = '';

    protected string $version = '';

    protected string $description = '';

    protected string $directory = '';

    public function getProjectName(): string
    {
        return $this->projectName;
    }

    public function setProjectName(string $projectName): TechnicalDocumentation
    {
        $this->projectName = $projectName;
        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): TechnicalDocumentation
    {
        $this->version = $version;
        return $this;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function setDirectory(string $directory): TechnicalDocumentation
    {
        $this->directory = $directory;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): TechnicalDocumentation
    {
        $this->description = $description;
        return $this;
    }


    public function __toString(): string
    {
        $meta = <<<'EOF'

:project name:
    %s
:version:
    %s

=================================================================================
|project|
=================================================================================

%s

..  toctree:: Table of content
    :glob:
    :titlesonly:

    *

EOF;
        return sprintf($meta, $this->projectName, $this->version, $this->description);

    }
}