<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class RegexBuilder
{
    protected array $options;
    protected array $tokens = [];

    const CAPTURE_LEFT = 1;
    const CAPTURE_RIGHT = 2;
    const ALLOW_OPT_TRAIL = 4;

    const START_SYMBOL = 0;
    const END_SYMBOL = 1;
    const SEPARATOR_SYMBOL = 2;
    const OPT_SYMBOL = 3;
    const CAPTURE_MODE = 4;
    const REGEX_DELIMITER = 5;
    const REGEX_MODIFIER = 6;
    const DEFAULT_REGEX_EXP = 7;
    const ASSIGN_SYMBOL = 8;

    public function __construct(array $options = [])
    {
        $options += [
            self::START_SYMBOL => '{',
            self::END_SYMBOL => '}',
            self::SEPARATOR_SYMBOL => ' ', // Space for SMS commands
            self::OPT_SYMBOL => '?',
            self::CAPTURE_MODE => self::CAPTURE_LEFT | self::ALLOW_OPT_TRAIL,
            self::REGEX_DELIMITER => '/',
            self::REGEX_MODIFIER => 'i', // Case-insensitive
            self::ASSIGN_SYMBOL => '=',
        ];

        if (!isset($options[self::DEFAULT_REGEX_EXP])) {
            $options[self::DEFAULT_REGEX_EXP] = '[^\s]+'; // Default: Capture one word
        }

        $this->options = $options;
    }

    public function getRegex(string $pattern): string
    {
        Log::debug("Converting pattern to regex", ['pattern' => $pattern]);

        $regex = [];
        $tokens = $this->getTokens($pattern);
        $delimiter = $this->options[self::REGEX_DELIMITER];
        $modifier = $this->options[self::REGEX_MODIFIER];
        $default_exp = $this->options[self::DEFAULT_REGEX_EXP];

        $sep = preg_quote($this->options[self::SEPARATOR_SYMBOL], $delimiter);

        foreach ($tokens as $t) {
            switch ($t['type']) {
                case 'separator':
                    $regex[] = $sep;
                    break;
                case 'variable':
                    $pattern = '(?P<' . $t['value'] . '>' . ($t['regex'] ?? ($t['value'] === 'message' ? '.*' : $default_exp)) . ')';
                    if ($t['opt']) {
                        $pattern .= '?';
                    }
                    $regex[] = $pattern;
                    break;
                default:
                    $regex[] = preg_quote($t['value'], $delimiter);
                    break;
            }
        }

        $finalRegex = $delimiter . '^' . implode('', $regex) . '$' . $delimiter . $modifier;
        Log::debug("Generated regex", ['regex' => $finalRegex]);

        return $finalRegex;
    }

    public function getValues(string $regex, string $message): array
    {
        if (preg_match($regex, $message, $matches)) {
            Log::info("Message matched regex", ['message' => $message, 'matches' => $matches]);

            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        Log::warning("No match found for message", ['message' => $message, 'regex' => $regex]);
        return [];
    }

    public function matches(string $regex, string $message): bool
    {
        return (bool) preg_match($regex, $message);
    }

    protected function getTokens(string $pattern): array
    {
        preg_match_all('/\{(\w+)\??\}/', $pattern, $matches, PREG_OFFSET_CAPTURE);

        $tokens = [];
        $lastIndex = 0;

        foreach ($matches[0] as $index => $match) {
            if ($match[1] > $lastIndex) {
                $tokens[] = ['type' => 'data', 'value' => substr($pattern, $lastIndex, $match[1] - $lastIndex)];
            }

            $tokens[] = [
                'type' => 'variable',
                'value' => $matches[1][$index][0],
                'opt' => substr($match[0], -1) === '?'
            ];

            $lastIndex = $match[1] + strlen($match[0]);
        }

        if ($lastIndex < strlen($pattern)) {
            $tokens[] = ['type' => 'data', 'value' => substr($pattern, $lastIndex)];
        }

        return $tokens;
    }
}
