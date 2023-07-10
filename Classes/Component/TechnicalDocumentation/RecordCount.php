<?php

namespace T3docs\ProjectInfo\Component\TechnicalDocumentation;

use T3docs\ProjectInfo\Component\Table;
use T3docs\ProjectInfo\Utilities\RenderRstUtility;

class RecordCount
{
    private array $tables = [];

    public function __construct(array $data) {
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
