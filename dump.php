<?php

require_once( __DIR__ . '/vendor/autoload.php' );

$env = new OsmLint\Environment;
$dumper = new OsmLint\PgDumper( $env );

$dumper->dumpToFile( 'dump.txt' );
