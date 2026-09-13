<?php
/**
 * Main plugin class.
 *
 * @package Multisite_Unfiltered_HTML
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles capability grants and the plugin's administration screens.
 */
final class Multisite_Unfiltered_HTML {

	private const OPTION_NAME = 'multisite_unfiltered_html_roles';
	private const USER_META   = '_multisite_unfiltered_html';
	private const NONCE_USER  = 'multisite_unfiltered_html_user';
	private const NONCE_ROLES = 'multisite_unfiltered_html_roles';

	/**
	 * Registers the plugin hooks.
	 */
	public static function init() {
		add_filter( 'map_meta_cap', array( __CLASS__, 'map_unfiltered_html' ), 10, 4 );

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'admin_post_multisite_unfiltered_html_save_roles', array( __CLASS__, 'save_roles' ) );
		add_action( 'show_user_profile', array( __CLASS__, 'show_user_setting' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'show_user_setting' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'save_user_setting' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save_user_setting' ) );
		add_action( 'admin_notices', array( __CLASS__, 'show_multisite_notice' ) );
	}

	/**
	 * Overrides Core's multisite restriction for explicitly authorized users.
	 *
	 * @param string[] $caps    Primitive capabilities required by WordPress.
	 * @param string   $cap     Requested capability.
	 * @param int      $user_id User ID.
	 * @param mixed[]  $args    Additional capability arguments.
	 * @return string[]
	 */
	public static function map_unfiltered_html( $caps, $cap, $user_id, $args ) {
		unset( $args );

		if ( 'unfiltered_html' !== $cap || ! is_multisite() ) {
			return $caps;
		}

		// Never bypass the explicit security constant.
		if ( defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML ) {
			return $caps;
		}

		if ( is_super_admin( $user_id ) || ! self::user_is_authorized( $user_id ) ) {
			return $caps;
		}

		// "exist" is a primitive capability held by every valid logged-in user.
		return array( 'exist' );
	}

	/**
	 * Checks the individual grant and the roles on the current site.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	private static function user_is_authorized( $user_id ) {
		if ( ! get_userdata( $user_id ) || ! is_user_member_of_blog( $user_id ) ) {
			return false;
		}

		if ( '1' === (string) get_user_meta( $user_id, self::USER_META, true ) ) {
			return true;
		}

		return ! empty( self::get_authorized_user_roles( $user_id ) );
	}

	/**
	 * Returns the authorized roles assigned to a user on the current site.
	 *
	 * @param int $user_id User ID.
	 * @return string[]
	 */
	private static function get_authorized_user_roles( $user_id ) {
		$user             = get_userdata( $user_id );
		$authorized_roles = self::get_authorized_roles();

		if ( ! $user || empty( $authorized_roles ) ) {
			return array();
		}

		return array_values( array_intersect( $user->roles, $authorized_roles ) );
	}

	/**
	 * Gets sanitized role slugs from the current site's option.
	 *
	 * @return string[]
	 */
	private static function get_authorized_roles() {
		$roles = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $roles ) ) {
			return array();
		}

		return array_values( array_unique( array_map( 'sanitize_key', $roles ) ) );
	}

	/**
	 * Adds the site-specific settings page for super admins.
	 */
	public static function add_settings_page() {
		if ( ! is_multisite() || ! is_super_admin() ) {
			return;
		}

		add_options_page(
			__( 'Unfiltered HTML', 'multisite-unfiltered-html' ),
			__( 'Unfiltered HTML', 'multisite-unfiltered-html' ),
			'manage_network_options',
			'multisite-unfiltered-html',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Renders the role settings page.
	 */
	public static function render_settings_page() {
		if ( ! is_multisite() || ! is_super_admin() ) {
			wp_die(
				esc_html__( 'You are not allowed to access this page.', 'multisite-unfiltered-html' ),
				'',
				array( 'response' => 403 )
			);
		}

		$roles      = wp_roles()->roles;
		$authorized = self::get_authorized_roles();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only status flag.
		$updated = isset( $_GET['updated'] )
			? sanitize_text_field( wp_unslash( $_GET['updated'] ) )
			: '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Unfiltered HTML', 'multisite-unfiltered-html' ); ?></h1>

			<?php if ( '1' === $updated ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved.', 'multisite-unfiltered-html' ); ?></p>
				</div>
			<?php endif; ?>

			<p>
				<?php esc_html_e( 'Allow selected roles on this site to use unfiltered HTML.', 'multisite-unfiltered-html' ); ?>
			</p>
			<div class="notice notice-warning inline">
				<p>
					<strong><?php esc_html_e( 'Security warning:', 'multisite-unfiltered-html' ); ?></strong>
					<?php
					esc_html_e(
						'Users with this capability can save unsafe code, including scripts and iframes. Grant it only to trusted users.',
						'multisite-unfiltered-html'
					);
					?>
				</p>
			</div>

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="multisite_unfiltered_html_save_roles">
				<?php wp_nonce_field( self::NONCE_ROLES ); ?>
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'Roles', 'multisite-unfiltered-html' ); ?></th>
							<td>
								<fieldset>
									<legend class="screen-reader-text"><?php esc_html_e( 'Select roles', 'multisite-unfiltered-html' ); ?></legend>
									<?php foreach ( $roles as $role_slug => $role_data ) : ?>
										<label>
											<input
												type="checkbox"
												name="roles[]"
												value="<?php echo esc_attr( $role_slug ); ?>"
												<?php checked( in_array( $role_slug, $authorized, true ) ); ?>
											>
											<?php echo esc_html( translate_user_role( $role_data['name'] ) ); ?>
										</label><br>
									<?php endforeach; ?>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Saves the authorized roles for the current site.
	 */
	public static function save_roles() {
		if ( ! is_multisite() || ! is_super_admin() ) {
			wp_die(
				esc_html__( 'You are not allowed to change this setting.', 'multisite-unfiltered-html' ),
				'',
				array( 'response' => 403 )
			);
		}

		check_admin_referer( self::NONCE_ROLES );

		$submitted = isset( $_POST['roles'] )
			? map_deep( wp_unslash( $_POST['roles'] ), 'sanitize_key' )
			: array();

		if ( ! is_array( $submitted ) ) {
			$submitted = array();
		}

		$submitted = array_filter( $submitted, 'is_string' );
		$valid     = array_keys( wp_roles()->roles );
		$roles     = array_values( array_intersect( $submitted, $valid ) );

		update_option( self::OPTION_NAME, $roles, false );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'multisite-unfiltered-html',
					'updated' => '1',
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	/**
	 * Shows the individual user setting to super admins.
	 *
	 * @param WP_User $profile_user User being edited.
	 */
	public static function show_user_setting( $profile_user ) {
		if ( ! is_multisite() || ! is_super_admin() || ! $profile_user instanceof WP_User ) {
			return;
		}

		$role_slugs       = self::get_authorized_user_roles( $profile_user->ID );
		$granted_by_role  = ! empty( $role_slugs );
		$individual_grant = '1' === (string) get_user_meta( $profile_user->ID, self::USER_META, true );
		$role_names       = array();
		$all_roles        = wp_roles()->roles;

		foreach ( $role_slugs as $role_slug ) {
			if ( isset( $all_roles[ $role_slug ]['name'] ) ) {
				$role_names[] = translate_user_role( $all_roles[ $role_slug ]['name'] );
			}
		}
		?>
		<h2><?php esc_html_e( 'Unfiltered HTML', 'multisite-unfiltered-html' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><?php esc_html_e( 'Unfiltered HTML', 'multisite-unfiltered-html' ); ?></th>
				<td>
					<?php wp_nonce_field( self::NONCE_USER, '_multisite_unfiltered_html_nonce' ); ?>
					<label for="multisite-unfiltered-html-user">
						<input
							type="checkbox"
							id="multisite-unfiltered-html-user"
							name="multisite_unfiltered_html_user"
							value="1"
							<?php checked( $granted_by_role || $individual_grant ); ?>
							<?php disabled( $granted_by_role ); ?>
						>
						<?php esc_html_e( 'Allow this user to use unfiltered_html', 'multisite-unfiltered-html' ); ?>
					</label>
					<?php if ( $granted_by_role ) : ?>
						<p class="description">
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: %s: One or more role names. */
									esc_html__( 'The capability is already granted by the %s role.', 'multisite-unfiltered-html' ),
									'<strong>' . esc_html( wp_sprintf_l( '%l', $role_names ) ) . '</strong>'
								)
							);
							?>
						</p>
					<?php else : ?>
						<p class="description">
							<?php esc_html_e( 'This individual grant applies across the entire network.', 'multisite-unfiltered-html' ); ?>
						</p>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Saves the individual user setting.
	 *
	 * @param int $user_id User ID.
	 */
	public static function save_user_setting( $user_id ) {
		if ( ! is_multisite() || ! is_super_admin() || ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		if ( ! isset( $_POST['_multisite_unfiltered_html_nonce'] )
			|| ! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['_multisite_unfiltered_html_nonce'] ) ),
				self::NONCE_USER
			)
		) {
			return;
		}

		// A disabled, role-controlled checkbox is not submitted. Preserve any
		// existing individual choice so it becomes effective again if needed.
		if ( ! empty( self::get_authorized_user_roles( $user_id ) ) ) {
			return;
		}

		$individual_grant = isset( $_POST['multisite_unfiltered_html_user'] )
			? sanitize_text_field( wp_unslash( $_POST['multisite_unfiltered_html_user'] ) )
			: '';

		if ( '1' === $individual_grant ) {
			update_user_meta( $user_id, self::USER_META, '1' );
		} else {
			delete_user_meta( $user_id, self::USER_META );
		}
	}

	/**
	 * Explains that the plugin only has an effect in multisite.
	 */
	public static function show_multisite_notice() {
		if ( is_multisite() || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		?>
		<div class="notice notice-warning">
			<p>
				<?php
				esc_html_e(
					'Multisite Unfiltered HTML only works on a WordPress multisite network.',
					'multisite-unfiltered-html'
				);
				?>
			</p>
		</div>
		<?php
	}
}
