<?php
/**
 * Class Meta_Expiry
 *
 * @package mkdo\content_expiry_date
 */

namespace mkdo\content_expiry_date;

/**
 * Renders the meta box
 */
class Meta_Expiry {

	/**
	 * Options prefix
	 *
	 * @var string
	 */
	private $options_prefix;

	/**
	 * Nonce Key
	 *
	 * @var string
	 */
	private $nonce_key;

	/**
	 * Nonce action
	 *
	 * @var string
	 */
	private $nonce_action;

	/**
	 * Meta Key Name
	 *
	 * @var string
	 */
	private $meta_key;

	/**
	 * Constructor
	 *
	 * @param Options $options The Plugin Options Object.
	 */
	function __construct( Options $options ) {
		$this->options_prefix = $options->get_options_prefix();
		$this->nonce_key      = 'content_expiry_date_nonce';
		$this->nonce_action   = 'content_expiry_date_role';
		$this->meta_key       = 'mkdo_content_expiry_date';
	}

	/**
	 * Do Work
	 */
	public function run() {
		add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ), 0 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );
	}

	/**
	 * Get Meta Key
	 */
	public function get_meta_key() {
		return $this->meta_key;
	}

	/**
	 * Add meta to the submit box
	 */
	public function post_submitbox_misc_actions() {
		global $post, $wp_locale;

		// Get the post types from our options.
		$selected_post_types = get_option(
			$this->options_prefix . 'post_types',
			array( 'post' )
		);

		// If this post isnt one of the types supported, bail.
		if ( ! in_array( $post->post_type, $selected_post_types, true ) ) {
			return;
		}

		$screen         = get_current_screen();
		$datef          = __( 'M j, Y @ H:i' );
		$expiry_date    = get_post_meta( get_the_ID(), $this->meta_key, true );
		$formatted_date = date_i18n( $datef, strtotime( $expiry_date ) );
		$time_adj       = current_time( 'timestamp' );
		$jj             = ! empty( $expiry_date ) ? mysql2date( 'd', $expiry_date, false ) : gmdate( 'd', $time_adj );
		$mm             = ! empty( $expiry_date ) ? mysql2date( 'm', $expiry_date, false ) : gmdate( 'm', $time_adj );
		$aa             = ! empty( $expiry_date ) ? mysql2date( 'Y', $expiry_date, false ) : gmdate( 'Y', $time_adj );
		$hh             = ! empty( $expiry_date ) ? mysql2date( 'H', $expiry_date, false ) : gmdate( 'H', $time_adj );
		$mn             = ! empty( $expiry_date ) ? mysql2date( 'i', $expiry_date, false ) : gmdate( 'i', $time_adj );
		$ss             = ! empty( $expiry_date ) ? mysql2date( 's', $expiry_date, false ) : gmdate( 's', $time_adj );
		$cur_jj         = gmdate( 'd', $time_adj );
		$cur_mm         = gmdate( 'm', $time_adj );
		$cur_aa         = gmdate( 'Y', $time_adj );
		$cur_hh         = gmdate( 'H', $time_adj );
		$cur_mn         = gmdate( 'i', $time_adj );
		$month          = '<label><span class="screen-reader-text">' . __( 'Month' ) . '</span><select id="mm" name="expire-mm" disabled="true"' . ">\n";
		for ( $i = 1; $i < 13; $i = $i + 1 ) {
			$monthnum = zeroise( $i, 2 );
			$monthtext = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
			$month .= "\t\t\t" . '<option value="' . $monthnum . '" data-text="' . $monthtext . '" ' . selected( $monthnum, $mm, false ) . '>';
			$month .= sprintf( __( '%1$s-%2$s', 'content-expiry-date' ), $monthnum, $monthtext ) . "</option>\n";
		}
		$month         .= '</select></label>';
		$day            = '<label><span class="screen-reader-text">' . __( 'Day', 'content-expiry-date' ) . '</span><input type="text" id="jj" name="expire-jj" value="' . $jj . '" size="2" maxlength="2" autocomplete="off" disabled="true" /></label>';
		$year           = '<label><span class="screen-reader-text">' . __( 'Year', 'content-expiry-date' ) . '</span><input type="text" id="aa" name="expire-aa" value="' . $aa . '" size="4" maxlength="4" autocomplete="off" disabled="true"/></label>';
		$hour           = '<label><span class="screen-reader-text">' . __( 'Hour', 'content-expiry-date' ) . '</span><input type="text" id="hh" name="expire-hh" value="' . $hh . '" size="2" maxlength="2" autocomplete="off" disabled="true" /></label>';
		$minute         = '<label><span class="screen-reader-text">' . __( 'Minute', 'content-expiry-date' ) . '</span><input type="text"id="mn" name="expire-mn" value="' . $mn . '" size="2" maxlength="2" autocomplete="off" disabled="true" /></label>';
		$previous_value = '';
		if ( ! empty( $expiry_date ) ) {
			$previous_value = mysql2date( 'Y-m-d H:i:s', $expiry_date, false );
		}
		?>
		<div class="misc-pub-section curtime misc-pub-curtime">
			<span id="expires">
				<span id="expires-default">
					<?php
					if ( empty( $expiry_date ) ) {
						printf(
							esc_html__( 'Expires %1$s%2$s%3$s', 'content-expiry-date' ),
							'<b>',
							esc_html__( 'never' ),
							'</b>'
						);
					} else {
						printf(
							esc_html__( 'Expires on: %1$s%2$s%3$s', 'content-expiry-date' ),
							'<b>',
							esc_html( $formatted_date ),
							'</b>'
						);
					}

					?>
				</span>
				<span id="expires-scheduled" class="hidden">
					<?php esc_html_e( 'Expires on: ', 'content-expiry-date' ); ?>
					<b></b>
				</span>
				<span id="expires-never" class="hidden">
					<?php esc_html_e( 'Expires ', 'content-expiry-date' ); ?>
					<b>
						<?php esc_html_e( 'never ', 'content-expiry-date' ); ?>
					</b>
				</span>
			</span>
			<a href="#edit_expires" class="edit-expires hide-if-no-js">
				<span aria-hidden="true">
					<?php esc_html_e( 'Edit', 'content-expiry-date' ); ?>
				</span>
				<span class="screen-reader-text">
					<?php esc_html_e( 'Edit expiry date and time', 'content-expiry-date' ); ?>
				</span>
			</a>
			<fieldset id='expiresdiv' class='hide-if-js'>
			<legend class="screen-reader-text">
				<?php esc_html_e( 'Expiry date and time', 'content-expiry-date' ); ?>
			</legend>
				<?php
				echo '<div class="timestamp-wrap">';
				printf( __( '%1$s %2$s, %3$s @ %4$s:%5$s' ), $month, $day, $year, $hour, $minute );
				echo '</div><input type="hidden" id="ss" name="expire-ss" value="' . $ss . '" />';
				wp_nonce_field( $this->nonce_action, $this->nonce_key );
				?>
				<input type="hidden" id="expiry-remove" name="expiry-remove" value='false'/>
				<input type="hidden" id="expiry-previous-value" name="expiry-previous-value" value="<?php echo esc_attr( $previous_value );?>"/>
				<p>
					<a href="#edit_expiry" class="save-expiry hide-if-no-js button"><?php esc_html_e( 'OK', 'content-expiry-date' ); ?></a>
					<a href="#edit_expiry" class="cancel-expiry hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel', 'content-expiry-date' ); ?></a>
					<a href="#edit_expiry" class="remove-expiry hide-if-no-js remove-cancel"><?php esc_html_e( 'Remove', 'content-expiry-date' ); ?></a>
				</p>
				</p>
			</fieldset>
		</div>
		<?php
	}

	/**
	 * Add Meta Boxes
	 */
	public function add_meta_boxes() {

		$override       = get_option( $this->options_prefix . 'expiry_behaviour_override', 'none' );
		$status         = get_option( $this->options_prefix . 'expiry_behaviour_status', '404' );
		$message_option = get_option( $this->options_prefix . 'expiry_behaviour_message_option', 'none' );
		$meta_box       = 'render_meta_box_all';

		if ( 'none' !== $override ) {

			if ( 'all' !== $override ) {
				if ( '301' === $status || '302' === $status ) {
					$meta_box = 'render_meta_box_redirect';
				} elseif ( 'none' !== $message_option ) {
					$meta_box = 'render_meta_box_message';
				} else {
					return;
				}
			}

			// Get the post types from our options.
			$selected_post_types = get_option(
				$this->options_prefix . 'post_types',
				array( 'post' )
			);

			add_meta_box(
				'mkdo_expiry_behaviour',
				__( 'Content Expiry Override', 'expiry_behaviour_status' ),
				array( $this, $meta_box ),
				$selected_post_types,
				'normal',
				'low'
			);

		}
	}

	/**
	 * Render Meta Box All
	 */
	public function render_meta_box_all() {

		$status         = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_status', true );
		$redirect       = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_redirect_url', true );
		$message_option = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_message_option', true );
		$message        = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_message', true );
		$exclude        = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_exclude', true );

		if ( empty( $status ) ) {
			$status = get_option( $this->options_prefix . 'expiry_behaviour_status', '404' );
		}

		if ( empty( $redirect ) ) {
			$redirect = get_option( $this->options_prefix . 'expiry_behaviour_redirect_url' );
		}

		if ( empty( $message_option ) ) {
			$message_option = get_option( $this->options_prefix . 'expiry_behaviour_message_option', 'none' );
		}

		if ( empty( $message ) ) {
			$message = get_option( $this->options_prefix . 'expiry_behaviour_message' );
		}

		if ( empty( $exclude ) ) {
			$exclude = get_option(
				$this->options_prefix . 'expiry_behaviour_exclude',
				array(
					'queries',
					'search',
				)
			);
		}

		if ( ! is_array( $exclude ) ) {
			$exclude = array();
		}

		?>
		<p class="field field-select">
			<label class="row-title" for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_status">
				<strong>
					<?php esc_html_e( 'Return Status:', 'content-expiry-date' ); ?>
				</strong>
			</label>
			<div class="row-content">
				<select name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_status" class="widefat">
					<option
						value="404"
						<?php selected( '404', $status, true ); ?>
					>
					<?php esc_html_e( 'Content not found (404)', 'content-expiry-date' ); ?>
					</option>
					<option
						value="301"
						<?php selected( '301', $status, true ); ?>
					>
					<?php esc_html_e( 'Content permanently redirected (301)', 'content-expiry-date' ); ?>
					</option>
					<option
						value="302"
						<?php selected( '302', $status, true ); ?>
					>
					<?php esc_html_e( 'Content temporarily redirected (302)', 'content-expiry-date' ); ?>
					</option>
					<option
						value="200"
						<?php selected( '200', $status, true ); ?>
					>
					<?php esc_html_e( 'Content successfully found (200)', 'content-expiry-date' ); ?>
					</option>
				</select>
			</div>
		</p>

		<p class="field field-input">
			<label class="row-title" for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_redirect_url">
				<strong>
					<?php esc_html_e( 'Redirect URL:', 'content-expiry-date' ); ?>
				</strong>
			</label>
			<div class="row-content">
				<input
					class="widefat"
					type="url"
					name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_redirect_url"
					value="<?php echo esc_html( $redirect );?>"
					placeholder="<?php esc_html_e( 'http://domain.com', 'content-expiry-date' );?>"
				/>
			</div>
		</p>

		<p class="field field-checkbox">
			<label class="row-title" for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_message_option">
				<strong>
					<?php esc_html_e( 'Message Options:', 'content-expiry-date' ); ?>
				</strong>
			</label>
			<div class="row-content">
				<ul class="field-input">
					<li>
						<label for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_message_option">
							<input
								type="radio"
								name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_message_option"
								value="none"
								<?php checked( 'none', $message_option, true ); ?>
							/>
							<?php esc_html_e( 'Do not show a message', 'content-expiry-date' ); ?>
						</label>
					</li>
					<li>
						<label for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_message_option">
							<input
								type="radio"
								name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_message_option"
								value="404"
								<?php checked( '404', $message_option, true ); ?>
							/>
							<?php esc_html_e( 'Show message on 404 page', 'content-expiry-date' ); ?>
						</label>
					</li>
					<li>
						<label for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_message_option">
							<input
								type="radio"
								name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_message_option"
								value="content"
								<?php checked( 'content', $message_option, true ); ?>
							/>
							<?php esc_html_e( 'Show message on content page', 'content-expiry-date' ); ?>
						</label>
					</li>
				</ul>
			</div>
		</p>

		<p class="field field-textarea">
			<label class="row-title" for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_message">
				<strong>
					<?php esc_html_e( 'Message:', 'content-expiry-date' ); ?>
				</strong>
			</label>
			<div class="row-content">
				<?php
				wp_editor(
					$message,
					esc_attr( $this->options_prefix ) . 'expiry_behaviour_message',
					array(
						'textarea_rows' => 5,
					)
				);
				?>
				<!-- <textarea
					type="url"
					name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_message"
				><?php echo esc_html( $message );?></textarea> -->
			</div>
		</p>

		<p class="field field-checkbox">
			<label class="row-title" for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_exclude">
				<strong>
					<?php esc_html_e( 'Exclude Options:', 'content-expiry-date' ); ?>
				</strong>
			</label>
			<div class="row-content">
				<ul class="field-input">
					<li>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_exclude[]" value="queries" <?php echo in_array( 'queries', $exclude, true ) ?  'checked="checked"' : ''; ?> />
							<?php esc_html_e( 'Exclude content from queries', 'content-expiry-date' );?>
						</label>
					</li>
					<li>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_exclude[]" value="search" <?php echo in_array( 'search', $exclude, true ) ?  'checked="checked"' : ''; ?> />
							<?php esc_html_e( 'Exclude content from search', 'content-expiry-date' );?>
						</label>
					</li>
				</ul>
			</div>
		</p>
		<?php
		wp_nonce_field( $this->nonce_action, $this->nonce_key );
	}

	/**
	 * Render Meta Box Message
	 */
	public function render_meta_box_message() {

		$message        = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_message', true );

		if ( empty( $message ) ) {
			$message = get_option( $this->options_prefix . 'expiry_behaviour_message' );
		}
		?>
		<p class="field field-textarea">
			<label class="row-title" for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_message">
				<strong>
					<?php esc_html_e( 'Message:', 'content-expiry-date' ); ?>
				</strong>
			</label>
			<div class="row-content">
				<?php
				wp_editor(
					$message,
					esc_attr( $this->options_prefix ) . 'expiry_behaviour_message',
					array(
						'textarea_rows' => 5,
					)
				);
				?>
				<!-- <textarea
					type="url"
					name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_message"
				><?php echo esc_html( $message );?></textarea> -->
			</div>
		</p>
		<?php
		wp_nonce_field( $this->nonce_action, $this->nonce_key );
	}

	/**
	 * Render Meta Box Redirect
	 */
	public function render_meta_box_redirect() {

		$redirect = get_post_meta( get_the_ID(), $this->options_prefix . 'expiry_behaviour_redirect_url', true );
		if ( empty( $redirect ) ) {
			$redirect = get_option( $this->options_prefix . 'expiry_behaviour_redirect_url' );
		}
		?>
		<p class="field field-input">
			<label class="row-title" for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_redirect_url">
				<strong>
					<?php esc_html_e( 'Redirect URL:', 'content-expiry-date' ); ?>
				</strong>
			</label>
			<div class="row-content">
				<input
					class="widefat"
					type="url"
					name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_redirect_url"
					value="<?php echo esc_html( $redirect );?>"
					placeholder="<?php esc_html_e( 'http://domain.com', 'content-expiry-date' );?>"
				/>
			</div>
		</p>
		<?php
		wp_nonce_field( $this->nonce_action, $this->nonce_key );
	}

	/**
	 * Save the Expiry Meta
	 *
	 * @param  int $post_id The Post ID.
	 */
	public function save_meta( $post_id ) {

		global $wp_roles;

		// If it is just a revision don't worry about it.
		if ( wp_is_post_revision( $post_id ) ) {
			return $post_id;
		}

		// Check it's not an auto save routine.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Verify the nonce to defend against XSS.
		if ( ! isset( $_POST[ $this->nonce_key ] ) || ! wp_verify_nonce( $_POST[ $this->nonce_key ], $this->nonce_action ) ) {
			return $post_id;
		}

		// Check that the current user has permission to edit the post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Check that all the date information is set.
		if (
			isset( $_POST['expire-mm'] ) && // Input var okay.
			isset( $_POST['expire-jj'] ) && // Input var okay.
			isset( $_POST['expire-aa'] ) && // Input var okay.
			isset( $_POST['expire-hh'] ) && // Input var okay.
			isset( $_POST['expire-mn'] ) // Input var okay.
		) {
			$year   = $_POST['expire-aa'];
			$month  = $_POST['expire-mm'];
			$day    = $_POST['expire-jj'];
			$hour   = $_POST['expire-hh'];
			$minute = $_POST['expire-mn'];

			$save_date = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':00';

			update_post_meta( $post_id, $this->meta_key, sanitize_text_field( $save_date ) );
		}

		if ( isset( $_POST['expiry-remove'] ) && 'true' === $_POST['expiry-remove'] ) { // Input var okay.
			delete_post_meta( $post_id, $this->meta_key );
		}

		if ( isset( $_POST[ $this->options_prefix . 'expiry_behaviour_status' ] ) ) { // Input var okay.
			$status = $_POST[ $this->options_prefix . 'expiry_behaviour_status' ];
			update_post_meta( $post_id, $this->options_prefix . 'expiry_behaviour_status', sanitize_text_field( $status ) );
		}

		if ( isset( $_POST[ $this->options_prefix . 'expiry_behaviour_redirect_url' ] ) ) { // Input var okay.
			$redirect = $_POST[ $this->options_prefix . 'expiry_behaviour_redirect_url' ];
			update_post_meta( $post_id, $this->options_prefix . 'expiry_behaviour_redirect_url', sanitize_text_field( $redirect ) );
		}

		if ( isset( $_POST[ $this->options_prefix . 'expiry_behaviour_message_option' ] ) ) { // Input var okay.
			$message_option = $_POST[ $this->options_prefix . 'expiry_behaviour_message_option' ];
			update_post_meta( $post_id, $this->options_prefix . 'expiry_behaviour_message_option', sanitize_text_field( $message_option ) );
		}

		if ( isset( $_POST[ $this->options_prefix . 'expiry_behaviour_message' ] ) ) { // Input var okay.
			$message = $_POST[ $this->options_prefix . 'expiry_behaviour_message' ];
			update_post_meta( $post_id, $this->options_prefix . 'expiry_behaviour_message', wp_kses_post( $message ) );
		}

		if ( isset( $_POST[ $this->options_prefix . 'expiry_behaviour_exclude' ] ) ) { // Input var okay.
			$exclude = $_POST[ $this->options_prefix . 'expiry_behaviour_exclude' ];
			if ( ! is_array( $exclude ) ) {
				$exclude = array();
			}
			foreach ( $exclude as &$e ) {
				$e = sanitize_text_field( $e );
			}
			update_post_meta( $post_id, $this->options_prefix . 'expiry_behaviour_exclude', $exclude );
		} else {
			$override = get_option( $this->options_prefix . 'expiry_behaviour_override', 'none' );
			if ( 'all' === $override ) {
				update_post_meta( $post_id, $this->options_prefix . 'expiry_behaviour_exclude', array() );
			}
		}
	}
}
