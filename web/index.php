<?php

$app = require_once dirname(__DIR__) . '/app/app.php';

if (is_cli()) {
    echo "This script can only be runned in a web context";
    return false;
}

$app->run();
