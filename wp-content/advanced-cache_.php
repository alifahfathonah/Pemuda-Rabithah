<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

define( 'WP_ROCKET_ADVANCED_CACHE', true );
$rocket_cache_path = '/nas/content/live/avadalive/wp-content/cache/wp-rocket/';
$rocket_config_path = '/nas/content/live/avadalive/wp-content/wp-rocket-config/';

if ( file_exists( '/nas/content/live/avadalive/wp-content/plugins/wp-rocket/inc/front/process.php' ) ) {
	include( '/nas/content/live/avadalive/wp-content/plugins/wp-rocket/inc/front/process.php' );
} else {
	define( 'WP_ROCKET_ADVANCED_CACHE_PROBLEM', true );
}