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

namespace T3docs\ProjectInfo\Component\TechnicalDocumentation;

use T3docs\ProjectInfo\Component\Data;
use T3docs\ProjectInfo\Component\Table;
use T3docs\ProjectInfo\Utilities\RenderRstUtility;

class RecordCount implements Data, \Stringable
{
    private array $tables = [];
    public function __construct(array $data)
    {
        foreach ($data as $type => $table) {
            $this->tables[$type] = $table;
        }
    }
    public function __toString(): string
    {
        $meta = <<<'EOF'
%s
%s
EOF;
        $tableTypes = '';
        foreach ($this->tables as $type => $table) {
            $tableTypes .= $this->printTableType($type, $table);
        }
        return sprintf($meta, RenderRstUtility::renderHeadLine('Record Count'), $tableTypes);
    }
    private function printTableType(string $type, Table $table): string
    {
        $meta = <<<'EOF'
%s
%s

EOF;
        return sprintf($meta, RenderRstUtility::renderHeadLine($type, 1), $table->__toString());
    }
}
