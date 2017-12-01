<?php
/**
 * Class Helper
 *
 * @package mkdo\content_expiry_date
 */

namespace mkdo\content_expiry_date;

/**
 * Helper
 */
class Helper {

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Render View
	 *
	 * @param  string $file_name File to render.
	 * @return string            File to render
	 */
	public static function render_view( $file_name ) {
		$template_path = get_stylesheet_directory() . '/template-parts/' . $file_name . '.php';
		if ( ! file_exists( $template_path ) ) {
			$template_path = plugin_dir_path( __FILE__ ) . '../views/' . $file_name . '.php';
		}
		return $template_path;
	}
}
