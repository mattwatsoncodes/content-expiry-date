<?php
/**
 * Class Render_Expiry_Message
 *
 * @package mkdo\content_expiry_date
 */

namespace mkdo\content_expiry_date
{
	/**
	 * Render the Expiry Messages
	 */
	class Render_Expiry_Message {

		/**
		 * Options prefix
		 *
		 * @var string
		 */
		private $options_prefix;

		/**
		 * Meta Key
		 *
		 * @var string
		 */
		private $meta_key;

		/**
		 * The instance of this object
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Constructor
		 *
		 * @param Options     $options     The Plugin Options Object.
		 * @param Meta_Expiry $meta_expiry The Meta Object.
		 */
		function __construct( Options $options, Meta_Expiry $meta_expiry ) {
			$this->options_prefix = $options->get_options_prefix();
			$this->meta_key       = $meta_expiry->get_meta_key();
		}

		/**
		 * Get instance of this object
		 *
		 * @return object Instance of this object
		 */
		public static function get_instance() {

			if ( null === self::$instance ) {
				$options     = new Options();
				$meta_expiry = new Meta_Expiry( $options );
	            self::$instance = new self( $options, $meta_expiry );
	        }

			return self::$instance;
	    }

		/**
		 * Do Work
		 */
		public function run() {
			add_action( 'init', array( $this, 'register_shortcode' ) );
		}

		/**
		 * Render the 404 message
		 */
		public function render_404_message() {

			global $post;

			if ( is_object( $post ) ) {

				$expiry_date = get_post_meta( $post->ID, 'mkdo_content_expiry_date', true );

				if ( ! empty( $expiry_date ) ) {

					$current_date = new \DateTime();
					$expiry_date  = \DateTime::createFromFormat( 'Y-m-d H:i:s', $expiry_date );

					if ( $expiry_date < $current_date ) {

						$override       = get_option( $this->options_prefix . 'expiry_behaviour_override', 'none' );
						$status         = get_option( $this->options_prefix . 'expiry_behaviour_status', '404' );
						$message_option = get_option( $this->options_prefix . 'expiry_behaviour_message_option', 'none' );
						$message        = get_option( $this->options_prefix . 'expiry_behaviour_message' );

						if ( 'none' !== $override ) {
							$status_override         = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_status', true );
							$message_option_override = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_message_option', true );
							$message_override        = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_message', true );
							if ( ! empty( $status_override ) ) {
								$status = $status_override;
							}
							if ( ! empty( $message_option_override ) ) {
								$message_option = $message_option_override;
							}
							if ( ! empty( $message_override ) ) {
								$message = $message_override;
							}
						}

						if ( '404' === $message_option && '404' === $status ) {
							include Helper::render_view( 'view-expiry-404-message' );
						}
					}
				}
			}
		}

		/**
		 * Render the content message
		 */
		public function render_content_message() {

			global $post;

			if ( is_object( $post ) ) {

				$expiry_date = get_post_meta( $post->ID, 'mkdo_content_expiry_date', true );

				if ( ! empty( $expiry_date ) ) {


					$current_date = new \DateTime();
					$expiry_date  = \DateTime::createFromFormat( 'Y-m-d H:i:s', $expiry_date );

					if ( $expiry_date < $current_date ) {
						$override       = get_option( $this->options_prefix . 'expiry_behaviour_override', 'none' );
						$status         = get_option( $this->options_prefix . 'expiry_behaviour_status', '404' );
						$message_option = get_option( $this->options_prefix . 'expiry_behaviour_message_option', 'none' );
						$message        = get_option( $this->options_prefix . 'expiry_behaviour_message' );

						if ( 'none' !== $override ) {
							$status_override         = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_status', true );
							$message_option_override = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_message_option', true );
							$message_override        = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_message', true );
							if ( ! empty( $status_override ) ) {
								$status = $status_override;
							}
							if ( ! empty( $message_option_override ) ) {
								$message_option = $message_option_override;
							}
							if ( ! empty( $message_override ) ) {
								$message = $message_override;
							}
						}

						if ( 'content' === $message_option && ( '404' === $status || '200' === $status ) ) {
							include Helper::render_view( 'view-expiry-content-message' );
						}
					}
				}
			}
		}

		/**
		 * Register the shortcodes
		 */
		public function register_shortcode() {
			add_shortcode( 'mkdo_content_expiry_date_render_404_message', array( $this, 'render_404_message' ) );
			add_shortcode( 'mkdo_content_expiry_date_render_content_message', array( $this, 'render_content_message' ) );
		}
	}
}

namespace
{
	if ( ! function_exists( 'mkdo_content_expiry_date_render_404_message' ) ) {
		/**
		 * Render the 404 message
		 */
		function mkdo_content_expiry_date_render_404_message() {
			$render = mkdo\content_expiry_date\Render_Expiry_Message::get_instance();
			$render->render_404_message();
		}
	}

	if ( ! function_exists( 'mkdo_content_expiry_date_render_content_message' ) ) {
		/**
		 * Render the content message
		 */
		function mkdo_content_expiry_date_render_content_message() {
			$render = mkdo\content_expiry_date\Render_Expiry_Message::get_instance();
			$render->render_content_message();
		}
	}
}
