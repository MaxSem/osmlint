<?php

namespace OsmLint;

class PageExistenceChecker extends QueryAccumulator  {
	protected $errors = [
		'redirect' => 'Page is a redirect',
		'nonexistent' => "Page doesn't exist",
	];

	protected function flushInternal() {
        if ( count( $this->data ) == 0 ) {
            return;
        }

        $titles = [];
		foreach ( $this->data as $title => $object ) {
			$titles[$this->titleDbForm( $title )] = $object;
		}

        $db = $this->connect();
        $s = implode( ', ', array_map( [ $db, 'quote' ], array_keys( $titles ) ) );

        $sql = "SELECT page_title, page_is_redirect
FROM page
WHERE
    page_namespace=0
    AND page_title IN ($s)";

        $query = $db->query( $sql );
        foreach ( $query->fetchAll() as $row ) {
            $title = $row['page_title'];
            if ( $row['page_is_redirect'] ) {
                $this->resultSet->add( $this->errors['redirect'], $titles[$title] );
            }
            unset( $titles[$title] );
        }

        foreach ( $titles as $object ) {
            $this->resultSet->add( $this->errors['nonexistent'], $object );
        }
	}
}
