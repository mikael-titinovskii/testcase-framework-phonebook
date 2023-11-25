<?php

const APP_DIR     = __DIR__.'/app';
const RUNTIME_DIR = __DIR__.'/app/runtime';
const PROJECT_DIR = __DIR__;

require "vendor/autoload.php";

/** @noinspection PhpUnhandledExceptionInspection */
(new app\App())->run();

