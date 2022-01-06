<?php

use function \WP_Forge\Helpers\dataGet;
use function \WP_Forge\Helpers\dataSet;

function data_get( $data, $key, $default = null ) {
	return dataGet( $data, $key, $default );
}

function data_set( &$target, $key, $value, $overwrite = true ) {
	return dataSet( $target, $key, $value, $overwrite );
}
