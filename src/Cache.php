<?php

namespace OsmLint;

class Cache {
    private $directory;
    private $data = [];

    public function __construct( $cacheDirectory ) {
        $this->directory = $cacheDirectory;
    }

    public function set( $key, $data ) {
        $this->data[$key] = $data;
        $fileName = $this->fileName( $key );
        $json = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        file_set_contents( $fileName, $json );
    }

    public function get( $key, $expiryMin, $callback ) {
        if ( !$this->data[$key] ) {
            $fileName = $this->fileName( $key );

            $mtime = stat()
            $data = @file_get_contents( $fileName );
            if ( !$data ) {
                $data = call_user_func( $callback );
            } else {
                $data = json_decode( $data );
            }
            $this->data[$key] = $data;
        }

        return $this->data[$key];
    }

    private function fileName( $key ) {
        return "{$this->directory}/{$key}.json";
    }
}
