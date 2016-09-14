<?php

namespace OsmLint;

use PDO;

class PgDumper {
    const BATCH_SIZE = 1000;

    private $environment;

    public function __construct( Environment $env ) {
        $this->environment = $env;
    }

    public function dumpToFile( $fileName ) {
        $file = fopen( $fileName, 'w' );
		$pg = $this->environment->getPostgresSettings();

        $connString = "host={$pg['host']} "
            . "dbname={$pg['database']} "
            . "user={$pg['user']} "
            . "password={$pg['password']}";

        $pg = pg_connect( $connString );

        $this->dumpTable( $pg, $file, 'point' );
        //$this->dumpTable( $pg, $file, 'line' );
        $this->dumpTable( $pg, $file, 'polygon' );

        pg_close( $pg );
    }

    private function dumpTable( $pg, $file, $table ) {
        $id = -10000000000;
        $statement = pg_prepare( $pg, '', "SELECT osm_id, name, tags->'wikipedia' wikipedia, tags->'wikidata' wikidata "
            . "FROM planet_osm_$table "
            . "WHERE ( tags ? 'wikipedia' OR tags ? 'wikidata' ) AND osm_id > $1 ORDER BY osm_id LIMIT $2"
        );
        do {
            $result = pg_execute( $pg, '', [ $id, self::BATCH_SIZE ] );
            while ( $row = pg_fetch_object( $result ) ) {
                $id = $row->osm_id;
                $row->type = $table;
                $json = json_encode( $row, JSON_UNESCAPED_UNICODE );
                fwrite( $file, "$json\n" );
            }
        } while ( pg_num_rows( $result ) > 0 );
    }
}
