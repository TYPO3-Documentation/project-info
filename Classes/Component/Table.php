<?php

namespace T3docs\ProjectInfo\Component;

class Table
{
    private array $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }


    public function __toString(): string
    {
        $tableCount = $this->getTableCount();
        $rst = self::tableDividerLine($tableCount);
        foreach ($this->data as $rowCount => $row) {
            $line = '';
            foreach ($row as $key => $field) {
                $line = ($line==='')?'':$line . '  ';
                $line .= $field . str_repeat(' ',$tableCount[$key] - strlen($field));
            }
            $rst .= $line . "\n";
            if ($rowCount === 0) {
                $rst .= self::tableDividerLine($tableCount);
            }
        }
        if (count($this->data) > 1) {
            $rst .= self::tableDividerLine($tableCount);
        }
        return $rst;
    }

    private static function tableDividerLine(array $tableCount):string {
        $rst = '';
        foreach ($tableCount as $fieldCount) {
            $rst = ($rst==='')?'':$rst . '  ';
            $rst .= str_repeat("=", (int)$fieldCount);
        }
        $rst .= "\n";
        return $rst;
    }

    private function getTableCount(): array
    {
        $tableCount = [];
        foreach ($this->data as $row) {
            foreach ($row as $key => $field) {
                if (!isset($tableCount[$key])) {
                    $tableCount[$key] = 0;
                }
                $tableCount[$key] = max(strlen($field . ''), $tableCount[$key]);
            }
        }
        return $tableCount;
    }
}
