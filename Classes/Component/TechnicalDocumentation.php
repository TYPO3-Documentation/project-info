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

namespace T3docs\ProjectInfo\Component;

use T3docs\ProjectInfo\Utilities\RenderRstUtility;

/**
 * TechnicalDocumentation component
 */
class TechnicalDocumentation implements Data, \Stringable
{
    protected string $projectName = '';
    protected string $version = '';
    protected string $description = '';
    protected string $directory = '';
    protected array $options = [];
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
    public function getOptions(): array
    {
        return $this->options;
    }
    public function setOptions(array $options): TechnicalDocumentation
    {
        $this->options = $options;
        return $this;
    }
    public function __toString(): string
    {
        $meta = <<<'EOF'

:project name:
    %s
:version:
    %s

%s

%s

..  toctree:: Table of content
    :glob:
    :titlesonly:

    *

EOF;
        return sprintf($meta, $this->projectName, $this->version, RenderRstUtility::renderHeadLine($this->projectName, 0), $this->description);
    }
}
