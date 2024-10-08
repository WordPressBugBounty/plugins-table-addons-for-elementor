<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Elementor Table Extension Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
class Table_Addons_For_Elementor_Extension {

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '3.1.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.4';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor (and other plugins) are loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init() {

		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}

		// Add Editing Mode Styles
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_elementor_editor_styles' ] );

		// Add Plugin actions
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'table-addons-for-elementor' ),
			'<strong>' . esc_html__( 'Table Addons for Elementor', 'table-addons-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'table-addons-for-elementor' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_post($message) );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %4$s or greater. %3$s of %1$s to work on your site.', 'table-addons-for-elementor' ),
			'<strong>' . esc_html__( 'Table Addons for Elementor', 'table-addons-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'table-addons-for-elementor' ) . '</strong>',
			'<a href="https://downloads.wordpress.org/plugin/table-addons-for-elementor.1.2.0.zip">' . esc_html__( 'Download old version', 'table-addons-for-elementor' ) . '</a>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_post($message) );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater. If you are using old version of PHP, Please download old version of the plugin. Or contact at support@fusionplugin.com', 'table-addons-for-elementor' ),
			'<strong>' . esc_html__( 'Table Addons for Elementor', 'table-addons-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'table-addons-for-elementor' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_post($message) );

	}

	/**
	 * Init Widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init_widgets() {

		// Include Widget files
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-table-addons-for-elementor-widget.php';

		// Register widget
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Table_Addons_For_Elementor_Widget() );

	}

	/**
	 * Enqueue editor panel styles
	 *
	 * Enqueue additional styles for Elementor editor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function enqueue_elementor_editor_styles() {
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			// Editor Styles
			wp_enqueue_style(
				'table-addons-for-elementor-editor',
				plugins_url( '/public/css/table-addons-for-elementor-editing-view.css', __DIR__ )
			);
		}
	}

}

Table_Addons_For_Elementor_Extension::instance();