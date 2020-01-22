<?php

define('APP_DIR', __DIR__.'/app');
define('RUNTIME_DIR', __DIR__.'/app/runtime');
define('PROJECT_DIR', __DIR__);

require "vendor/autoload.php";

/** @noinspection PhpUnhandledExceptionInspection */
(new app\App())->run();

