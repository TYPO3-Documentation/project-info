<?php

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

use T3docs\ProjectInfo\Utilities\LanguageService;

abstract class BaseDataProvider implements DataProvider
{
    protected string $filename;
    protected string $header;

    public function __construct(
        protected readonly LanguageService $languageService,
    ) {}

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getHeader(): string
    {
        return $this->languageService->translateLocalLLL($this->header);
    }
}
