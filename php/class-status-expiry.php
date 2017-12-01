<?php
/**
 * Class Status_Expiry
 *
 * @package mkdo\content_expiry_date
 */

namespace mkdo\content_expiry_date;

/**
 * Renders the expiry status
 */
class Status_Expiry {

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
	 * Do Work
	 */
	public function run() {
		add_action( 'init', array( $this, 'expired_post_status' ) );
		add_action( 'admin_footer-post.php', array( $this, 'expired_post_status_list' ) );
		add_filter( 'display_post_states', array( $this, 'expired_post_status_state' ) );
		add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 10, 3 );
	}

	/**
	 * Custom post status for expired items
	 */
	public function expired_post_status() {

		$public         = true;
		$exclude_search = false;
		$override       = get_option( $this->options_prefix . 'expiry_behaviour_override', 'none' );
		$status         = get_option( $this->options_prefix . 'expiry_behaviour_status', '404' );
		$exclude        = get_option(
			$this->options_prefix . 'expiry_behaviour_exclude',
			array(
				'queries',
				'search',
			)
		);

		if ( 'none' === $override && '404' === $status ) {
			if ( in_array( 'search', $exclude, true ) ) {
				$exclude_search = true;
			}
			if ( in_array( 'queries', $exclude, true ) ) {
				$public = false;
			}
		}

		register_post_status( 'expired', array(
			'label'                     => __( 'Expired', 'content-expiry-date' ),
			'public'                    => $public,
			'exclude_from_search'       => $exclude_search,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'content-expiry-date' ),
		));
	}

	/**
	 * Add the post status as a choosible option
	 */
	public function expired_post_status_list() {

		global $post;

		$name 		= '';
		$label 		= '';
		$complete 	= '';

		if ( 'expired' === $post->post_status ) {
			$name     = __( 'Expired', 'content-expiry-date' );
			$label    = '<span id=\"post-status-display\"> ' . $name . '</span>';
			$complete = ' selected=\"selected\"';
			echo '
			<script>
				jQuery( document ).ready( function( $ ) {
					$( ".misc-pub-section #post-status-display" ).append( " ' . esc_html( $name ) . '" );
				} );
			</script>
			';
		}
		echo '
		<script>
			jQuery( document ).ready( function( $ ) {
				$( "select#post_status" ).append( "<option value=\"expired\" ' . $complete . '>Expired</option>" );
			} );
		</script>
		';
	}

	/**
	 * Append the post status state to post list
	 *
	 * @param  array $states Existing states.
	 * @return array         Modified states
	 */
	public function expired_post_status_state( $states ) {

		global $post;

		$status = get_query_var( 'post_status' );

		if ( 'expired' !== $status ) {
			if ( 'expired' === $post->post_status ) {
				return array( 'Expired' );
			}
		}
		return $states;
	}

	/**
	 * Change Expiry when post status changed
	 *
	 * @param  string $new_status New Status.
	 * @param  string $old_status Old Status.
	 * @param  object $post       Post Object.
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {
		$expire_date = get_post_meta( $post->ID, $this->meta_key, true );
		if ( 'expired' === $new_status ) {
			// If we have just switched to expired, and it is set to never expire,
			// update the date to now.
			if ( empty( $expire_date ) ) {
				update_post_meta( $post->ID, $this->meta_key, date( 'Y-m-d H:i:s' ) );
			}
		} elseif ( 'expired' === $old_status ) {
			// If we have just switched away from expired, and the expire date has
			// passed, we need to make sure it dosnt immediately expire, so remove
			// the expiry date.
			if ( ! empty( $expire_date ) ) {
				$expire_date  = \DateTime::createFromFormat( 'Y-m-d H:i:s', $expire_date );
				$current_date = new \DateTime();
				if ( $current_date >= $expire_date ) {
					delete_post_meta( $post->ID, $this->meta_key );
				}
			}
		}
	}
}
