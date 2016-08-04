<?php

require_once( 'vendor/autoload.php' );

$settings = new OsmLint\DbSettings;
$dumper = new OsmLint\PgDumper( $settings );

$dumper->dumpToFile( 'dump.txt' );
