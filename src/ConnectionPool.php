<?php

namespace OsmLint;

class ConnectionPool {
    private $environment;
    private $pool = [];

    public function __construct( Environment $env ) {
        $this->environment = $env;
    }

    public function getConnection( $dbName ) {
        if ( !isset( $this->pool[$dbName] ) ) {
            $settings = $this->environment->getMysqlSettings();
            $this->pool[$dbName] = new mysqli( "$dbName.wikidb",
                $settings['user'],
                $settings['password'],
                $dbName
            );
        }

        return $this->pool[$dbName];
    }
}
