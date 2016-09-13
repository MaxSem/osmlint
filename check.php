<?php

namespace OsmLint;

require_once( __DIR__ . '/vendor/autoload.php' );

ini_set( 'memory_limit', '512M' );

$env = new Environment;
$pool = new ConnectionPool( $env );
$checker = new Checker( $env, $pool );
$runner = new CheckRunner( $checker );

$errors = $runner->checkDump( 'dump.txt' );

echo json_encode( $errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
echo "\n";
