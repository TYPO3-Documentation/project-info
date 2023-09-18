<?php

declare(strict_types=1);

namespace T3docs\ProjectInfo\Utilities;

class RenderRstUtility
{
    public const EOL = "\n";
    public const HEADER_CHARS = ['=', '=', '-', '~', '\''];

    public static function renderHeadLine(string $headline, int $level = 0): string
    {
        $rst = '';
        if ($level === 0) {
            $rst .= str_repeat(self::HEADER_CHARS[$level], strlen($headline)) . self::EOL;
        }
        $rst .= $headline . self::EOL;
        $rst .= str_repeat(self::HEADER_CHARS[$level], strlen($headline)) . self::EOL;
        return $rst;
    }

    public static function escape(string $string): string
    {
        $string = preg_replace('/[\\n\\r]/', '|', $string);
        $string = preg_replace('/[\\\\|`=*<>]/', '\\${1}', $string);
        return $string;
    }

    public static function indent($inputString, $numSpaces = 4): string
    {
        // Create the indentation string with the desired number of spaces
        $indentation = str_repeat(' ', $numSpaces);

        // Split the input string into an array of lines
        $lines = explode("\n", $inputString);

        // Add the indentation to each line
        foreach ($lines as &$line) {
            $line = $indentation . $line;
        }

        // Join the lines back together to form the indented string
        $indentedString = implode("\n", $lines);

        return $indentedString;
    }

    public static function wrapTextAtMaxLength($text, $maxLength = 80): string
    {
        // Use wordwrap to wrap lines at the specified maximum length
        $wrappedText = wordwrap($text, $maxLength, "\n", true);

        return $wrappedText;
    }
}
