<?php
namespace APSS_Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the shortcode and overlay template rendering.
 */
class UI {
	public function init() {
		add_shortcode( 'apss_search_trigger', array( $this, 'render_trigger' ) );
		add_action( 'wp_footer', array( $this, 'render_overlay' ) );
	}

	public function render_trigger() {
		Assets::enqueue();
		
		$show_icon = Settings::get_setting( 'show_trigger_icon', 1 );
		
		ob_start();
		?>
		<button class="apss-trigger" aria-label="<?php esc_attr_e( 'Open Search', 'apss-search-overlay' ); ?>">
			<?php if ( $show_icon ) : ?>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="11" cy="11" r="8"></circle>
					<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
				</svg>
			<?php endif; ?>
		</button>
		<?php
		return ob_get_clean();
	}

		public function render_overlay() {

			if ( ! wp_script_is( 'apss-search-js', 'enqueued' ) ) {

				return;

			}

			

									$overlay_title = Settings::get_setting( 'overlay_title', __( 'Search for products', 'apss-search-overlay' ) );

			

									$show_title = Settings::get_setting( 'show_overlay_title', 1 );

			

									$search_placeholder = Settings::get_setting( 'search_placeholder', __( 'Start typing to see products you are looking for.', 'apss-search-overlay' ) );

			

									$font_size = Settings::get_setting( 'input_font_size', 24 );

			

											$max_width = Settings::get_setting( 'results_max_width', 1200 );

			

											?>

			

											<div id="apss-search-overlay" class="apss-search-overlay" style="display: none;">

			

									

			

							

			

								<div class="apss-search-container" style="max-width: <?php echo esc_attr( $max_width ); ?>px;">

			

									<button class="apss-close" aria-label="<?php esc_attr_e( 'Close Search', 'apss-search-overlay' ); ?>">&times;</button>

			

									<div class="apss-search-inner">

			

										<?php if ( $show_title ) : ?>

			

											<h2 class="apss-search-title"><?php echo esc_html( $overlay_title ); ?></h2>

			

										<?php endif; ?>

			

										<div class="apss-search-field-wrapper">

			

					

			

									<input type="text" id="apss-search-input" class="apss-search-input" placeholder="<?php echo esc_attr( $search_placeholder ); ?>" autocomplete="off">

			

									<div class="apss-spinner" style="display: none;"></div>

			

								</div>

			

			
					<div id="apss-search-results" class="apss-search-results"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
