<?php
namespace APSS_Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles registration and localization of scripts and styles.
 */
class Assets {
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	public function register_assets() {
		wp_register_style(
			'apss-search-css',
			APSS_SEARCH_URL . 'assets/css/apss-search.css',
			array(),
			'1.0.0'
		);

		wp_register_script(
			'apss-search-js',
			APSS_SEARCH_URL . 'assets/js/apss-search.js',
			array(),
			'1.0.0',
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		wp_localize_script( 'apss-search-js', 'apssSearchData', array(
			'root'   => esc_url_raw( rest_url() ),
			'nonce'  => wp_create_nonce( 'wp_rest' ),
			'labels' => Settings::get_setting( 'post_type_labels', array() ),
		) );
	}

	public static function enqueue() {
		wp_enqueue_style( 'apss-search-css' );
		wp_enqueue_script( 'apss-search-js' );
	}
}
