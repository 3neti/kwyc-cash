<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class RegexBuilder
{
    protected array $options;

    public function __construct(array $options = [])
    {
        $options += [
            'START_SYMBOL' => '{',
            'END_SYMBOL' => '}',
            'SEPARATOR_SYMBOL' => ' ', // Space separator for SMS commands
            'OPT_SYMBOL' => '?',
            'REGEX_DELIMITER' => '/',
            'REGEX_MODIFIER' => 'i', // Case-insensitive for SMS
            'DEFAULT_REGEX_EXP' => '[^\s]+', // Default: Capture one word
        ];

        $this->options = $options;
    }

    /**
     * Convert an SMS pattern into a regex.
     *
     * @param string $pattern The pattern string.
     * @return string The converted regex.
     */
    public function getRegex(string $pattern): string
    {
        $regex = [];
        $tokens = $this->getTokens($pattern);
        $delimiter = $this->options['REGEX_DELIMITER'];
        $modifier = $this->options['REGEX_MODIFIER'];

        $sep = '\s+'; // Explicit space separator

        foreach ($tokens as $t) {
            switch ($t['type']) {
                case 'separator':
                    $regex[] = $sep;
                    break;

                case 'variable':
                    $varName = $t['value'];
                    $pattern = ($varName === 'message') ? '.+' : '[A-Za-z0-9]+';

                    if ($t['opt']) {
                        $regex[] = '(?:' . $sep . '(?P<' . $varName . '>' . $pattern . '))?';
                    } else {
                        $regex[] = '(?P<' . $varName . '>' . $pattern . ')';
                    }
                    break;

                default:
                    $regex[] = preg_quote($t['value'], $delimiter);
                    break;
            }
        }

        return $delimiter . '^' . implode('', $regex) . '$' . $delimiter . $modifier;
    }

    /**
     * Extract named values from a matched regex pattern.
     *
     * @param string $regex The regex pattern.
     * @param string $message The message string.
     * @return array The extracted values.
     */
    public function getValues(string $regex, string $message): array
    {
        if (preg_match($regex, $message, $matches)) {
            return array_map(fn($v) => $v === '' ? null : $v, array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
        }

        return [];
    }

    /**
     * Extract tokens from a pattern.
     *
     * @param string $pattern The pattern string.
     * @return array The extracted tokens.
     */
    protected function getTokens(string $pattern): array
    {
        preg_match_all('/\{(\w+)\??\}/', $pattern, $matches, PREG_OFFSET_CAPTURE);
        $tokens = [];

        foreach ($matches[0] as $index => $match) {
            $tokens[] = ['type' => 'variable', 'value' => $matches[1][$index][0], 'opt' => substr($match[0], -1) === '?'];
        }

        return $tokens;
    }
}
