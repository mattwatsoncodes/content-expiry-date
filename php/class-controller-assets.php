<?php
/**
 * Class Controller_Assets
 *
 * @package mkdo\content_expiry_date
 */

namespace mkdo\content_expiry_date;

/**
 * Sets up the JS and CSS needed for this plugin
 */
class Controller_Assets {

	/**
	 * Options prefix
	 *
	 * @var string
	 */
	private $options_prefix;

	/**
	 * Constructor
	 *
	 * @param Options $options The Plugin Options Object.
	 */
	public function __construct( Options $options ) {
		$this->options_prefix = $options->get_options_prefix();
	}

	/**
	 * Do Work
	 */
	public function run() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqeue Scripts
	 */
	public function admin_enqueue_scripts() {

		global $post_id;

		$prefix = $this->options_prefix;

		$enqueued_assets = get_option(
			$prefix . 'enqueue_back_end_assets',
			array(
				'plugin_admin_css',
				//'plugin_admin_editor_css',
				'plugin_admin_js',
			)
		);

		// If no assets are enqueued
		// prevent errors by declaring the variable as an array.
		if ( ! is_array( $enqueued_assets ) ) {
			$enqueued_assets = array();
		}

		/* CSS */
		if ( in_array( 'plugin_admin_css', $enqueued_assets, true ) ) {
			$plugin_css_url = plugins_url( 'css/plugin-admin.css', MKDO_CONTENT_EXPIRY_DATE_ROOT );
			wp_enqueue_style( 'content-expiry-date', $plugin_css_url, array(), '1.0.6' );
		}

		/* JS */
		if ( in_array( 'plugin_admin_js', $enqueued_assets, true ) ) {
			$plugin_js_url  = plugins_url( 'js/plugin-admin.js', MKDO_CONTENT_EXPIRY_DATE_ROOT );
			wp_enqueue_script( 'content-expiry-date', $plugin_js_url, array( 'jquery' ), '1.0.21', true );
		}
	}
}
