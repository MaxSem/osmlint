<?php

namespace OsmLint;

class Environment {
    private $pgSettings;
    private $mysqlSettings;
    private $wikiLists = [];

    public function getPostgresSettings() {
        if ( !$this->pgSettings ) {
            $file = getenv( 'HOME' ) . '/.pg_credentials';
            $data = parse_ini_file( $file, true );
            $this->pgSettings = $data['osmlint'];
        }

        return $this->pgSettings;
    }

    public function getMysqlSettings() {
        if ( !$this->mysqlSettings ) {
            $file = getenv( 'HOME' ) . '/replica.my.cnf';
            $this->mysqlSettings = parse_ini_file( $file );
        }

        return $this->mysqlSettings;
    }

    public function getWikiList( $name ) {
        if ( !isset( $this->wikiLists[$name] ) ) {
            ini_set( 'user_agent', 'https://tools.wmflabs.org/osmlint/' );
            $url = "https://raw.githubusercontent.com/wikimedia/operations-mediawiki-config/master/dblists/$name.dblist";
            $output = trim( file_get_contents( $url ) );
            $this->wikiLists[$name] = explode( "\n", $output );
        }

        return $this->wikiLists[$name];
    }
}
