<?php

declare(strict_types=1);

namespace T3docs\ProjectInfo\Utilities;

class RenderRstUtility
{
    public const EOL = "\n";
    public const HEADER_CHARS = ['=', '=', '-', '~', '\''];

    public static function renderHeadLine(string $headline, int $level = 0) : string
    {
        $rst = '';
        if ($level === 0) {
            $rst .= str_repeat(self::HEADER_CHARS[$level], strlen($headline)) . self::EOL;
        }
        $rst .= $headline . self::EOL;
        $rst .= str_repeat(self::HEADER_CHARS[$level], strlen($headline)) . self::EOL;
        return $rst;
    }
}
