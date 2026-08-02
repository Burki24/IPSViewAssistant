<?php

declare(strict_types=1);

/**
 * Fails one repository test with a readable message.
 */
function failTest(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

/**
 * Asserts a repository test condition.
 */
function assertTest(bool $condition, string $message): void
{
    if (!$condition) {
        failTest($message);
    }
}
