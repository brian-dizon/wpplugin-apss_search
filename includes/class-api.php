<?php
namespace APSS_Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles REST API registration and search logic.
 */
class API {
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route( 'apss/v1', '/search', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'handle_search' ),
			'permission_callback' => '__return_true',
		) );
	}

	public function handle_search( $request ) {
		$term = sanitize_text_field( $request->get_param( 'term' ) );

		if ( empty( $term ) ) {
			return new \WP_REST_Response( array(), 200 );
		}

		$cache_key = 'apss_search_' . md5( $term );
		$results   = get_transient( $cache_key );

		if ( false === $results ) {
			$post_types = Settings::get_setting( 'searchable_post_types', array( 'post', 'page', 'portfolio', 'product' ) );
			
			$query = new \WP_Query( array(
				'post_type'      => $post_types,
				'posts_per_page' => 8,
				's'              => $term,
			) );

			$results = array();

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$results[] = array(
						'id'        => get_the_ID(),
						'title'     => esc_html( get_the_title() ),
						'permalink' => esc_url( get_the_permalink() ),
						'image'     => esc_url( get_the_post_thumbnail_url( get_the_ID(), 'medium' ) ),
					);
				}
				wp_reset_postdata();
			}

			set_transient( $cache_key, $results, HOUR_IN_SECONDS );
		}

		return new \WP_REST_Response( $results, 200 );
	}
}
