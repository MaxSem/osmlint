<?php

namespace OsmLint;

class CheckRunner {
    private $checker;

    public function __construct( Checker $checker ) {
        $this->checker = $checker;
    }

    public function checkDump( $fileName ) {
        $lines = file( $fileName );
        //$line = array_slice( $lines, 0, 1000 );

        $errors = [];
        foreach ( $lines as $line ) {
            $object = json_decode( $line );
            $result = $this->checker->check( $object );

            foreach ( $result as $err ) {
                $errors[$err][] = $object;
            }
        }

        return $errors;
    }
}
