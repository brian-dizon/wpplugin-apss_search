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
			'permission_callback' => function( $request ) {
				// Verify the nonce passed in the X-WP-Nonce header
				$nonce = $request->get_header( 'X-WP-Nonce' );
				return wp_verify_nonce( $nonce, 'wp_rest' );
			},
		) );
	}

	public function handle_search( $request ) {
		$term = sanitize_text_field( $request->get_param( 'term' ) );

		if ( empty( $term ) ) {
			return new \WP_REST_Response( array(), 200 );
		}

		$post_types = Settings::get_setting( 'searchable_post_types', array( 'post', 'page', 'portfolio', 'product' ) );
		
		// Improve cache privacy and ensure cache clears on settings change
		$user_status = is_user_logged_in() ? 'logged_in' : 'guest';
		$cache_key   = 'apss_search_' . md5( $term . $user_status . serialize( $post_types ) );
		$results     = get_transient( $cache_key );

		if ( false === $results ) {
			$query = new \WP_Query( array(
				'post_type'           => $post_types,
				'posts_per_page'      => 8,
				's'                   => $term,
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			) );

			$results = array();

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					global $post;
					$post_type = get_post_type();
					$post_id   = get_the_ID();
					
					if ( ! isset( $results[$post_type] ) ) {
						$results[$post_type] = array();
					}

					// Optimized Excerpt Fetching - Bypass heavy filter chain
					$excerpt = $post->post_excerpt; // Use manual excerpt first
					
					if ( empty( $excerpt ) ) {
						// Fallback to raw content if excerpt is empty
						$excerpt = $post->post_content;
					}

					if ( empty( $excerpt ) ) {
						// Fallback to Oxygen Builder meta if still empty
						$excerpt = get_post_meta( $post_id, 'ct_builder_shortcodes', true );
					}

					// Clean and trim the excerpt
					$excerpt = wp_strip_all_tags( strip_shortcodes( $excerpt ) );
					$excerpt = wp_trim_words( $excerpt, 20 );

					$results[$post_type][] = array(
						'id'        => $post_id,
						'title'     => esc_html( get_the_title() ),
						'permalink' => esc_url( get_the_permalink() ),
						'image'     => esc_url( get_the_post_thumbnail_url( $post_id, 'medium' ) ),
						'excerpt'   => $excerpt,
						'date'      => get_the_date(),
					);
				}
				wp_reset_postdata();
			}

			set_transient( $cache_key, $results, HOUR_IN_SECONDS );
		}

		return new \WP_REST_Response( $results, 200 );
	}
}
