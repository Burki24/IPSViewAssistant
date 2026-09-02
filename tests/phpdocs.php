<?php

declare(strict_types=1);

function assertDocumentation(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * Returns the PHPDoc block attached directly to a method declaration.
 *
 * @param list<array{0:int,1:string,2:int}|string> $tokens Parsed PHP token stream.
 * @param int                                      $functionIndex Index of the T_FUNCTION token.
 *
 * @return ?string Direct method PHPDoc or null when the declaration is undocumented.
 */
function methodDocComment(array $tokens, int $functionIndex): ?string
{
    $allowedModifiers = [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_FINAL, T_ABSTRACT];
    for ($index = $functionIndex - 1; $index >= 0; $index--) {
        $token = $tokens[$index];
        if (is_array($token) && $token[0] === T_WHITESPACE) {
            continue;
        }
        if (is_array($token) && in_array($token[0], $allowedModifiers, true)) {
            continue;
        }

        return is_array($token) && $token[0] === T_DOC_COMMENT ? $token[1] : null;
    }

    return null;
}

/**
 * Verifies complete PHPDoc contracts for all named methods in one source file.
 *
 * @param string $path Absolute source-file path.
 *
 * @return void
 */
function assertMethodDocumentation(string $path): void
{
    $source = (string) file_get_contents($path);
    $tokens = token_get_all($source);
    $tokenCount = count($tokens);

    for ($index = 0; $index < $tokenCount; $index++) {
        $token = $tokens[$index];
        if (!is_array($token) || $token[0] !== T_FUNCTION) {
            continue;
        }

        $cursor = $index + 1;
        while ($cursor < $tokenCount) {
            $candidate = $tokens[$cursor];
            if (is_array($candidate) && $candidate[0] === T_WHITESPACE) {
                $cursor++;
                continue;
            }
            if ($candidate === '&') {
                $cursor++;
                continue;
            }
            break;
        }
        if ($cursor >= $tokenCount || !is_array($tokens[$cursor]) || $tokens[$cursor][0] !== T_STRING) {
            continue;
        }

        $methodName = (string) $tokens[$cursor][1];
        $methodDoc = methodDocComment($tokens, $index);
        assertDocumentation(
            is_string($methodDoc),
            basename($path) . '::' . $methodName . ' must have a PHPDoc block.'
        );

        while ($cursor < $tokenCount && $tokens[$cursor] !== '(') {
            $cursor++;
        }
        $parameters = [];
        $depth = 0;
        for (; $cursor < $tokenCount; $cursor++) {
            $candidate = $tokens[$cursor];
            if ($candidate === '(') {
                $depth++;
                continue;
            }
            if ($candidate === ')') {
                $depth--;
                if ($depth === 0) {
                    $cursor++;
                    break;
                }
                continue;
            }
            if ($depth === 1 && is_array($candidate) && $candidate[0] === T_VARIABLE) {
                $parameters[] = substr((string) $candidate[1], 1);
            }
        }

        foreach ($parameters as $parameter) {
            assertDocumentation(
                preg_match('/@param\s+[^\r\n]*\$' . preg_quote($parameter, '/') . '\b/', $methodDoc) === 1,
                basename($path) . '::' . $methodName . ' must document $' . $parameter . '.'
            );
        }

        $signatureTail = '';
        for (; $cursor < $tokenCount; $cursor++) {
            $candidate = $tokens[$cursor];
            if ($candidate === '{' || $candidate === ';') {
                break;
            }
            $signatureTail .= is_array($candidate) ? $candidate[1] : $candidate;
        }
        if (preg_match('/:\s*([^\s{]+)/', $signatureTail, $returnType) === 1) {
            $declaredReturnType = strtolower(trim($returnType[1]));
            if ($declaredReturnType !== 'void') {
                assertDocumentation(
                    str_contains($methodDoc, '@return'),
                    basename($path) . '::' . $methodName . ' must document its return value.'
                );
            }
        }
    }
}

$root = dirname(__DIR__);
foreach ([
    $root . '/IPSView Assistant/module.php',
    $root . '/libs/IPSViewSharedStyleIntegration.php',
    $root . '/libs/IPSViewThemePreview.php'
] as $sourceFile) {
    assertDocumentation(is_file($sourceFile), 'Documentation source file is missing: ' . $sourceFile);
    assertMethodDocumentation($sourceFile);
}

$rootReadme = (string) file_get_contents($root . '/README.md');
$moduleReadme = (string) file_get_contents($root . '/IPSView Assistant/README.md');
assertDocumentation(
    str_contains($rootReadme, '109 nativen IPSView-Farbfelder in 15 Gruppen')
        && str_contains($rootReadme, 'Abweichend')
        && str_contains($rootReadme, '`ColorView`')
        && str_contains($rootReadme, '`ColorPage`'),
    'The root README must document the native IPSView color configuration contract.'
);
assertDocumentation(
    str_contains($moduleReadme, '109 nativen IPSView-Farbfelder in 15 Gruppen')
        && str_contains($moduleReadme, '**Abweichend**')
        && str_contains($moduleReadme, '`ColorView`')
        && str_contains($moduleReadme, '`ColorPage`')
        && str_contains($moduleReadme, '`IPSViewStyleConfigurationHelper`'),
    'The module README must document native color inheritance, overrides and the shared configuration helper.'
);

echo "IPSView Assistant documentation contract passed.\n";
