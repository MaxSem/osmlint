<?php

namespace OsmLint;

use Exception;

class ResultSet {
	const MAX_CATEGORY_SIZE = 10000;

	private static $errorTypes = [
		'wp-invalid-prefix' => [
			'name' => 'Wikipedia tag has an invalid wiki prefix',
			'show-wp' => false,
		],
		"Invalid Wikidata entity ID",
		"Wikipedia tag contains a link",
		"Wikipedia tag contains no wiki prefix",
		"Wikipedia tag contains a non-Wikipedia link",
		"Wikidata item is a redirect",
		"Wikidata item doesn't exist"
	];

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
//		if ( !isset( self::$errorTypes[$category] ) ) {
//			throw new Exception( "Unrecognized category name: $category" );
//		}
        $this->results[$category][] = $object;
    }

    public function addMulti( array $results ) {
        foreach ( $results as $category => $objects ) {
            foreach ( $objects as $object ) {
                $this->add( $category, $object );
            }
        }
    }

    public static function getCategoryProperties( $name ) {
		return self::$errorTypes;
	}
}
