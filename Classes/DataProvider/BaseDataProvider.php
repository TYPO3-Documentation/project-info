<?php

namespace T3docs\ProjectInfo\DataProvider;

use T3docs\ProjectInfo\Utilities\LanguageService;

abstract class BaseDataProvider implements DataProvider
{
    protected string $filename;
    protected string $header;

    public function __construct(
        protected readonly LanguageService $languageService
    ) {
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getHeader(): string
    {
        return $this->languageService->translateLocalLLL($this->header);
    }
}
