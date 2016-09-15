<?php

namespace OsmLint;

class ResultSet {
	const MAX_CATEGORY_SIZE = 10000;

    private $results = [];

    public function getResults() {
        return array_map(
            function( $list ) {
                return array_slice( $list, 0, self::MAX_CATEGORY_SIZE );
            },
            $this->results
        );
    }

    public function add( $category, $object ) {
        $this->results[$category][] = $object;
    }
}
