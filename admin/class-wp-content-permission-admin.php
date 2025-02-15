<?php
/**
 * Plugin Name.
 *
 * @package   WP_Content_Permission_Admin
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-plugin-name.php`
 *

 *
 * @package WP_Content_Permission_Admin
 * @author  Your Name <email@example.com>
 */
class WP_Content_Permission_Admin {

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Slug of the plugin screen.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_screen_hook_suffix = null;

    protected $ohmem_fields = array();

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     */
    private function __construct() {

        /*
         * @TODO :
         *
         * - Uncomment following lines if the admin class should only be available for super admins
         */
        /* if( ! is_super_admin() ) {
            return;
        } */

        /*
         * Call $plugin_slug from public plugin class.
         *
         * @TODO:
         *
         * - Rename "WP_Content_Permission" to the name of your initial plugin class
         *
         */
        $plugin = WP_Content_Permission::get_instance();
        $this->plugin_slug = $plugin->get_plugin_slug();

        // Load admin style sheet and JavaScript.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        // Add the options page and menu item.
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

        // Add an action link pointing to the options page.
        $plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
        add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

        /*
         * Define custom functionality.
         *
         * Read more about actions and filters:
         * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
         */
        add_action( 'admin_init', array( $this, 'oh_mem_register_settings' ) );
        add_action( 'admin_notices', array( $this, 'add_notice' ) );
        add_action( 'admin_init', array( $this, 'dismiss_notice' ) );
        add_action( 'add_meta_boxes', array( $this, 'ohmem_custom_box' ) );
        add_action( 'save_post', array( $this, 'ohmem_save_postdata' ) );
//		add_filter( '@TODO', array( $this, 'filter_method_name' ) );

        $this->ohmem_fields = array(
            array(
                "id" => "_ohmem_is_protected",
                "name" => __('Members Only', 'ohtn'),
                "type" => "checkbox"
            )
        );

    }

    public function add_notice(){
        global $current_user ;
        $user_id = $current_user->ID;
        /* Check that the user hasn't already clicked to ignore the message */
        if ( ! get_user_meta($user_id, 'sogo_mem_ignore_notice') ) {
            echo '<div class="updated"><p>';
            printf(__('Great! Content Permission by <a href="%1$s">SOGO</a> is installed, feel free to <a href="%2$s">vote</a> for us.? | <a href="%3$s">Hide Notice</a>'),
                'http://sogo.co.il/',
                'https://wordpress.org/support/view/plugin-reviews/wp-content-permission#postform',
                '?mem_ignore=0');
            echo "</p></div>";
        }
    }

    public function dismiss_notice(){
        global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if ( isset($_GET['mem_ignore']) && '0' == $_GET['mem_ignore'] ) {
            add_user_meta($user_id, 'sogo_mem_ignore_notice', 'true', true);
        }
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        /*
         * @TODO :
         *
         * - Uncomment following lines if the admin class should only be available for super admins
         */
        /* if( ! is_super_admin() ) {
            return;
        } */

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Register and enqueue admin-specific style sheet.
     *
     * - Rename "WP_Content_Permission" to the name your plugin
     *
     * @since     1.0.0
     *
     * @return    null    Return early if no settings page is registered.
     */
    public function enqueue_admin_styles() {

        if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
            return;
        }

        $screen = get_current_screen();
        if ( $this->plugin_screen_hook_suffix == $screen->id ) {
            wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), WP_Content_Permission::VERSION );
        }

    }

    /**
     * Register and enqueue admin-specific JavaScript.
     *
     *
     * @since     1.0.0
     *
     * @return    null    Return early if no settings page is registered.
     */
    public function enqueue_admin_scripts() {

        if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
            return;
        }

        $screen = get_current_screen();
        if ( $this->plugin_screen_hook_suffix == $screen->id ) {
            wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), WP_Content_Permission::VERSION );
        }

    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {

        /*
         * Add a settings page for this plugin to the Settings menu.
         *
         * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
         *
         *        Administration Menus: http://codex.wordpress.org/Administration_Menus
         *

         * - Change 'manage_options' to the capability you see fit
         *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
         */
        $this->plugin_screen_hook_suffix = add_options_page(
            __( 'WP Content Permission Configuration', $this->plugin_slug ),
            __( 'WP Content Permission', $this->plugin_slug ),
            'manage_options',
            $this->plugin_slug,
            array( $this, 'display_plugin_admin_page' )
        );

    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once('views/admin.php');
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */
    public function add_action_links( $links ) {

        return array_merge(
            array(
                'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
            ),
            $links
        );

    }

    /**
     * NOTE:     Actions are points in the execution of a page or process
     *           lifecycle that WordPress fires.
     *
     *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
     *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
     *
     * @since    1.0.0
     */
    public function oh_mem_register_settings() {
        register_setting( 'ohmem-settings-group', 'ohmem-oops-page' );
        register_setting( 'ohmem-settings-group', 'ohmem-message' );
        register_setting( 'ohmem-settings-group', 'ohmem-register-redirect' );


    }

    public function ohmem_custom_box() {
        $post_types=get_post_types(array('public'=>true,'_builtin'=>false));
        array_push($post_types,'post','page');

        foreach($post_types as $post_type){
            add_meta_box(
                'ohmem_properties',
                __( 'Members Properties','ohtn'),
                array( $this, 'ohmem_print_custom_box' ),
                $post_type,
                'side',
                'high'

            );
        }


    }

    public function ohmem_print_custom_box( $post, $args) {
        wp_nonce_field( plugin_basename( __FILE__ ), 'oh_fields_noncename' );
        $val  = get_post_meta($post->ID,'_ohmem_is_protected', true) ;

        echo '<div class="oh_editor_box"> ';
        echo "<input id ='_ohmem_is_protected' type='checkbox'
         name='_ohmem_is_protected'  ". checked('on', $val, false) ."/> <label for='_ohmem_is_protected'>".__("This page is for members only.")."</label>";
        echo '</div>';
        //echo $item;
    }

    /* When the post is saved, saves our custom data */
    public function ohmem_save_postdata( $post_id ) {

        // verify if this is an auto save routine.
        // If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times

        if (!isset($_POST['oh_fields_noncename']) || !wp_verify_nonce( $_POST['oh_fields_noncename'], plugin_basename( __FILE__ ) ) )
            return;


        if ( !current_user_can( 'edit_post', $post_id ) )
            return;

        // OK, we're authenticated: we need to find and save the data


        if(isset($_POST['_ohmem_is_protected'])){
            update_post_meta($post_id, '_ohmem_is_protected', 'on');
        }else{
            delete_post_meta($post_id, '_ohmem_is_protected' );
        }

    }

    /* Do something with the data entered */


    /**
     * NOTE:     Filters are points of execution in which WordPress modifies data
     *           before saving it or sending it to the browser.
     *
     *           Filters: http://codex.wordpress.org/Plugin_API#Filters
     *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
     *
     * @since    1.0.0
     */
    public function filter_method_name() {
        // @TODO: Define your filter hook callback here
    }

}
