<?php

namespace OsmLint;

class CheckRunner {
    private $checker;

    public function __construct( Checker $checker ) {
        $this->checker = $checker;
    }

    public function checkDump( $fileName ) {
        $file = fopen( $fileName, 'r' );

        while ( ( $line = fgets( $file ) ) !== false ) {
            $object = json_decode( $line );
            $this->checker->check( $object );
        }
        fclose( $file );

        $this->checker->finalizeChecks();
    }
}
