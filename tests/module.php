<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$root = dirname(__DIR__);
$moduleSource = file_get_contents($root . '/IPSView Assistant/module.php');
$factorySource = file_get_contents($root . '/libs/IPSViewFactory.php');
$form = json_decode(
    (string) file_get_contents($root . '/IPSView Assistant/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

assertTest(is_string($moduleSource), 'The module source could not be read.');
assertTest(is_string($factorySource), 'The IPSView factory source could not be read.');
assertTest(str_contains($moduleSource, 'extends IPSModuleStrict'), 'The module does not use IPSModuleStrict.');
assertTest(str_contains($moduleSource, 'public function CreateView('), 'The public CreateView method is missing.');
assertTest(str_contains($factorySource, 'IPS_CreateMedia(0)'), 'The factory does not create an IPSView media object.');
assertTest(str_contains($factorySource, 'IPS_SetMediaFile('), 'The factory does not assign a media file.');
assertTest(str_contains($factorySource, 'IPS_SetMediaContent('), 'The factory does not write the IPSView content.');
assertTest(str_contains($factorySource, 'IPS_SendMediaEvent('), 'The factory does not announce the media update.');

$actions = $form['actions'] ?? [];
$button = null;
foreach ($actions as $action) {
    if (($action['type'] ?? '') === 'Button' && ($action['caption'] ?? '') === 'Create View') {
        $button = $action;
        break;
    }
}

assertTest(is_array($button), 'The Create View button is missing from the form.');
assertTest(
    str_contains((string) ($button['onClick'] ?? ''), 'IPSVIEWA_CreateView('),
    'The Create View button does not call the public module method.'
);

echo "IPSView Assistant module tests passed.\n";
