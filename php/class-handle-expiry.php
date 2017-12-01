<?php
/**
 * Class Handle_Expiry
 *
 * @package mkdo\content_expiry_date
 */

namespace mkdo\content_expiry_date;

/**
 * Handle the Expiry
 */
class Handle_Expiry {

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
	 * Expired Post IDs
	 *
	 * @var array
	 */
	private $expired_post_ids;

	/**
	 * Constructor
	 *
	 * @param Options     $options     The Plugin Options Object.
	 * @param Meta_Expiry $meta_expiry The Meta Object.
	 */
	function __construct( Options $options, Meta_Expiry $meta_expiry ) {
		$this->options_prefix    = $options->get_options_prefix();
		$this->meta_key          = $meta_expiry->get_meta_key();
		$this->expired_post_ids  = $this->get_expired_post_ids();
	}

	/**
	 * Do Work
	 */
	public function run() {
		add_action( 'wp', array( $this, 'handle_expiry' ) );
		add_action( 'pre_get_posts', array( $this, 'exclusions' ) );
	}

	/**
	 * Do the Redirect
	 */
	public function handle_expiry() {

		global $wp_query, $post;

		$expire_date  = get_post_meta( get_the_ID(), $this->meta_key, true );

		// If there is no expired date, bail.
		if ( empty( $expire_date ) ) {
			return;
		}

		$expire_date  = \DateTime::createFromFormat( 'Y-m-d H:i:s', $expire_date );
		$current_date = new \DateTime();

		// If the expiry date has not passed, bail.
		if ( $current_date < $expire_date ) {
			return;
		}

		// Update the post status if it is not set.
		if ( is_object( $post ) && 'expired' !== $post->post_status ) {
			$post->post_status = 'expired';
			wp_update_post( $post );
		}

		// If we are in the admin screen, bail.
		if ( is_admin() ) {
			return;
		}

		// Check if this is an override.
		$override = get_option( $this->options_prefix . 'expiry_behaviour_override', 'none' );
		$status   = get_option( $this->options_prefix . 'expiry_behaviour_status', '404' );

		/**
		 * Handle Redirect
		 */
		$redirect = get_option( $this->options_prefix . 'expiry_behaviour_redirect_url' );

		// If this is overridden, get the values from the post.
		if ( 'none' !== $override ) {
			$status_override   = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_status', true );
			$redirect_override = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_redirect_url', true );
			if ( ! empty( $status_override ) ) {
				$status = $status_override;
			}
			if ( ! empty( $redirect_override ) ) {
				$redirect = $redirect_override;
			}
		}

		// If the status is 301 or 302, do the redirect.
		if ( '301' === $status || '302' === $status ) {
			if ( ! empty( $redirect ) ) {
				header( 'Location: ' . $redirect, true, $status );
				exit;
			}
		}

		/**
		 * Handle status
		 */
		if ( '200' === $status || '404' === $status ) {
			status_header( $status );

			/**
			 * Handle status message
			 */
			$message_option = get_option( $this->options_prefix . 'expiry_behaviour_message_option', 'none' );
			$message        = get_option( $this->options_prefix . 'expiry_behaviour_message' );

			// If this is overridden, get the values from the post.
			if ( 'none' !== $override ) {
				$message_option_override = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_message_option', true );
				$message_override        = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_message', true );
				if ( ! empty( $message_option_override ) ) {
					$message_option = $message_option_override;
				}
				if ( ! empty( $message_override ) ) {
					$message = $message_override;
				}
			}

			// If the message is set to appear on the 404 page, or there is no
			// message, send us to the 404 page template.
			if ( 'none' === $message_option || '404' === $message_option ) {
				$wp_query->is_404 = ( '404' === $status );
			}
		}
	}

	/**
	 * Do query exclusions
	 *
	 * @param object $query Query Object.
	 */
	public function exclusions( $query ) {

		// If we are in the admin screen, bail.
		if ( is_admin() ) {
			return;
		}

		$override              = get_option( $this->options_prefix . 'expiry_behaviour_override', 'none' );
		$excluded_search_posts = array();
		$excluded_query_posts  = array();

		// Get the post types from our options.
		$selected_post_types = get_option(
			$this->options_prefix . 'post_types',
			array( 'post' )
		);

		foreach ( $this->expired_post_ids as $expired_post_id ) {

			$expired_post = get_post( $expired_post_id );
			$status         = get_option( $this->options_prefix . 'expiry_behaviour_status', '404' );
			$exclude        = get_option(
				$this->options_prefix . 'expiry_behaviour_exclude',
				array(
					'queries',
					'search',
				)
			);

			// Include the exclude post status in all queries, unless globally
			// excluded.
			if ( 'none' !== $override || ! in_array( 'queries', $exclude, true ) ) {
			    if ( ! is_search() || ! isset( $query->query_vars['post_status'] ) ) {
					$query->set(
						'post_status',
						array(
							'publish',
							'expired',
						)
					);
			    }
			}

			if ( in_array( $expired_post->post_type, $selected_post_types, true ) ) {

				if ( 'none' !== $override ) {
					$status_override  = get_post_meta( $expired_post->ID, $this->options_prefix . 'expiry_behaviour_status', true );
					$exclude          = get_post_meta( $expired_post->ID, $this->options_prefix . 'expiry_behaviour_exclude', true );
					if ( ! empty( $status_override ) ) {
						$status = $status_override;
					}
				}

				if ( ! is_array( $exclude ) ) {
					$exclude = array();
				}

				if ( in_array( 'search', $exclude, true ) ) {
					$excluded_search_posts[] = $expired_post->ID;
				}

				if ( in_array( 'queries', $exclude, true ) ) {
					$excluded_query_posts[] = $expired_post->ID;
				}
			}
		}

		// Set the posts that should be excluded from search.
		if ( ! empty( $excluded_search_posts ) ) {
		    if ( $query->is_main_query() && is_search() ) {
				$query->set( 'post__not_in', $excluded_search_posts );
		    }
		}

		// Set the posts that should be excluded from everywhere.
		if ( ! empty( $excluded_query_posts ) ) {
		    if ( ! is_search() ) {
				$query->set( 'post__not_in', $excluded_query_posts );
		    }
		}
	}

	/**
	 * Get the expired post ids
	 */
	public function get_expired_post_ids() {

		global $wpdb;

		$post_status           = 'expired';
		$post_ids              = $wpdb->get_results(
	        $wpdb->prepare(
		        "
		        SELECT ID FROM $wpdb->posts
		        WHERE post_status = %s",
		        $post_status
	        )
	    );

		return $post_ids;
	}
}
