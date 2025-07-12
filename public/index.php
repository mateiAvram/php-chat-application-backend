<?php
// public/index.php
require __DIR__ . '/../vendor/autoload.php';

$createApp = require __DIR__ . '/../src/app.php';
$app = $createApp();    // no container → uses default
$app->run();
