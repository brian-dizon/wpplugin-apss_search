<?php
/**
 * Plugin Name: APSS Search Overlay
 * Description: A high-performance, secure, and professional live search overlay for All-Pumps.
 * Version: 1.3.0
 * Author: Brian Dizon
 * Text Domain: apss-search-overlay
 * 
 * @package APSS_Search
 */

namespace APSS_Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants
define( 'APSS_SEARCH_PATH', plugin_dir_path( __FILE__ ) );
define( 'APSS_SEARCH_URL', plugin_dir_url( __FILE__ ) );

/**
 * Basic PSR-4 style autoloader for the APSS_Search namespace
 */
spl_autoload_register( function ( $class ) {
	$prefix = 'APSS_Search\\';
	$base_dir = APSS_SEARCH_PATH . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . 'class-' . strtolower( str_replace( '_', '-', $relative_class ) ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

/**
 * Main Plugin Bootstrap Class
 */
class Plugin {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init_components();
	}

	private function init_components() {
		add_action( 'init', function() {
			add_post_type_support( 'page', 'excerpt' );
		} );
		
		( new Settings() )->init();
		( new API() )->init();
		( new Assets() )->init();
		( new UI() )->init();
	}
}

// Start the plugin
Plugin::get_instance();