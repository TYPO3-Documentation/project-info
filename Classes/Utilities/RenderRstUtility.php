<?php

declare(strict_types=1);

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

namespace T3docs\ProjectInfo\Utilities;

class RenderRstUtility
{
    final public const EOL = "\n";
    final public const HEADER_CHARS = ['=', '=', '-', '~', '\''];

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

    public static function indent(string $inputString, int $numSpaces = 4): string
    {
        // Create the indentation string with the desired number of spaces
        $indentation = str_repeat(' ', $numSpaces);

        // Split the input string into an array of lines
        $lines = explode("\n", (string)$inputString);

        // Add the indentation to each line
        foreach ($lines as &$line) {
            $line = $indentation . $line;
        }

        // Join the lines back together to form the indented string
        $indentedString = implode("\n", $lines);

        return $indentedString;
    }

    public static function wrapTextAtMaxLength(string $text, int $maxLength = 80): string
    {
        // Use wordwrap to wrap lines at the specified maximum length
        $wrappedText = wordwrap((string)$text, $maxLength, "\n", false);

        return $wrappedText;
    }
}
