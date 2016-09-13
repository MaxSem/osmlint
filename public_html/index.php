<?php

namespace OsmLint;

require_once( __DIR__ . '/../vendor/autoload.php' );

$env = new Environment();
$ui = new UI( $_GET, $env );

$ui->show();
