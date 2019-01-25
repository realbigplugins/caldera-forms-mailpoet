<?php
/**
 * Plugin Name: Caldera Forms - MailPoet
 * Plugin URI: https://calderaforms.com/
 * Description: Integrates Caldera Forms with MailPoet so that form submissions can result in new subscribers getting added to MailPoet lists
 * Version: 1.0
 * Author: Real Big Plugins
 * Author URI: https://realbigplugins.com/
 * Text Domain: mp_cal_addon
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if ( ! defined( 'ABSPATH' ) ) exit;

register_activation_hook( __FILE__, [ 'MailPoet_CALDERA', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'MailPoet_CALDERA', 'deactivation' ] );

/**
 * Class MailPoet_CALDERA
 */
class MailPoet_CALDERA {

    public static $VERSION = '1.0';

	/**
	 * @var self
	 */
	private static $instance = null;

	/**
	 * @since 1.0
	 * @return $this
	 */
	public static function instance() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof MailPoet_CALDERA ) ) {
			self::$instance = new self;

			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Activation function hook
	 *
	 * @since 1.0
	 * @return void
	 */
	public function activation() {

		if ( ! current_user_can( 'activate_plugins' ) )
			return;
		
		if ( ! self::meets_requirements() ) {
			return ;
		}
	}

	/**
	 * Setup Constants
	 */
	private function setup_constants() {

		/**
		 * Directory
		 */
		define( 'MP_CAL_DIR', plugin_dir_path( __FILE__ ) );
		define( 'MP_CAL_DIR_FILE', MP_CAL_DIR . basename( __FILE__ ) );
		define( 'MP_CAL_INCLUDES_DIR', trailingslashit( MP_CAL_DIR . 'includes' ) );
		define( 'MP_CAL_BASE_DIR', plugin_basename( __FILE__ ) );
		
		/**
		 * URLS
		 */
		define( 'MP_CAL_PLUGIN_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );
		define( 'MP_CAL_ASSETS_URL', trailingslashit( MP_CAL_PLUGIN_URL . 'assets' ) );
	}

	/**
	 * Include Required Files
	 */
	private function includes() {

		if ( file_exists( MP_CAL_INCLUDES_DIR . 'base.php' ) ) {
			require_once( MP_CAL_INCLUDES_DIR . 'base.php' );
		}

		if ( file_exists( MP_CAL_INCLUDES_DIR . 'functions.php' ) ) {
			require_once( MP_CAL_INCLUDES_DIR . 'functions.php' );
		}

		include_once MP_CAL_DIR . '/vendor/autoload.php';

	}

	private function hooks() {

	   	/**
		 * filter to initialize the license system
		 */
		add_action( 'admin_init', [ $this, 'mp_cal_init_license' ] );

		add_action( 'plugins_loaded', [ $this, 'upgrade' ] );
		add_action( 'admin_notices', array( $this, 'disable_plugin' ) );
	}
	
	/**
     * License function
     */
	function mp_cal_init_license() {
		$plugin = array(
			'slug'      =>  'mailpoet-caldera-integration',
			'name'      =>  'MailPoet Caldera Integration',
			'author'    =>  'Real Big Plugins',
			'url'		=>  'https://calderaforms.com/',
			'version'	=>	( !empty( self::$VERSION ) ? self::$VERSION : '1.0' ),
			'key_store'	=>  'cf_mailpoet_license_key',
			'file'		=>  dirname( __FILE__ ),
		);
	
		new \calderawp\licensing_helper\licensing( $plugin );
	}

	/**
	 * Check if Caldera and MailPoet is available
	 *
	 * @since  1.0
	 * @return bool True if Caldera and MailPoet is available, false otherwise
	 */
	public function meets_requirements() {

		if ( ! defined( 'CFCORE_VER' ) || ! defined( 'MAILPOET_VERSION' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ), true );
			return false;
        } 
        
        return true;
	} 
	
	/**
	 * Display admin notifications that dependency not found.
	 */
	function disable_plugin() {
		if ( ! $this->meets_requirements() ) {
			deactivate_plugins( plugin_basename( __FILE__ ), true );
			$class = 'notice is-dismissible error';
			$message = __( 'MailPoet Caldera Integration add-on requires <a href="https://www.mailpoet.com/" target="_BLANK">Mail Poet</a> plugin and <a href="https://wordpress.org/plugins/caldera-forms/" target="_BLANK">Caldera Forms</a> plugin to be activated.', 'mp_cal_addon' );
			printf( '<div id="message" class="%s"> <p>%s</p></div>', $class, $message );
		}
	}
}

/**
 * @return MailPoet_CALDERA|bool
 */
function MailPoet_CALDERA() {

	$instance = MailPoet_CALDERA::instance();
	if ( ! $instance->meets_requirements() ) {
		return false;
	}
	$GLOBALS['mailpoet_caldera'] = $instance;

}
add_action( 'plugins_loaded', 'MailPoet_CALDERA' );