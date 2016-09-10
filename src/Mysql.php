<?php

namespace OsmLint;

use PDO;

class Mysql {
    public static function connect( $dbName, Environment $environment ) {
        $settings = $environment->getMysqlSettings();
        return new PDO( "mysql:dbname=$dbName_p;host=$dbName.labsdb",
            $settings['user'],
            $settings['password']
        );
    }
}
