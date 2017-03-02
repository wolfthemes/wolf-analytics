<?php
/**
 * Plugin Name: Wolf Analytics
 * Plugin URI: %LINK%
 * Description: %DESCRIPTION%
 * Version: %VERSION%
 * Author: %AUTHOR%
 * Author URI: %AUTHORURI%
 * Requires at least: %REQUIRES%
 * Tested up to: %TESTED%
 *
 * Text Domain: %TEXTDOMAIN%
 * Domain Path: /languages/
 *
 * @package WolfAnalytics
 * @category Core
 * @author %AUTHOR%
 *
 * Being a free product, this plugin is distributed as-is without official support.
 * Verified customers however, who have purchased a premium theme
 * at http://themeforest.net/user/Wolf-Themes/portfolio?ref=Wolf-Themes
 * will have access to support for this plugin in the forums
 * http://help.wolfthemes.com/
 *
 * Copyright (C) 2013 Constantin Saguin
 * This WordPress Plugin is a free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * It is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * See http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Wolf_Analytics' ) ) {
	/**
	 * Main Wolf_Analytics Class
	 *
	 * Contains the main functions for Wolf_Analytics
	 *
	 * @class Wolf_Analytics
	 * @version %VERSION%
	 * @since 1.0.0
	 */
	class Wolf_Analytics {

		/**
		 * @var string
		 */
		public $version = '%VERSION%';

		/**
		 * @var %NAME% The single instance of the class
		 */
		protected static $_instance = null;

		/**
		 * @var string
		 */
		private $update_url = 'http://plugins.wolfthemes.com/update';

		/**
		 * @var the support forum URL
		 */
		private $support_url = 'http://help.wolfthemes.com/';

		/**
		 * Main %NAME% Instance
		 *
		 * Ensures only one instance of %NAME% is loaded or can be loaded.
		 *
		 * @static
		 * @see WPM()
		 * @return %NAME% - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', '%TEXTDOMAIN%' ), '1.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', '%TEXTDOMAIN%' ), '1.0' );
		}

		/**
		 * %NAME% Constructor.
		 */
		public function __construct() {

			$this->init_hooks();
		}

		/**
		 * Hook into actions and filters
		 */
		private function init_hooks() {
			add_action( 'init', array( $this, 'init' ), 0 );

			// output in our theme custom hook if exist
			if ( function_exists( 'wolf_body_end' ) ) {
				
				add_action( 'wolf_body_end', array( $this, 'analytics_tracking_code' ) );
			
			} else {
				
				add_action( 'wp_footer', array( $this, 'analytics_tracking_code' ) );
			}

			// admin hooks
			add_action( 'admin_menu', array( $this, 'add_menu' ) );
			add_action( 'admin_init', array( $this, 'options' ) );

			// Plugin update notifications
			add_action( 'admin_init', array( $this, 'plugin_update' ) );

			// Plugin row meta
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'settings_action_links' ) );
		}

		/**
		 * Init %NAME% when WordPress Initialises.
		 */
		public function init() {
			// Set up localisation
			$this->load_plugin_textdomain();
		}

		/**
		 * Add admin menu
		 */
		public function add_menu() {

			add_management_page( esc_html( 'Analytics', '%TEXTDOMAIN%' ), esc_html( 'Analytics', '%TEXTDOMAIN%' ), 'activate_plugins', 'wolf-analytics', array( $this, 'analytics_settings' ) , 'dashicons-chart-line' );
		}

		/**
		 * Register options fields
		 */
		public function options() {

			register_setting( 'wolf-analytics-options', 'wolf_analytics_settings', array( $this, 'settings_validate' ) );
			add_settings_section( 'wolf-analytics-options', '', array( $this, 'section_intro' ), 'wolf-analytics-options' );
			add_settings_field( 'code', esc_html__( 'Your Google Analytics ID', 'wolf-analytics' ), array( $this, 'section_analytics_code' ), 'wolf-analytics-options', 'wolf-analytics-options' );
		}

		/**
		 * Validate options
		 *
		 * @return string
		 */
		public function settings_validate( $input ) {

			$input['google_id'] = sanitize_text_field( $input['google_id'] );
			return $input;

		}

		/**
		 * Intro used for debug and JS
		 */
		public function section_intro() {
			//global $options;
			//echo "<pre>";
			//print_r( get_option( 'wolf_analytics_settings' ) );
			//echo "</pre>";
		}

		/**
		 * Skin
		 */
		public function section_analytics_code() {
			?>
			<input type="text" placeholder="UA-84684236-1" value="<?php echo esc_attr( $this->get_option( 'google_id' ) ); ?>" name="wolf_analytics_settings[google_id]">
			<?php
		}

		/**
		 * Get player options
		 *
		 * @param string $value
		 * @return string
		 */
		public function get_option( $value ) {
			global $options;
			$settings = get_option( 'wolf_analytics_settings' );

			if ( isset( $settings[ $value ] ) ) {
				return $settings[ $value ];
			}
		}

		/**
		 * Print options form
		 *
		 * @return string
		 */
		public function analytics_settings() {
			?>
			<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h2><?php esc_html_e( 'Google Analytics ID', '%TEXTDOMAIN%' ); ?></h2>
				<?php if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) { ?>
				<div id="setting-error-settings_updated" class="updated settings-error">
					<p><strong><?php esc_html_e( 'Settings saved.', '%TEXTDOMAIN%' ); ?></strong></p>
				</div>
				<?php } ?>
				<form action="options.php" method="post">
					<?php settings_fields( 'wolf-analytics-options' ); ?>
					<?php do_settings_sections( 'wolf-analytics-options' ); ?>
					<p class="submit">
						<input name="save" type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', '%TEXTDOMAIN%' ); ?>">
					</p>
				</form>
			</div>
			<?php
		}

		/**
		 * Output analytics code in the page footer
		 *
		 * @return int
		 */
		public function analytics_tracking_code() {

			$google_id = esc_js( $this->get_option( 'google_id' ) );

			if ( $google_id ) {
				$tracking_code = "<script>
				  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

				  ga('create', '$google_id', 'auto');
				  ga('send', 'pageview');

				</script>";

				if ( 
					$tracking_code
					&& ! is_user_logged_in() 
				) {
					echo $tracking_code;
				}
			}
		}

		/**
		 * Loads the plugin text domain for translation
		 */
		public function load_plugin_textdomain() {

			$domain = '%TEXTDOMAIN%';
			$locale = apply_filters( '%TEXTDOMAIN%', get_locale(), $domain );
			load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Add settings link in plugin page
		 */
		public function settings_action_links( $links ) {
			$setting_link = array(
				'<a href="' . admin_url( 'tools.php?page=wolf-analytics' ) . '">' . esc_html__( 'Settings', '%TEXTDOMAIN%' ) . '</a>',
			);
			return array_merge( $links, $setting_link );
		}

		/**
		 * Plugin update
		 */
		public function plugin_update() {
			
			$plugin_data     = get_plugin_data( __FILE__ );
			$current_version = $plugin_data['Version'];
			$plugin_slug     = plugin_basename( dirname( __FILE__ ) );
			$plugin_path     = plugin_basename( __FILE__ );
			$remote_path     = $this->update_url . '/' . $plugin_slug;

			include_once( 'class-wa-update.php' );

			new WLFNLTCS_Update( $current_version, $remote_path, $plugin_path );
		}
	}
} // endif class exists

/**
 * Returns the main instance of WLFNLTCS to prevent the need to use globals.
 *
 * @return Wolf_Analytics
 */
function WLFNLTCS() {
	return Wolf_Analytics::instance();
}

WLFNLTCS(); // Go