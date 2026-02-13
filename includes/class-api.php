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
		// Priority 999 to run after security plugins
		add_filter( 'rest_authentication_errors', array( $this, 'bypass_auth_restriction' ), 999 );
	}

	/**
	 * Forcefully bypass global REST API restrictions for our specific namespace.
	 */
	public function bypass_auth_restriction( $result ) {
		if ( true === $result || is_wp_error( $result ) ) {
			if ( strpos( $_SERVER['REQUEST_URI'], 'apss/v1/search' ) !== false ) {
				return null; // Return null to signify NO error
			}
		}
		return $result;
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
					$post_type = get_post_type();
					$post_id   = get_the_ID();
					
					if ( ! isset( $results[$post_type] ) ) {
						$results[$post_type] = array();
					}

					// Robust Excerpt Fetching
					$excerpt = get_the_excerpt();
					
					if ( empty( $excerpt ) ) {
						// Fallback to content if excerpt is empty
						$excerpt = get_the_content();
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
