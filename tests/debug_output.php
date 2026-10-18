<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

/** Minimal offline kernel for actual public Assistant entry points. */
class IPSModuleStrict
{
    public int $InstanceID = 5000;
    public array $debugMessages = [];
    public bool $failDebug = false;

    public function __call(string $name, array $arguments): mixed
    {
        return $name === 'Translate' ? $arguments[0] : null;
    }

    public function Create(): void
    {
    }

    protected function SendDebug(string $message, string $data, int $format): bool
    {
        if ($this->failDebug) {
            throw new RuntimeException('Synthetic debug failure.');
        }
        $this->debugMessages[] = [$message, json_decode($data, true, 512, JSON_THROW_ON_ERROR)];
        return true;
    }
}

require_once dirname(__DIR__) . '/IPSView Assistant/module.php';
$instance = new IPSViewAssistant();
$instance->Create();
$firstOperation = $instance->debugMessages[0][1]['Vorgang'];
$instance->debugMessages = [];
$result = $instance->ImportStyleProfileFile('private-document-sentinel');
assertTest(is_string($result) && $result !== '', 'Rejected imports must preserve the public text response.');
assertTest(!str_contains(json_encode($instance->debugMessages), 'private-document-sentinel'), 'Imported document content must never reach debug output.');
assertTest($instance->debugMessages[0][1]['Vorgang'] !== $firstOperation, 'New outer calls need a new operation identity.');
$starts = [];
$hasError = false;
foreach ($instance->debugMessages as [$category, $entry]) {
    assertTest(preg_match('/:[1-9][0-9]*$/', $category) === 1, 'Source locations must contain an automatic line number.');
    $key = $entry['Vorgang'] . ':' . $entry['Aufruf'];
    if (str_contains($category, ' | START | ')) {
        $starts[$key] = true;
    } elseif (str_contains($category, ' | ENDE | ')) {
        assertTest(isset($starts[$key]), 'End must identify a matching start.');
        unset($starts[$key]);
    }
    $hasError = $hasError || str_contains($category, ' | FEHLER | ');
}
assertTest($starts === [] && $hasError, 'Rejected imports need balanced calls and a safe error explanation.');
$instance->debugMessages = [];
$adapter = new ReflectionMethod($instance, 'SendDebug');
$adapter->invoke($instance, 'LegacyHelper', 'private-document-sentinel', 0);
assertTest(!str_contains(json_encode($instance->debugMessages), 'private-document-sentinel'), 'Legacy helper errors must be protected by the module adapter.');
assertTest(count($instance->debugMessages) === 3, 'Standalone legacy helper diagnostics need a balanced safe envelope.');
$instance->failDebug = true;
assertTest($instance->ImportStyleProfileFile('private-document-sentinel') === $result, 'Debug failure must preserve import behavior.');
echo "Assistant debug lifecycle, import rejection, privacy and logging-failure checks passed.\n";
