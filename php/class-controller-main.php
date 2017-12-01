<?php
/**
 * Class Controller_Main
 *
 * @package mkdo\content_expiry_date
 */

namespace mkdo\content_expiry_date;

/**
 * The main loader for this plugin
 */
class Controller_Main {

	/**
	 * Object to load the plugin options
	 *
	 * @var object
	 */
	private $options;

	/**
	 * Object to load the assets
	 *
	 * @var object
	 */
	private $controller_assets;

	/**
	 * Object to render the meta boxes
	 *
	 * @var object
	 */
	private $meta_expiry;

	/**
	 * Object to handle the expiry
	 *
	 * @var object
	 */
	private $handle_expiry;

	/**
	 * Object to render the status of the post
	 *
	 * @var object
	 */
	private $status_expiry;

	/**
	 * Object to render the messages
	 *
	 * @var object
	 */
	private $render_expiry_message;

	/**
	 * Constructor
	 *
	 * @param Options           $options           Object to load the plugin options.
	 * @param Controller_Assets $controller_assets Object to load the assets.
	 * @param Meta_Expiry       $meta_expiry       Object to render the meta boxes.
	 * @param Handle_Expiry          $handle_expiry          Object to handle the expiry.
	 * @param Status_Expiry     $status_expiry     Object to render the status of the post.
	 */
	public function __construct(
		Options $options,
		Controller_Assets $controller_assets,
		Meta_Expiry $meta_expiry,
		Handle_Expiry $handle_expiry,
		Status_Expiry $status_expiry,
		Render_Expiry_Message $render_expiry_message
	) {
		$this->options               = $options;
		$this->controller_assets     = $controller_assets;
		$this->meta_expiry           = $meta_expiry;
		$this->handle_expiry         = $handle_expiry;
		$this->status_expiry         = $status_expiry;
		$this->render_expiry_message = $render_expiry_message;
	}

	/**
	 * Do Work
	 */
	public function run() {
		load_plugin_textdomain( 'content-expiry-date', false, MKDO_CONTENT_EXPIRY_DATE_ROOT . '\languages' );

		$this->options->run();
		$this->controller_assets->run();
		$this->meta_expiry->run();
		$this->handle_expiry->run();
		$this->status_expiry->run();
		$this->render_expiry_message->run();
	}
}
