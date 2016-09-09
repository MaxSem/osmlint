<?php

namespace OsmLint;

class LintResults {
    public $results = [];

    public function add( $category, $object ) {
        $this->results[$category][] = $object;
    }

    public function addMulti( array $results ) {
        foreach ( $results as $category => $objects ) {
            foreach ( $objects as $object ) {
                $this->add( $category, $object );
            }
        }
    }
}
