<?php

namespace Tests;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Constraint\IsType;

/**
 * Trait to provide fixed assertions that work around PHPUnit's encoding detection bug
 * when testing large HTML responses.
 */
trait HasFixedAssertions
{
    /**
     * Assert that the response contains the given string, with proper UTF-8 handling.
     *
     * This works around PHPUnit's mb_detect_encoding bug on large HTML responses
     * (see: https://github.com/sebastianbergmann/phpunit/issues/5000)
     */
    protected function assertSeeFixed($response, string $value, bool $escape = true): void
    {
        $content = $this->getFixedContent($response);
        $needle = $escape ? e($value) : $value;

        $this->assertStringContainsString($needle, $content,
            "Failed asserting that response contains \"{$value}\".");
    }

    /**
     * Assert that the response does NOT contain the given string.
     */
    protected function assertDontSeeFixed($response, string $value, bool $escape = true): void
    {
        $content = $this->getFixedContent($response);
        $needle = $escape ? e($value) : $value;

        $this->assertStringNotContainsString($needle, $content,
            "Failed asserting that response does not contain \"{$value}\".");
    }

    /**
     * Assert that the response contains the given strings in order.
     */
    protected function assertSeeInOrderFixed($response, array $values, bool $escape = true): void
    {
        $content = $this->getFixedContent($response);
        $position = 0;

        foreach ($values as $value) {
            $needle = $escape ? e($value) : $value;
            $pos = mb_strpos($content, $needle, $position);

            $this->assertNotFalse($pos, "Failed asserting that \"{$value}\" appears in order.");
            $position = $pos + mb_strlen($needle);
        }
    }

    /**
     * Get response content with proper UTF-8 encoding handling.
     *
     * PHPUnit's mb_detect_encoding incorrectly identifies large UTF-8 HTML
     * responses as UTF-16. We force UTF-8 interpretation.
     */
    private function getFixedContent($response): string
    {
        $content = $response->getContent();

        // If content appears to be UTF-16 (starts with BOM or has null bytes every other char),
        // convert it to UTF-8
        if (strlen($content) >= 2) {
            $bom = substr($content, 0, 2);
            if ($bom === "\xFF\xFE" || $bom === "\xFE\xFF") {
                // UTF-16 LE or BE with BOM
                $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16');
            } elseif (preg_match('/^[\x00-\xFF]{2}$/', $bom) === 0 && strpos($content, "\x00") !== false) {
                // Heuristic: lots of null bytes suggests UTF-16
                $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
            }
        }

        // Ensure we return valid UTF-8
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        }

        return $content;
    }
}