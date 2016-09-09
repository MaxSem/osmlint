<?php

namespace OsmLint;

class Mysql {
    public static function connect( $dbName, Environment $environment ) {
        $settings = $environment->getMysqlSettings();
        return new PDO( "mysql:dbname=$dbName;host=$dbName.wikidb",
            $settings['user'],
            $settings['password']
        );
    }
}
