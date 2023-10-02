<?php

namespace T3docs\ProjectInfo\Utilities;

use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

class LanguageService
{
    private const PREFIX = 'LLL:EXT:project_info/Resources/Private/Language/locallang.xlf:';
    private string $locale = 'de-DE';

    public function __construct(
        private readonly LanguageServiceFactory $languageServiceFactory
    )
    {
    }

    public function translateLLL(string $lll): string
    {
        $languageService = $this->languageServiceFactory->create($this->locale);
        if (!str_starts_with($lll, 'LLL')) {
            return $lll;
        }
        $languageService = $this->languageServiceFactory->create($this->locale);
        return $languageService->sL($lll);
    }

    public function translateLocalLLL(string $lll): string
    {

        if (!str_starts_with($lll, 'LLL')) {
            $translated = $this->translateLLL(self::PREFIX . $lll);
        } else {
            $translated = $this->translateLLL($lll);
        }
        return$translated !== '' ? $translated : $lll;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}
