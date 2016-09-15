<?php

namespace OsmLint;

class Checker {
	/** @var Environment */
    private $environment;
	/** @var WikidataExistenceChecker */
	private $wikidataCheck;
	/** @var ExistenceHolder */
	private $wikipediaCheck;
	/** @var ResultSet */
	private $resultSet;

    private static $checks = [
        'checkWikidataLink',
        'checkWikipediaLink',
        'checkWpWd',
    ];

    private static $wikiAliases = [
        'be-tarask' => 'be-x-old',
    ];

    public function __construct( Environment $env, ResultSet $resultSet ) {
        $this->environment = $env;
        $this->wikidataCheck = new WikidataExistenceChecker( $env, $resultSet );
		$this->wikipediaCheck = new ExistenceHolder( $env, $resultSet );
		$this->resultSet = $resultSet;
    }

    public function check( $object ) {
        foreach ( self::$checks as $check ) {
            $result = $this->$check( $object );
            if ( $result ) {
				$this->resultSet->add( $result, $object );
            }
        }
    }

    public function finalizeChecks() {
        $this->wikidataCheck->flush();
		$this->wikipediaCheck->flush();
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
			$this->wikidataCheck->add( $object );
		}

        return $res;
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

		list( $prefix, , $dbName ) = $this->parseTitle( $object->wikipedia );
		if ( !$prefix ) {
			return "Wikipedia tag contains no wiki prefix";
		}
		$wikis = $this->environment->getWikiList( 'wikipedia' );
		if ( !$dbName || !in_array( $dbName, $wikis ) ) {
			return 'Wikipedia tag has an invalid wiki prefix';
		}

		return null;
	}

	private function parseTitle( $title ) {
		$parts = explode( ':', $title, 2 );
		if ( count( $parts ) < 2 ) {
			return [ null, $title, $null ];
		}
		$wiki = isset( self::$wikiAliases[ $parts[0] ] )
			? self::$wikiAliases[ $parts[0] ]
			: $parts[0];
		$dbName = str_replace( '-', '_', $wiki );
		$dbName = "{$dbName}wiki";

		return [ $wiki, $parts[1], $dbName ];
	}

    private function checkWikipediaLink( $object ) {
		$res = $this->checkWikipediaLinkQuick( $object );

		if ( $res !== null ) {
			// @fixme: double parse
			list( , $title, $dbName ) = $this->parseTitle( $object->wikipedia );
			$this->wikipediaCheck->addTitle( $dbName, $title, $object );
			return $res;
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
