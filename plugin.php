<?php
/**
 * Content Expiry Date
 *
 * @link              https://github.com/mkdo/content-expiry-date
 * @package           mkdo\content_expiry_date
 *
 * Plugin Name:       Content Expiry Date
 * Plugin URI:        https://github.com/mkdo/content-expiry-date
 * Description:       Remove content from WordPress on a certain date
 * Version:           1.0.0
 * Author:            Make Do <hello@makedo.net>
 * Author URI:        http://www.makedo.in
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       content-expiry-date
 * Domain Path:       /languages
 */

// Constants.
define( 'MKDO_CONTENT_EXPIRY_DATE_ROOT', __FILE__ );

// Load Classes.
require_once 'php/class-helper.php';
require_once 'php/class-controller-assets.php';
require_once 'php/class-controller-main.php';
require_once 'php/class-handle-expiry.php';
require_once 'php/class-meta-expiry.php';
require_once 'php/class-options.php';
require_once 'php/class-status-expiry.php';
require_once 'php/class-render-expiry-message.php';

// Use Namespaces.
use mkdo\content_expiry_date\Helper;
use mkdo\content_expiry_date\Controller_Assets;
use mkdo\content_expiry_date\Controller_Main;
use mkdo\content_expiry_date\Handle_Expiry;
use mkdo\content_expiry_date\Meta_Expiry;
use mkdo\content_expiry_date\Options;
use mkdo\content_expiry_date\Status_Expiry;
use mkdo\content_expiry_date\Render_Expiry_Message;


// Initialize Classes.
$options               = new Options();
$controller_assets     = new Controller_Assets( $options );
$meta_expiry           = new Meta_Expiry( $options );
$handle_expiry         = new Handle_Expiry( $options, $meta_expiry );
$status_expiry         = new Status_Expiry( $options, $meta_expiry );
$render_expiry_message = new Render_Expiry_Message( $options, $meta_expiry );
$controller            = new Controller_Main(
	$options,
	$controller_assets,
	$meta_expiry,
	$handle_expiry,
	$status_expiry,
	$render_expiry_message
);

// Run the Plugin.
$controller->run();
