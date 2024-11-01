<?php
/**
 * Plugin Name.
 *
 * @package   WP_Content_Permission
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-wp-content-permission-admin.php`
 *
 *
 * @package WP_Content_Permission
 * @author  Your Name <email@example.com>
 */
class WP_Content_Permission {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**

	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'wp-content-permission';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		
		
		add_shortcode( 'members', array( $this,'members_content' ));
		add_shortcode( 'guests', array( $this,'guests_content') );
		add_shortcode( 'login-form', array( $this,'oh_login_form' ));
		add_shortcode( 'member-name', array( $this,'oh_member_name' ));
		add_shortcode( 'register', array( $this,'oh_register' ));


		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		
		add_action('init', array( $this, 'app_output_buffer') );
		
		add_action( 'get_header', array( $this, 'action_method_name' ) );



	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	 
	public function members_content( $atts, $content = null ){
		if ( is_user_logged_in() ){
			return  do_shortcode( $content ) ;
		}
	}
	public function guests_content( $atts, $content = null ){
		if ( !is_user_logged_in() ){
			return do_shortcode( $content );
		}
	}
	public function oh_login_form( $atts ){
		if ( !is_user_logged_in() ){
			return wp_login_form(array('echo' =>false));
		}else{
			return apply_filters('the_content', get_option('ohmem-message'));
		}
	} 
	public function oh_member_name( $atts ){
		if ( is_user_logged_in() ){
			$current_user 	= wp_get_current_user();
			$nickname 		= $current_user->display_name;
			return $nickname;
		}
	} 
function oh_register($atts, $content = null) {
    //extract(shortcode_atts(), $atts ));
    ob_start();
	//$output='';
	if ( !is_user_logged_in() ){
        $redirect = get_option('ohmem-register-redirect') ? get_permalink(get_option('ohmem-register-redirect')): get_site_url('/');
    ?>
    <div id="register-form">

		<form action="<?php echo site_url('wp-login.php?action=register', 'login_post') ?>" method="post">
			<p>
			<label for="user_login"><?php _e('Username', 'ohtn'); ?><br>
			<input type="text" name="user_login" id="user_login" class="input" value="" size="20"></label>

			</p>
			<p>
			<label for="user_email"><?php _e('E-mail', 'ohtn'); ?> <br>
			<input type="text" name="user_email" id="user_email" class="input" value="" size="20"></label>
			</p>
			<p id="reg_passmail"><?php _e('A password will be e-mailed to you.', 'ohtn'); ?></p>
			<br class="clear">
			<?php do_action('register_form'); ?>
			<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Register"></p>
            <input type="hidden" name="redirect_to"  value="<?php echo $redirect ?>" >
		</form>
	</div>
    <?php
	}
    $output = ob_get_clean();
    //ob_end_clean();
    return $output;
}

	 
	public function app_output_buffer() {
		ob_start();
	} // soi_output_buffer 
	 
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		global $current_user;
		$user_id = $current_user->ID;
		/* If user clicks to ignore the notice, add that to their user meta */
		delete_user_meta($user_id, 'sogo_mem_ignore_notice');
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
        if(is_admin()){
            return false;
        }
        global $post;
        if( 'on'  == get_post_meta($post->ID, '_ohmem_is_protected', true) ){
            if(!is_user_logged_in()){
                $redirect = (get_option('ohmem-oops-page')? get_permalink(get_option('ohmem-oops-page')): get_site_url('/'));
                wp_redirect( $redirect );
                exit();
            }
        }
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

}
