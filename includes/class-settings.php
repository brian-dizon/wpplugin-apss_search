<?php
namespace APSS_Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the plugin settings page under Tools.
 */
class Settings {
	private $option_name = 'apss_search_settings';

	public function init() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_settings_page() {
		add_submenu_page(
			'tools.php',
			__( 'Search Overlay Settings', 'apss-search-overlay' ),
			__( 'Search Overlay', 'apss-search-overlay' ),
			'manage_options',
			'apss-search-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting( $this->option_name, $this->option_name, array( $this, 'sanitize_settings' ) );

		add_settings_section(
			'apss_search_main_section',
			__( 'General Settings', 'apss-search-overlay' ),
			null,
			'apss-search-settings'
		);

		add_settings_field(
			'searchable_post_types',
			__( 'Searchable Post Types', 'apss-search-overlay' ),
			array( $this, 'post_types_callback' ),
			'apss-search-settings',
			'apss_search_main_section'
		);

		add_settings_field(
			'overlay_title',
			__( 'Overlay Title', 'apss-search-overlay' ),
			array( $this, 'overlay_title_callback' ),
			'apss-search-settings',
			'apss_search_main_section'
		);

		add_settings_field(
			'show_trigger_icon',
			__( 'Show Default Icon', 'apss-search-overlay' ),
			array( $this, 'show_icon_callback' ),
			'apss-search-settings',
			'apss_search_main_section'
		);

		add_settings_section(
			'apss_search_usage_section',
			__( 'How to Use', 'apss-search-overlay' ),
			array( $this, 'usage_section_callback' ),
			'apss-search-settings'
		);
	}

	public function usage_section_callback() {
		echo '<p>' . esc_html__( 'There are two ways to trigger the search overlay:', 'apss-search-overlay' ) . '</p>';
		echo '<ul class="ul-disc">';
		echo '<li><strong>' . esc_html__( 'Shortcode:', 'apss-search-overlay' ) . '</strong> ' . esc_html__( 'Place', 'apss-search-overlay' ) . ' <code>[apss_search_trigger]</code> ' . esc_html__( 'anywhere in your content or header.', 'apss-search-overlay' ) . '</li>';
		echo '<li><strong>' . esc_html__( 'Custom Class:', 'apss-search-overlay' ) . '</strong> ' . esc_html__( 'Add the class', 'apss-search-overlay' ) . ' <code>apss-trigger</code> ' . esc_html__( 'to any link, button, or image.', 'apss-search-overlay' ) . '</li>';
		echo '</ul>';
		echo '<p><span class="description">' . esc_html__( 'Note: To ensure the search overlay stays below your header, set your header\'s z-index to 1000. The overlay is set to 999.', 'apss-search-overlay' ) . '</span></p>';
	}

	public function sanitize_settings( $input ) {
		$new_input = array();
		
		if ( isset( $input['searchable_post_types'] ) && is_array( $input['searchable_post_types'] ) ) {
			$new_input['searchable_post_types'] = array_map( 'sanitize_text_field', $input['searchable_post_types'] );
		} else {
			$new_input['searchable_post_types'] = array();
		}

		$new_input['overlay_title'] = sanitize_text_field( $input['overlay_title'] );
		$new_input['show_trigger_icon'] = isset( $input['show_trigger_icon'] ) ? 1 : 0;

		return $new_input;
	}

	public function post_types_callback() {
		$options = get_option( $this->option_name );
		$selected = isset( $options['searchable_post_types'] ) ? $options['searchable_post_types'] : array( 'post', 'page', 'portfolio', 'product' );
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		foreach ( $post_types as $post_type ) {
			if ( $post_type->name === 'attachment' ) continue;
			printf(
				'<label style="display:block;margin-bottom:5px;"><input type="checkbox" name="%1$s[searchable_post_types][]" value="%2$s" %3$s> %4$s</label>',
				esc_attr( $this->option_name ),
				esc_attr( $post_type->name ),
				checked( in_array( $post_type->name, $selected ), true, false ),
				esc_html( $post_type->label )
			);
		}
	}

	public function overlay_title_callback() {
		$options = get_option( $this->option_name );
		$value = isset( $options['overlay_title'] ) ? $options['overlay_title'] : __( 'Search for products', 'apss-search-overlay' );
		printf(
			'<input type="text" name="%s[overlay_title]" value="%s" class="regular-text">',
			esc_attr( $this->option_name ),
			esc_attr( $value )
		);
	}

	public function show_icon_callback() {
		$options = get_option( $this->option_name );
		$checked = isset( $options['show_trigger_icon'] ) ? $options['show_trigger_icon'] : 1;
		printf(
			'<input type="checkbox" name="%s[show_trigger_icon]" value="1" %s>',
			esc_attr( $this->option_name ),
			checked( 1, $checked, false )
		);
	}

	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( $this->option_name );
				do_settings_sections( 'apss-search-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public static function get_setting( $key, $default = null ) {
		$options = get_option( 'apss_search_settings' );
		return isset( $options[$key] ) ? $options[$key] : $default;
	}
}
