<?php

// load our environment files - used to store credentials & configuration
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

return
    [
        'paths' => [
            'migrations' => 'app/migrations',
            'seeds' => 'app/migrations/seeds',
        ],
        'environments' =>
            [
                'default_database' => 'def',
                'default_migration_table' => 'phinxlog',
                'def' =>
                    [
                        'adapter' => 'mysql',
                        'host' => getenv('MYSQL_HOST'),
                        'name' => getenv('MYSQL_DATABASE'),
                        'user' => getenv('MYSQL_USER'),
                        'pass' => getenv('MYSQL_PASSWORD'),
                        'port' => getenv('MYSQL_PORT'),
                        'charset' => 'utf8',
                        'collation' => 'utf8_unicode_ci',
                    ],
            ],
    ];