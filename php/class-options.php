<?php
/**
 * Class Plugin Options
 *
 * @package mkdo\content_expiry_date
 */

namespace mkdo\content_expiry_date;

/**
 * Options page for the plugin
 */
class Options {

	/**
	 * Options Prefix
	 *
	 * @var string
	 */
	private $options_prefix;

	/**
	 * Slug for the menu
	 *
	 * @var string
	 */
	private $options_menu_slug;

	/**
	 * URL for the settings page
	 *
	 * @var string
	 */
	private $plugin_settings_url;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options_prefix      = 'mkdo_content_expiry_date_';
		$this->options_menu_slug   = 'content_expiry_date';
		$this->plugin_settings_url = admin_url( 'options-general.php?page=' . $this->options_menu_slug );
	}

	/**
	 * Do Work
	 */
	public function run() {
		add_action( 'admin_init', array( $this, 'init_options_page' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'plugin_action_links_' . plugin_basename( MKDO_CONTENT_EXPIRY_DATE_ROOT ) , array( $this, 'add_setings_link' ) );
	}

	/**
	 * Get options prefix
	 */
	public function get_options_prefix() {
		return $this->options_prefix;
	}

	/**
	 * Get plugin settings url
	 */
	public function get_plugin_settings_url() {
		return $this->plugin_settings_url;
	}

	/**
	 * Initialise the Options Page
	 */
	public function init_options_page() {

		$prefix   = $this->options_prefix;
		$settings = $prefix . 'settings';

		// Register Settings.
		register_setting( $settings . '_group', $this->options_prefix . 'post_types' );
		register_setting( $settings . '_group', $this->options_prefix . 'expiry_behaviour_status' );
		register_setting( $settings . '_group', $this->options_prefix . 'expiry_behaviour_redirect_url' );
		register_setting( $settings . '_group', $this->options_prefix . 'expiry_behaviour_message_option' );
		register_setting( $settings . '_group', $this->options_prefix . 'expiry_behaviour_message' );
		register_setting( $settings . '_group', $this->options_prefix . 'expiry_behaviour_exclude' );
		register_setting( $settings . '_group', $this->options_prefix . 'expiry_behaviour_override' );
		register_setting( $settings . '_group', $prefix . 'enqueue_front_end_assets' );
		register_setting( $settings . '_group', $prefix . 'enqueue_back_end_assets' );

		// Add section for choosing post types.
		$section = $this->options_prefix . 'section_select_post_types';
		add_settings_section( $section, __( 'Choose Post Types', 'content-expiry-date' ), array( $this, 'render_section_select_post_types' ), $settings );
		add_settings_field( $this->options_prefix . 'field_post_types', __( 'Post Types:', 'content-expiry-date' ), array( $this, 'render_field_post_types' ), $settings, $section );

		// Add section for choosing expiry behaviour.
		$section = $this->options_prefix . 'section_select_expiry_behaviour';
		add_settings_section( $section, __( 'Expiry Behaviour', 'content-expiry-date' ), array( $this, 'section_select_expiry_behaviour' ), $settings );
		add_settings_field( $this->options_prefix . 'field_expiry_behaviour_status', __( 'Return Status:', 'content-expiry-date' ), array( $this, 'render_field_expiry_behaviour_status' ), $settings, $section );
		add_settings_field( $this->options_prefix . 'field_expiry_behaviour_message_redirect_url', __( 'Redirect URL:', 'content-expiry-date' ), array( $this, 'render_field_expiry_behaviour_redirect_url' ), $settings, $section );
		add_settings_field( $this->options_prefix . 'field_expiry_behaviour_message_option', __( 'Message Options:', 'content-expiry-date' ), array( $this, 'render_field_expiry_behaviour_message_option' ), $settings, $section );
		add_settings_field( $this->options_prefix . 'field_expiry_behaviour_message', __( 'Message:', 'content-expiry-date' ), array( $this, 'render_field_expiry_behaviour_message' ), $settings, $section );
		add_settings_field( $this->options_prefix . 'field_expiry_behaviour_exclude', __( 'Exclude Options:', 'content-expiry-date' ), array( $this, 'render_field_expiry_behaviour_exclude' ), $settings, $section );
		add_settings_field( $this->options_prefix . 'field_expiry_behaviour_override', __( 'Override Settings:', 'content-expiry-date' ), array( $this, 'render_field_expiry_behaviour_override' ), $settings, $section );

		// Add section and fields for Asset Enqueing.
		$section = $prefix . 'section_enqueue_assets';
		add_settings_section( $section, 'Enqueue Assets', array( $this, 'render_section_enqueue_assets' ), $settings );
		// add_settings_field( $prefix . 'field_enqueue_front_end_assets', 'Enqueue Front End Assets:', array( $this, 'render_field_enqueue_front_end_assets' ), $settings, $section );
		add_settings_field( $prefix . 'field_enqueue_back_end_assets', 'Enqueue Back End Assets:', array( $this, 'render_field_enqueue_back_end_assets' ), $settings, $section );
	}

	/**
	 * Call back for the post_type section
	 */
	public function render_section_select_post_types() {
		echo '<p>';
		esc_html_e( 'Select the Post Types that you wish to enable the expiry date on.', 'content-expiry-date' );
		echo '</p>';
	}

	/**
	 * Call back for the post_type selector
	 */
	public function render_field_post_types() {

		$post_type_args = array(
			'show_ui' => true,
		);
		$post_types          = get_post_types( $post_type_args );
		$selected_post_types = get_option(
			$this->options_prefix . 'post_types',
			array( 'post' )
		);

		unset( $post_types['attachment'] );

		if ( ! is_array( $selected_post_types ) ) {
			$selected_post_types = array();
		}

		?>
		<div class="field field-checkbox">
			<ul class="field-input">
			<?php
			foreach ( $post_types as $key => $post_type ) {
				$post_type_object = get_post_type_object( $post_type );
				?>
				<li>
					<label>
						<input
							type="checkbox"
							name="<?php echo esc_attr( $this->options_prefix );?>post_types[]"
							value="<?php echo esc_attr( $key ); ?>"
							<?php if ( in_array( $key, $selected_post_types, true ) ) { echo ' checked="checked"'; } ?>
						/>
						<?php echo esc_html( $post_type_object->labels->name ); ?>
					</label>
				</li>
				<?php
			}
			?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Call back for the expiry_behaviour section
	 */
	public function section_select_expiry_behaviour() {
		echo '<p>';
		esc_html_e( 'Chose how an expired peice of content should behave', 'content-expiry-date' );
		echo '</p>';
		echo '<p>';
		esc_html_e(
			'
			To exclude the page completely from Search Engines, ensure that both
			exclude options are checked, and that the page returns a 404 status
			(also prevent overriding to ensure that the plugin is excluded from
			public view).
			',
			'content-expiry-date'
		);
		echo '</p>';
	}

	/**
	 * Call back for the expiry_behaviour_status options
	 */
	public function render_field_expiry_behaviour_status() {
		$status = get_option( $this->options_prefix . 'expiry_behaviour_status', '404' );
		?>
		<div class="field field-select">
			<select name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_status">
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
		<?php
	}

	/**
	 * Call back for the expiry_behaviour_redirect_url options
	 */
	public function render_field_expiry_behaviour_redirect_url() {
		$redirect = get_option( $this->options_prefix . 'expiry_behaviour_redirect_url' );
		?>
		<div class="field field-input">
			<input
				type="url"
				class="regular-text"
				name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_redirect_url"
				value="<?php echo esc_html( $redirect );?>"
				placeholder="<?php esc_html_e( 'http://domain.com', 'content-expiry-date' );?>"
			/>
		</div>
		<p class="description">
			<?php
			esc_html_e(
				'Redirect will only occur if a 301 or 302 has been selected as the status.',
				'content-expiry-date'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Call back for the expiry_behaviour_message_option options
	 */
	public function render_field_expiry_behaviour_message_option() {

		$message_option = get_option( $this->options_prefix . 'expiry_behaviour_message_option', 'none' );
		?>
		<div class="field field-checkbox">
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
		<?php
	}

	/**
	 * Call back for the expiry_behaviour_message options
	 */
	public function render_field_expiry_behaviour_message() {
		$message  = get_option( $this->options_prefix . 'expiry_behaviour_message' );
		?>
		<div class="field field-textarea">
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
		<p class="description">
			<?php
			esc_html_e(
				'Message will only appear if message option is set, and if a 200 or 400 has been selected as the status.',
				'content-expiry-date'
			);
			?>
			<br/>
			<br/>
		</p>
		<p>
			<?php
			esc_html_e(
				'To render the 404 message ensure you place the function',
				'content-expiry-date'
			);
			?>
			<code>
				<?php
				esc_html_e(
					'mkdo_content_expiry_date_render_404_message()',
					'content-expiry-date'
				);
				?>
			</code>
			<?php
			esc_html_e(
				'or the shortcode',
				'content-expiry-date'
			);
			?>
			<code>
				<?php
				esc_html_e(
					'[mkdo_content_expiry_date_render_404_message]',
					'content-expiry-date'
				);
				?>
			</code>
			<?php
			esc_html_e(
				'into your template.',
				'content-expiry-date'
			);
			?>
			<br/>
			<br/>
		</p>
		<p>
			<?php
			esc_html_e(
				'To render the content message ensure you place the function',
				'content-expiry-date'
			);
			?>
			<code>
				<?php
				esc_html_e(
					'mkdo_content_expiry_date_render_content_message()',
					'content-expiry-date'
				);
				?>
			</code>
			<?php
			esc_html_e(
				'or the shortcode',
				'content-expiry-date'
			);
			?>
			<code>
				<?php
				esc_html_e(
					'[mkdo_content_expiry_date_render_content_message]',
					'content-expiry-date'
				);
				?>
			</code>
			<?php
			esc_html_e(
				'into your template (or content when using the shortcode).',
				'content-expiry-date'
			);
			?>
			<br/>
			<br/>
		</p>
		<?php
	}

	/**
	 * Call back for the expiry_behaviour_exclude options
	 */
	public function render_field_expiry_behaviour_exclude() {

		$exclude = get_option(
			$this->options_prefix . 'expiry_behaviour_exclude',
			array(
				'queries',
				'search',
			)
		);

		if ( ! is_array( $exclude ) ) {
			$exclude = array();
		}

		?>
		<div class="field field-checkbox">
			<ul class="field-input">
				<li>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_exclude[]" value="queries" <?php echo in_array( 'queries', $exclude, true ) ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Exclude content from queries', 'content-expiry-date' );?>
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_exclude[]" value="search" <?php echo in_array( 'search', $exclude, true ) ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Exclude content from search', 'content-expiry-date' );?>
					</label>
				</li>
			</ul>
		</div>
		<p class="description">
			<?php
			esc_html_e(
				'
				If your theme uses standard WordPress queries, exclude from queries
				will prevent the content showing up in lists, archive pages and menus.
				',
				'content-expiry-date'
			);
			?>
			<br/><br/>
		</p>
		<p>
			<?php
			esc_html_e(
				'
				If your theme uses the standard WordPress search, exclude from search
				will prevent the content showing up in searches.
				',
				'content-expiry-date'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Call back for the expiry_behaviour_override options
	 */
	public function render_field_expiry_behaviour_override() {
		$override = get_option( $this->options_prefix . 'expiry_behaviour_override', 'none' );
		?>
		<div class="field field-checkbox">
			<ul class="field-input">
				<li>
					<label for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_override">
						<input
							type="radio"
							name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_override"
							value="none"
							<?php checked( 'none', $override, true ); ?>
						/>
						<?php esc_html_e( 'Do not allow override expiry settings', 'content-expiry-date' ); ?>
					</label>
				</li>
				<li>
					<label for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_override">
						<input
							type="radio"
							name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_override"
							value="all"
							<?php checked( 'all', $override, true ); ?>
						/>
						<?php esc_html_e( 'Allow override of all expiry settings', 'content-expiry-date' ); ?>
					</label>
				</li>
				<li>
					<label for="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_override">
						<input
							type="radio"
							name="<?php echo esc_attr( $this->options_prefix );?>expiry_behaviour_override"
							value="partial"
							<?php checked( 'partial', $override, true ); ?>
						/>
						<?php esc_html_e( 'Allow override of preconfigured settings', 'content-expiry-date' ); ?>
					</label>
				</li>
			</ul>
			<p class="description">
				<?php
				esc_html_e(
					'
					Overriding content will place a meta box on peices of content\'s
					edit screens.
					',
					'content-expiry-date'
				);
				?>
				<br/><br/>
			</p>
			<p>
				<?php
				esc_html_e(
					'
					Preconfigured settings will let certain settings be
					overridden depending on the options that have been chosen.
					',
					'content-expiry-date'
				);
				?>
				<br/>
				<?php esc_html_e( 'For example:', 'content-expiry-date' ); ?>
			</p>
			<ul>
				<li>
				<?php
				esc_html_e(
					'
					If a status of redirect has been chosen, it will allow you to
					define the redirect URL per piece of content.
					',
					'content-expiry-date'
				);
				?>
				</li>
				<li>
				<?php
				esc_html_e(
					'
					If you have opted to show a message, it will allow you to change
					the message per peice of content.
					',
					'content-expiry-date'
				);
				?>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render the Enqueue Assets section
	 */
	public function render_section_enqueue_assets() {
		echo '<p>';
		esc_html_e( 'Assets are loaded by default, however we recomend that you disable asset loading and include assets in your frontend workflow.', 'content-expiry-date' );
		echo '</p>';
	}

	/**
	 * Render the Enqueue Front End Assets field.
	 */
	public function render_field_enqueue_front_end_assets() {

		$enqueued_assets = get_option(
			$this->options_prefix . 'enqueue_front_end_assets',
			array(
				'plugin_css',
				'plugin_js',
			)
		);

		// If no assets are enqueued
		// prevent errors by declaring the variable as an array.
		if ( ! is_array( $enqueued_assets ) ) {
			$enqueued_assets = array();
		}

		?>
		<div class="field field-checkbox">
			<ul class="field-input">
				<li>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->options_prefix );?>enqueue_front_end_assets[]" value="plugin_css" <?php echo in_array( 'plugin_css', $enqueued_assets, true ) ?  'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Plugin CSS', 'content-expiry-date' );?>
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->options_prefix );?>enqueue_front_end_assets[]" value="plugin_js" <?php echo in_array( 'plugin_js', $enqueued_assets, true ) ?  'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Plugin JS', 'content-expiry-date' );?>
					</label>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render the Enqueue Back End Assets field.
	 */
	public function render_field_enqueue_back_end_assets() {

		$enqueued_assets = get_option(
			$this->options_prefix . 'enqueue_back_end_assets',
			array(
				'plugin_admin_css',
				// 'plugin_admin_editor_css',
				'plugin_admin_js',
			)
		);

		// If no assets are enqueued
		// prevent errors by declaring the variable as an array.
		if ( ! is_array( $enqueued_assets ) ) {
			$enqueued_assets = array();
		}

		?>
		<div class="field field-checkbox">
			<ul class="field-input">
				<li>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->options_prefix );?>enqueue_back_end_assets[]" value="plugin_admin_css" <?php echo in_array( 'plugin_admin_css', $enqueued_assets, true ) ?  'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Plugin Admin CSS', 'content-expiry-date' );?>
					</label>
				</li>
				<!-- <li>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->options_prefix );?>enqueue_back_end_assets[]" value="plugin_admin_css" <?php echo in_array( 'plugin_admin_editor_css', $enqueued_assets, true ) ?  'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Plugin Admin Editor CSS', 'content-expiry-date' );?>
					</label>
				</li> -->
				<li>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->options_prefix );?>enqueue_back_end_assets[]" value="plugin_admin_js" <?php echo in_array( 'plugin_admin_js', $enqueued_assets, true ) ?  'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Plugin Admin JS', 'content-expiry-date' );?>
					</label>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Add the options page
	 */
	public function add_options_page() {
		add_submenu_page( 'options-general.php', esc_html__( 'Content Expiry Date', 'content-expiry-date' ), esc_html__( 'Content Expiry Date', 'content-expiry-date' ), 'manage_options', 'content_expiry_date', array( $this, 'render_options_page' ) );
	}

	/**
	 * Render the options page
	 */
	public function render_options_page() {
		$prefix   = $this->options_prefix;
		$settings = $prefix . 'settings';
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Content Expiry Date', 'content-expiry-date' );?></h2>
			<form action="options.php" method="POST">
	            <?php settings_fields( $settings . '_group' ); ?>
	            <?php do_settings_sections( $settings ); ?>
	            <?php submit_button(); ?>
	        </form>
		</div>
	<?php
	}

	/**
	 * Add 'Settings' action on installed plugin list
	 *
	 * @param  array $links The plugin links.
	 * @return array        The modified links
	 */
	public function add_setings_link( $links ) {
		array_unshift( $links, '<a href="' . esc_attr( $this->plugin_settings_url ) . '">' . esc_html__( 'Settings', 'content-expiry-date' ) . '</a>' );
		return $links;
	}

}
