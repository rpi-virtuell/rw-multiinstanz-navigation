<?php
/**
 *
 * @package   RPI Multi-Instanz Navigation
 * @author    Joachim Happel
 * @license   GPL-2.0+
 * @link      https://github.com/rpi-virtuell/rw-multiinstanz-navigation
 */

/*
 * Plugin Name:       RPI Multi-Instanz Navigation
 * Plugin URI:        https://github.com/rpi-virtuell/rw-multiinstanz-navigation
 * Description:       Topnaviationsleiste über allen Dienste von rpi-virtuell
 * Version:           1.2.0
 * Author:            Joachim Happel
 * Author URI:        http://joachim-happel.de
 * License:           GNU General Public License v2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path:       /languages
 * Text Domain:       rw-multiinstanz-navigation
 * Network:           false
 * GitHub Plugin URI: https://github.com/rpi-virtuell/rw-multiinstanz-navigation
 * GitHub Branch:     master
 * Requires WP:       4.0
 * Requires PHP:      5.3
 */


class RW_MultiInstanz_Navigation {
    /**
     * Plugin version
     *
     * @var     string
     * @since   0.0.1
     * @access  public
     */
    static public $version = "1.2.0";

    /**
     * Singleton object holder
     *
     * @var     mixed
     * @since   0.0.1
     * @access  private
     */
    static private $instance = NULL;

    /**
     * @var     mixed
     * @since   0.0.1
     * @access  public
     */
    static public $plugin_name = NULL;

    /**
     * @var     mixed
     * @since   0.0.1
     * @access  public
     */
    static public $textdomain = NULL;

    /**
     * @var     mixed
     * @since   0.0.1
     * @access  public
     */
    static public $plugin_base_name = NULL;

    /**
     * @var     mixed
     * @since   0.0.1
     * @access  public
     */
    static public $plugin_url = NULL;

    /**
     * @var     string
     * @since   0.0.1
     * @access  public
     */
    static public $plugin_filename = __FILE__;

    /**
     * @var     string
     * @since   0.0.1
     * @access  public
     */
    static public $plugin_version = '';


    /**
     * @var     array
     * @since   0.0.2
     * @access  public
     */
    static public $notice = array( 'label'=>'info' , 'message'=>'' );

    /**
     * @var     string
     * @since   0.0.2
     * @access  public
     */
    static public $plugin_dir = NULL;


    /**
     * Plugin constructor.
     *
     * @since   0.0.1
     * @access  public
     * @uses    plugin_basename
     * @action  rw_multiinstanz_navigation_init
     */
    public function __construct () {
        // set the textdomain variable
        self::$textdomain = self::get_textdomain();

        // The Plugins Name
        self::$plugin_name = $this->get_plugin_header( 'Name' );

        // The Plugins Basename
        self::$plugin_base_name = plugin_basename( __FILE__ );

        // The Plugins Version
        self::$plugin_version = $this->get_plugin_header( 'Version' );


        // absolute path to plugins root
        self::$plugin_dir = plugin_dir_path(__FILE__);

        // url to plugins root
        self::$plugin_url = plugins_url('/',__FILE__);

        // Load the textdomain
        $this->load_plugin_textdomain();

        // Add Filter & Actions
        // - https://codex.wordpress.org/Plugin_API/Action_Reference
        // - https://codex.wordpress.org/Plugin_API/Filter_Reference


        //@TODO  Hier Filter und Actions einbinden.


        add_action('init',                      array( 'RW_MultiInstanz_Navigation_Core','init' ) );
        do_action( 'rw_multiinstanz_navigation_init' );


        //@TODO uncomment what you need

        //enable and load css and js files
         add_action( 'wp_enqueue_scripts',       array( 'RW_MultiInstanz_Navigation_Core','enqueue_style' ) ,9999);
         add_action( 'wp_enqueue_scripts',       array( 'RW_MultiInstanz_Navigation_Core','enqueue_js' ) ,9999);

         do_action( 'rw_multiinstanz_navigation_enqueue' );

        //enable ajax
        add_action( 'admin_enqueue_scripts',    array( 'RW_MultiInstanz_Navigation_Core','enqueue_js' ) ,9999);
        add_action( 'wp_ajax_rw_multiinstanz_navigation_core_ajaxresponse' ,array( 'RW_MultiInstanz_Navigation_Core','ajaxresponse' )  );

        //because WordPress does not automatically do ajax actions for users not logged-in,we need this as workarround
        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'rw_multiinstanz_navigation_core_ajaxresponse' ):
            do_action( 'wp_ajax_' . $_REQUEST['action'] );
            do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
        endif;
        //enable an widget
        //add_action('widgets_init',             array( 'RW_MultiInstanz_Navigation_Widget','init' ) );
        //do_action( 'rw_multiinstanz_navigation_widget_init' );

        //enable options setting in backend
        add_action('init',             array( 'RW_MultiInstanz_Navigation_Settings','init' ) ,99);
        do_action( 'rw_multiinstanz_navigation_settings_init' );

        //enable custom template functions
        add_action( 'init',  array( 'RW_MultiInstanz_Navigation_Template','init' ) );
        do_action( 'rw_multiinstanz_navigation_template_init' );

        //enable buddypress functions
        //add_action( 'bp_include',      array( 'RW_MultiInstanz_Navigation_Buddypress','init' ) ,99);
        //do_action( 'rw_multiinstanz_navigation_buddypress_init' );

        if(!defined('REGISTER_URL')){
            define('REGISTER_URL', "https://konto.rpi-virtuell.de/registrieren/?ref_service=" . urlencode(get_home_url()));
        }
    }

    /**
     * Creates an Instance of this Class
     *
     * @since   0.0.1
     * @access  public
     * @return  Object
     */
    public static function get_instance() {

        if ( NULL === self::$instance )
            self::$instance = new self;

        return self::$instance;
    }

    /**
     * Load the localization
     *
     * @since	0.0.1
     * @access	public
     * @uses	load_plugin_textdomain, plugin_basename
     * @filters @TODO rw_sticky_activity_translationpath path to translations files
     * @return	void
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain( self::get_textdomain(), false, apply_filters ( 'rw_multiinstanz_navigation_domain', dirname( plugin_basename( __FILE__ )) .  self::get_textdomain_path() ) );
    }

    /**
     * Get a value of the plugin header
     *
     * @since   0.0.1
     * @access	protected
     * @param	string $value
     * @uses	get_plugin_data, ABSPATH
     * @return	string The plugin header value
     */
    protected function get_plugin_header( $value = 'TextDomain' ) {

        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . '/wp-admin/includes/plugin.php');
        }

        $plugin_data = get_plugin_data( __FILE__ );
        $plugin_value = $plugin_data[ $value ];

        return $plugin_value;
    }

    /**
     * get the textdomain
     *
     * @since   0.0.1
     * @static
     * @access	public
     * @return	string textdomain
     */
    public static function get_textdomain() {
        if( is_null( self::$textdomain ) )
            self::$textdomain = self::get_plugin_data( 'TextDomain' );

        return self::$textdomain;
    }

    /**
     * get the textdomain path
     *
     * @since   0.0.1
     * @static
     * @access	public
     * @return	string Domain Path
     */
    public static function get_textdomain_path() {
        return self::get_plugin_data( 'DomainPath' );
    }

    /**
     * return plugin comment data
     *
     * @since   0.0.1
     * @uses    get_plugin_data
     * @access  public
     * @param   $value string, default = 'Version'
     *		Name, PluginURI, Version, Description, Author, AuthorURI, TextDomain, DomainPath, Network, Title
     * @return  string
     */
    public static function get_plugin_data( $value = 'Version' ) {

        if ( ! function_exists( 'get_plugin_data' ) )
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

        $plugin_data  = get_plugin_data ( __FILE__ );
        $plugin_value = $plugin_data[ $value ];

        return $plugin_value;
    }


    /**
     * creates an admin notification on admin pages
     *
     * @since   0.0.2
     * @uses     _notice_admin
     * @access  public
     * @param label         $value string,  default = 'info'
     *        error, warning, success, info
     * @param message       $value string
     * @param $dismissible  $value bool,  default = false
     *
     */
    public static function notice_admin($label=info, $message, $dismissible=false ) {
        $notice = array(
            'label'             =>  $label
        ,   'message'           =>  $message
        ,   'is-dismissible'    =>  (bool)$dismissible

        );
        self::_notice_admin($notice);
    }

    /**
     * creates an admin notification on admin pages
     *
     * @since   0.0.2
     * @uses     _notice_admin
     * @access  private
     * @param $value array
     * @link https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices
     */

    static function _notice_admin($notice) {

        self::$notice = $notice;

        add_action( 'admin_notices',function(){

            $note = RW_MultiInstanz_Navigation::$notice;
            $note['IsDismissible'] =
                (isset($note['is-dismissible']) && $note['is-dismissible'] == true) ?
                    ' is-dismissible':'';
            ?>
            <div class="notice notice-<?php echo $note['label']?><?php echo $note['IsDismissible']?>">
                <p><?php echo __( $note['message'] ,RW_MultiInstanz_Navigation::get_textdomain() ); ?></p>
            </div>
            <?php
        });

    }

}


if ( class_exists( 'RW_MultiInstanz_Navigation' ) ) {


    add_action( 'plugins_loaded', array( 'RW_MultiInstanz_Navigation', 'get_instance' ) );

    require_once 'inc/RW_MultiInstanz_Navigation_Autoloader.php';
    RW_MultiInstanz_Navigation_Autoloader::register();

    register_activation_hook( __FILE__, array( 'RW_MultiInstanz_Navigation_Installation', 'on_activate' ) );
    register_uninstall_hook(  __FILE__,	array( 'RW_MultiInstanz_Navigation_Installation', 'on_uninstall' ) );
    register_deactivation_hook( __FILE__, array( 'RW_MultiInstanz_Navigation_Installation', 'on_deactivation' ) );
}

