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

namespace T3docs\ProjectInfo\Component;

class Table implements Data
{
    public function __construct(private readonly array $data) {}

    public function getData(): array
    {
        return $this->data;
    }

    public function __toString(): string
    {
        return json_encode($this->data);
    }
}
