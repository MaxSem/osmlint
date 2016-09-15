<?php

namespace OsmLint;

require_once( __DIR__ . '/vendor/autoload.php' );

ini_set( 'memory_limit', '1G' );

$env = new Environment();
$resultSet = new ResultSet();
$checker = new Checker( $env, $resultSet );
$runner = new CheckRunner( $checker );

$runner->checkDump( 'dump.txt' );
$errors = $resultSet->getResults();

echo json_encode( $errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
echo "\n";
