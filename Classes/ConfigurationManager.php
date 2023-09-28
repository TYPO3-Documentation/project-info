<?php

namespace T3docs\ProjectInfo;
class ConfigurationManager
{
    private array $configuration = [];

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

}
