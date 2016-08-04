<?php

namespace OsmLint;

use PDO;

class PgDumper {
    const BATCH_SIZE = 1000;

    private $dbSettings;

    public function __construct( DbSettings $settings ) {
        $this->dbSettings = $settings;
    }

    public function dumpToFile( $fileName ) {
        $file = fopen( $fileName, 'w' );

        $connString = "host={$this->dbSettings->getPostgresHost()} "
            . "dbname={$this->dbSettings->getPostgresDatabase()} "
            . "user={$this->dbSettings->getPostgresUser()} "
            . "password={$this->dbSettings->getPostgresPassword()} ";

        $pg = pg_connect( $connString );

        $this->dumpTable( $pg, $file, 'point' );
        $this->dumpTable( $pg, $file, 'line' );
        $this->dumpTable( $pg, $file, 'polygon' );

        pg_close( $pg );
    }

    private function dumpTable( $pg, $file, $table ) {
        $id = 0;
        $statement = pg_prepare( $pg, '', "SELECT osm_id, tags->'wikipedia', tags->'wikidata' "
            . "FROM planet_osm_$table "
            . "WHERE tags ? 'wikipedia' AND tags->'wikidata' AND osm_id > $1 LIMIT $2"
        );
        do {
            $result = pg_execute( $pg, $statement, [ $id, self::BATCH_SIZE ] );
            foreach ( $result as $row ) {
                $id = $row->osm_id;
                $row->type = $table;
                $json = json_encode( $row, JSON_UNESCAPED_UNICODE );
                fwrite( $file, "$json,\n" );
            }
        } while ( pg_num_rows( $result ) > 0 );
    }
}
