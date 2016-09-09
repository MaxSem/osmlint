<?php

namespace OsmLint;

class WikidataExistenceChecker extends QueryAccumulator {
    public function __construct( Environment $environment ) {
        parent::__construct( 'wikidatawiki', $environment );
    }

    public function flush() {
        if ( count( $this->data ) == 0 ) {
            return;
        }

        $titles = [];
        foreach ( $this->data as $object ) {
            $titles[$object->wikidata] = $object;
        }

        $db = $this->connect();

        $query = $db->prepare( 'SELECT page_title, page_is_redirect
FROM page
WHERE
    page_namespace=0
    AND page_title IN (:titles)'
        );

        $query->execute( ['titles' => array_keys( $titles )] );
        foreach ( $query->fetchAll() as $row ) {
            $title = $row['page_title'];
            if ( $row['page_is_redirect'] ) {
                $this->results['Wikidata item is a redirect'][] = $titles[$title];
            }
            unset( $titles[$title] );
        }

        foreach ( $titles as $object ) {
            $this->results["Wikidata item doesn't exist"][] = $object;
        }

        $this->data = [];
    }
}
