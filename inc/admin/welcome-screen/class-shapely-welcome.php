<?php

/**
 * Welcome Screen Class
 */
class Shapely_Welcome {

	/**
	 * Constructor for the welcome screen
	 */
	public function __construct() {
		/* create dashbord page */
		add_action( 'admin_menu', array( $this, 'shapely_welcome_register_menu' ) );

		/* enqueue script and style for welcome screen */
		add_action( 'admin_enqueue_scripts', array( $this, 'shapely_welcome_style_and_scripts' ) );

		/* enqueue script for customizer */
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'shapely_welcome_scripts_for_customizer' ) );

		/* ajax callback for dismissable required actions */
		add_action( 'wp_ajax_shapely_dismiss_required_action', array(
			$this,
			'shapely_dismiss_required_action_callback',
		) );
		add_action( 'wp_ajax_nopriv_shapely_dismiss_required_action', array(
			$this,
			'shapely_dismiss_required_action_callback',
		) );

		$theme = wp_get_theme();
		$this->theme_name = $theme->get( 'Name' );
		$this->theme_slug = $theme->get( 'TextDomain' );

		/**
		 * Add the notice in the admin backend
		 */
		$this->shapely_activation_admin_notice();

	}

	/**
	 * Creates the dashboard page
	 *
	 * @see   add_theme_page()
	 * @since 1.8.2.4
	 */
	public function shapely_welcome_register_menu() {
		$action_count = $this->count_actions();
		$title        = $action_count > 0 ? esc_html__( 'About Shapely', 'shapely' ) . '<span class="badge-action-count">' . esc_html( $action_count ) . '</span>' : esc_html__( 'About Shapely', 'shapely' );

		add_theme_page( 'About shapely', $title, 'edit_theme_options', 'shapely-welcome', array(
			$this,
			'shapely_welcome_screen',
		) );
	}

	/**
	 * Adds an admin notice upon successful activation.
	 *
	 * @since 1.8.2.4
	 */
	public function shapely_activation_admin_notice() {
		if ( ! class_exists( 'Epsilon_Notifications' ) ) {
			return;
		}

		if ( empty( $this->notice ) ) {
			$this->notice = '<img src="' . get_template_directory_uri() . '/inc/admin/welcome-screen/img/colorlib-logo-white2.png" class="epsilon-author-logo" />';

			/* Translators: %1$s - Theme Name */
			$this->notice .= '<h1>' . sprintf( esc_html__( 'Welcome to %1$s', 'shapely' ), $this->theme_name ) . '</h1>';
			$this->notice .= '<p>';
			$this->notice .=
				sprintf( /* Translators: Notice */
					esc_html__( 'Welcome! Thank you for choosing %3$s! To fully take advantage of the best our theme can offer please make sure you visit our %1$swelcome page%2$s.', 'shapely' ),
					'<a href="' . esc_url( admin_url( 'themes.php?page=' . $this->theme_slug . '-welcome' ) ) . '">',
					'</a>',
					$this->theme_name
				);
			$this->notice .= '</p>';
			/* Translators: %1$s - Theme Name */
			$this->notice .= '<p><a href="' . esc_url( admin_url( 'themes.php?page=' . $this->theme_slug . '-welcome' ) ) . '" class="button button-primary button-hero" style="text-decoration: none;"> ' . sprintf( esc_html__( 'Get started with %1$s', 'shapely' ), $this->theme_name ) . '</a></p>';

		}

		$notifications = Epsilon_Notifications::get_instance();
		$notifications->add_notice(
			array(
				'id'      => 'shapely_install_notice',
				'type'    => 'notice epsilon-big',
				'message' => $this->notice,
			)
		);
	}

	/**
	 * Display an admin notice linking to the welcome screen
	 *
	 * @since 1.8.2.4
	 */
	public function shapely_welcome_admin_notice() {
		?>
		<div class="updated notice is-dismissible">
			<p><?php echo sprintf( esc_html__( 'Welcome! Thank you for choosing Shapely! To fully take advantage of the best our theme can offer please make sure you visit our %1$swelcome page%2$s.', 'shapely' ), '<a href="' . esc_url( admin_url( 'themes.php?page=shapely-welcome' ) ) . '">', '</a>' ); ?></p>
			<p><a href="<?php echo esc_url( admin_url( 'themes.php?page=shapely-welcome' ) ); ?>" class="button"
				  style="text-decoration: none;"><?php echo esc_html__( 'Get started with Shapely', 'shapely' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Load welcome screen css and javascript
	 *
	 * @since  1.8.2.4
	 */
	public function shapely_welcome_style_and_scripts( $hook_suffix ) {

		wp_enqueue_style( 'shapely-welcome-screen-css', get_template_directory_uri() . '/inc/admin/welcome-screen/css/welcome.css' );
		wp_enqueue_script( 'shapely-welcome-screen-js', get_template_directory_uri() . '/inc/admin/welcome-screen/js/welcome.js', array( 'jquery' ) );

		wp_localize_script( 'shapely-welcome-screen-js', 'shapelyWelcomeScreenObject', array(
			'nr_actions_required'      => absint( $this->count_actions() ),
			'ajaxurl'                  => esc_url( admin_url( 'admin-ajax.php' ) ),
			'template_directory'       => esc_url( get_template_directory_uri() ),
			'no_required_actions_text' => esc_html__( 'Hooray! There are no required actions for you right now.', 'shapely' ),
		) );

	}

	/**
	 * Load scripts for customizer page
	 *
	 * @since  1.8.2.4
	 */
	public function shapely_welcome_scripts_for_customizer() {

		wp_enqueue_style( 'shapely-welcome-screen-customizer-css', get_template_directory_uri() . '/inc/admin/welcome-screen/css/welcome_customizer.css' );

	}

	/**
	 * Dismiss required actions
	 *
	 * @since 1.8.2.4
	 */
	public function shapely_dismiss_required_action_callback() {

		global $shapely_required_actions;

		$action_id = ( isset( $_GET['id'] ) ) ? $_GET['id'] : 0;

		echo esc_html( wp_unslash( $action_id ) ); /* this is needed and it's the id of the dismissable required action */

		if ( ! empty( $action_id ) ) :

			/* if the option exists, update the record for the specified id */
			if ( get_option( 'shapely_show_required_actions' ) ) :

				$shapely_show_required_actions = get_option( 'shapely_show_required_actions' );

				switch ( esc_html( $_GET['todo'] ) ) {
					case 'add';
						$shapely_show_required_actions[ $action_id ] = true;
						break;
					case 'dismiss';
						$shapely_show_required_actions[ $action_id ] = false;
						break;
					default:
						return new WP_Error( 'Action denied!', __( 'I\'ve fallen and can\'t get up', 'shapely' ) );
						break;
				}

				update_option( 'shapely_show_required_actions', $shapely_show_required_actions );

				/* create the new option,with false for the specified id */
			else :

				$shapely_show_required_actions_new = array();

				if ( ! empty( $shapely_required_actions ) ) :

					foreach ( $shapely_required_actions as $shapely_required_action ) :

						if ( $shapely_required_action['id'] == $action_id ) :
							$shapely_show_required_actions_new[ $shapely_required_action['id'] ] = false;
						else :
							$shapely_show_required_actions_new[ $shapely_required_action['id'] ] = true;
						endif;

					endforeach;

					update_option( 'shapely_show_required_actions', $shapely_show_required_actions_new );

				endif;

			endif;

		endif;

		die(); // this is required to return a proper result
	}

	/**
	 *
	 */
	public function count_actions() {
		global $shapely_required_actions;

		$shapely_show_required_actions = get_option( 'shapely_show_required_actions' );
		if ( ! $shapely_show_required_actions ) {
			$shapely_show_required_actions = array();
		}

		$i = 0;
		foreach ( $shapely_required_actions as $action ) {
			$true      = false;
			$dismissed = false;

			if ( ! $action['check'] ) {
				$true = true;
			}

			if ( ! empty( $shapely_show_required_actions ) && isset( $shapely_show_required_actions[ $action['id'] ] ) && ! $shapely_show_required_actions[ $action['id'] ] ) {
				$true = false;
			}

			if ( $true ) {
				$i ++;
			}
		}

		return $i;
	}

	public function call_plugin_api( $slug ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		$call_api = get_transient( 'shapely_plugin_information_transient_' . $slug );
		if ( false === $call_api ) {
			$call_api = plugins_api( 'plugin_information', array(
				'slug'   => $slug,
				'fields' => array(
					'downloaded'        => false,
					'rating'            => false,
					'description'       => false,
					'short_description' => true,
					'donate_link'       => false,
					'tags'              => false,
					'sections'          => true,
					'homepage'          => true,
					'added'             => false,
					'last_updated'      => false,
					'compatibility'     => false,
					'tested'            => false,
					'requires'          => false,
					'downloadlink'      => false,
					'icons'             => true,
				),
			) );
			set_transient( 'shapely_plugin_information_transient_' . $slug, $call_api, 30 * MINUTE_IN_SECONDS );
		}

		return $call_api;
	}

	public function check_active( $slug ) {
		$slug2 = Shapely_Notify_System::_get_plugin_basename_from_slug( $slug );

		$path = WP_PLUGIN_DIR . '/' . $slug2;
		if ( ! file_exists( $path ) ) {
			$path = false;
		}

		if ( file_exists( $path ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			$needs = is_plugin_active( $slug2 ) ? 'deactivate' : 'activate';

			return array(
				'status' => is_plugin_active( $slug2 ),
				'needs' => $needs,
			);
		}

		return array(
			'status' => false,
			'needs' => 'install',
		);
	}

	public function check_for_icon( $arr ) {
		if ( ! empty( $arr['svg'] ) ) {
			$plugin_icon_url = $arr['svg'];
		} elseif ( ! empty( $arr['2x'] ) ) {
			$plugin_icon_url = $arr['2x'];
		} elseif ( ! empty( $arr['1x'] ) ) {
			$plugin_icon_url = $arr['1x'];
		} else {
			$plugin_icon_url = $arr['default'];
		}

		return $plugin_icon_url;
	}

	public function create_action_link( $state, $slug ) {
		$slug2 = Shapely_Notify_System::_get_plugin_basename_from_slug( $slug );
		
		switch ( $state ) {
			case 'install':
				return wp_nonce_url(
					add_query_arg(
						array(
							'action' => 'install-plugin',
							'plugin' => $slug,
						),
						network_admin_url( 'update.php' )
					),
					'install-plugin_' . $slug
				);
				break;
			case 'deactivate':
				return add_query_arg( array(
					'action'        => 'deactivate',
					'plugin'        => rawurlencode( $slug2 ),
					'plugin_status' => 'all',
					'paged'         => '1',
					'_wpnonce'      => wp_create_nonce( 'deactivate-plugin_' . $slug2 ),
				), network_admin_url( 'plugins.php' ) );
				break;
			case 'activate':
				return add_query_arg( array(
					'action'        => 'activate',
					'plugin'        => rawurlencode( $slug2 ),
					'plugin_status' => 'all',
					'paged'         => '1',
					'_wpnonce'      => wp_create_nonce( 'activate-plugin_' . $slug2 ),
				), network_admin_url( 'plugins.php' ) );
				break;
		}
	}

	/**
	 * Welcome screen content
	 *
	 * @since 1.8.2.4
	 */
	public function shapely_welcome_screen() {
		require_once( ABSPATH . 'wp-load.php' );
		require_once( ABSPATH . 'wp-admin/admin.php' );
		require_once( ABSPATH . 'wp-admin/admin-header.php' );

		$shapely      = wp_get_theme();
		$active_tab   = isset( $_GET['tab'] ) ? wp_unslash( $_GET['tab'] ) : 'getting_started';
		$action_count = $this->count_actions();

		?>

		<div class="wrap about-wrap epsilon-wrap">

			<h1><?php echo esc_html__( 'Welcome to Shapely! - Version ', 'shapely' ) . esc_html( $shapely['Version'] ); ?></h1>

			<div
				class="about-text"><?php echo esc_html__( 'Shapely is now installed and ready to use! Get ready to build something beautiful. We hope you enjoy it! We want to make sure you have the best experience using shapely and that is why we gathered here all the necessary information for you. We hope you will enjoy using shapely, as much as we enjoy creating great products.', 'shapely' ); ?></div>

			<div class="wp-badge epsilon-welcome-logo"></div>


			<h2 class="nav-tab-wrapper wp-clearfix">
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=shapely-welcome&tab=getting_started' ) ); ?>"
				   class="nav-tab <?php echo 'getting_started' == $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Getting Started', 'shapely' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=shapely-welcome&tab=recommended_actions' ) ); ?>"
				   class="nav-tab <?php echo 'recommended_actions' == $active_tab ? 'nav-tab-active' : ''; ?> "><?php echo esc_html__( 'Recommended Actions', 'shapely' ); ?>
					<?php echo $action_count > 0 ? '<span class="badge-action-count">' . esc_html( $action_count ) . '</span>' : '' ?></a>
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=shapely-welcome&tab=recommended_plugins' ) ); ?>"
				   class="nav-tab <?php echo 'recommended_plugins' == $active_tab ? 'nav-tab-active' : ''; ?> "><?php echo esc_html__( 'Recommended Plugins', 'shapely' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=shapely-welcome&tab=support' ) ); ?>"
				   class="nav-tab <?php echo 'support' == $active_tab ? 'nav-tab-active' : ''; ?> "><?php echo esc_html__( 'Support', 'shapely' ); ?></a>
			</h2>

			<?php
			switch ( $active_tab ) {
				case 'getting_started':
					require_once get_template_directory() . '/inc/admin/welcome-screen/sections/getting-started.php';
					break;
				case 'recommended_actions':
					require_once get_template_directory() . '/inc/admin/welcome-screen/sections/actions-required.php';
					break;
				case 'recommended_plugins':
					require_once get_template_directory() . '/inc/admin/welcome-screen/sections/recommended-plugins.php';
					break;
				case 'support':
					require_once get_template_directory() . '/inc/admin/welcome-screen/sections/support.php';
					break;
				default:
					require_once get_template_directory() . '/inc/admin/welcome-screen/sections/getting-started.php';
					break;
			}
			?>


		</div><!--/.wrap.about-wrap-->

		<?php
	}
}

new shapely_Welcome();
