<?php

namespace OsmLint;

class Checker {
    private $environment, $existenceCheck;

    private static $checks = [
        'checkWikidataLink',
        'checkWikipediaLink',
        'checkWpWd',
    ];

    private static $wikiAliases = [
        'be-tarask' => 'be-x-old',
    ];

    public function __construct( Environment $env ) {
        $this->environment = $env;
        $this->existenceCheck = new WikidataExistenceChecker( $env );
    }

    public function check( $object ) {
        $errors = [];
        foreach ( self::$checks as $check ) {
            $result = $this->$check( $object );
            if ( $result ) {
                $errors[$result][] = $object;
            }
        }

        return $errors;
    }

    public function finalizeChecks() {
        $this->existenceCheck->flush();
        return $this->existenceCheck->results;
    }

    public function checkWikidataLinkQuick( $object ) {
		if ( $object->wikidata === null ) {
			return false;
		}
		if ( preg_match( '/;/', $object->wikidata ) ) {
			return 'Wikidata field contains multiple values';
		}
		if ( !preg_match( '/^Q\d+$/', $object->wikidata ) ) {
			return 'Invalid Wikidata entity ID';
		}

		return null;
	}

    private function checkWikidataLink( $object ) {
		$res = $this->checkWikidataLinkQuick( $object );

		if ( $res === null ) {
			$this->existenceCheck->add( $object );
		}

        return false;
    }

    public function checkWikipediaLinkQuick( $object ) {
		if ( $object->wikipedia === null ) {
			return false;
		}

		if ( preg_match( '#^https?://\w+\.wikipedia\.org/wiki/#', $object->wikipedia ) ) {
			return 'Wikipedia tag contains a link';
		}

		if ( preg_match( '#^https?://#', $object->wikipedia ) ) {
			return 'Wikipedia tag contains a non-Wikipedia link';
		}

		if ( preg_match( '/;/', $object->wikipedia ) ) {
			return 'Wikipedia field contains multiple values';
		}

		$parts = explode( ':', $object->wikipedia, 2 );
		if ( count( $parts ) < 2 ) {
			return "Wikipedia tag contains no wiki prefix";
		}
		$wiki = isset( self::$wikiAliases[ $parts[0] ] )
			? self::$wikiAliases[ $parts[0] ]
			: $parts[0];
		$wiki = str_replace( '-', '_', $wiki );
		$wiki = "{$wiki}wiki";
		$wikis = $this->environment->getWikiList( 'wikipedia' );
		if ( !in_array( $wiki, $wikis ) ) {
			return 'Wikipedia tag has an invalid wiki prefix';
		}

		return null;
	}

    private function checkWikipediaLink( $object ) {
		$res = $this->checkWikipediaLinkQuick( $object );

		if ( $res === null ) {
			// @todo:
		}

        return false;
    }

    private function checkWpWd( $object ) {
        if ( $object->wikipedia && !$object->wikidata ) {
            return 'Object links to Wikipedia but not Wikidata';
        }
        if ( !$object->wikipedia && $object->wikidata ) {
            return 'Object links to Wikidata but not Wikipedia';
        }

        return false;
    }
}
