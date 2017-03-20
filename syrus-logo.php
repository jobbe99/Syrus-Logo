<?php
/*
Plugin Name: Syrus Logo
Plugin URI: 
Description: Adds a custom logo to your wp-admin and login page.
Author: Syrus Industry
Version: 1.0
Author URI: http://www.syrusindustry.com
Text Domain: syrus-logo
Domain Path: /languages
License: GPL2
*/

// Plugin version
if ( ! defined( 'ADD_LOGO_VERSION' ) ) {
	define( 'ADD_LOGO_VERSION', '1.6.2' );
}

if ( ! class_exists( 'WP_Add_Logo_To_Admin' ) ) {
    class WP_Add_Logo_To_Admin {
        /**
         * Construct the plugin object
         */
        public function __construct() {
            $plugin_options = get_option( 'wp_add_logo_to_admin' );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'admin_init', array( $this, 'admin_init' ) );
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );

            if ( 'on' == $plugin_options['login'] ) {
            	add_action( 'login_enqueue_scripts', array( $this, 'login_enqueue_scripts' ) );
                add_filter( 'login_headertitle', array( $this, 'login_headertitle' ) );
                add_filter( 'login_headerurl', array( $this, 'login_headerurl' ) );
            }

        }

        public function admin_init() {
            register_setting( 'wp_add_logo_to_admin', 'wp_add_logo_to_admin', array( $this, 'wp_add_logo_to_admin_validation' ) );

            load_plugin_textdomain( 'syrus-logo', null, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        }

    	/**
    	 * Validation
    	 *
    	 * @since 1.6
    	 */
    	public function wp_add_logo_to_admin_validation( $input ) {
    		$input['login'] = ( empty( $input['login'] ) ) ? '' : 'on';
    		$input['admin'] = ( empty( $input['admin'] ) ) ? '' : 'on';
    		$input['image'] = esc_url( $input['image'] );

    		return $input;
    	}

        public function admin_menu() {
            add_options_page( __( 'Syrus Logo', 'syrus-logo' ), __( 'Syrus Logo', 'syrus-logo' ), 'manage_options', __FILE__, array( $this, 'add_logo_options_page' ) );
        }

        /**
         * Custom login logo URL
         *
         * This function is attached to the 'login_headerurl' filter hook.
         *
         * @since 1.6
         */
        public function login_headerurl() {
            return esc_url( home_url() );
        }

        /**
         * Custom login logo URL title
         *
         * This function is attached to the 'login_headertitle' filter hook.
         *
         * @since 1.6
         */
        public function login_headertitle() {
            return esc_attr( get_bloginfo( 'name' ) );
        }

        /**
         * Custom login screen
         *
         * This function is attached to the 'login_enqueue_scripts' filter hook.
         *
         * @since 1.6
         */
        function login_enqueue_scripts() {
            $plugin_options = get_option( 'wp_add_logo_to_admin' );
        	if ( $image = $plugin_options['image' ] ) { ?>
<style>
body.login div#login h1 a {
    background-image: url(<?php echo esc_url( $image ); ?>);
    background-size: inherit;
    width: 100%;
    height: 110px;
}
</style>
            <?php
            }
        }

        /**
         * Create the add logo to admin page
         *
         * This function is referenced in 'add_options_page()'.
         *
         * @since 1.6
         */
        public function add_logo_options_page() {
            if ( ! current_user_can( 'manage_options' ) )
                wp_die( __( 'Non hai i permessi per accedere a questa pagina.', 'syrus-logo' ) );

            $plugin_options = get_option( 'wp_add_logo_to_admin' );
            $image = ( $plugin_options['image'] ) ? '<img src="' . esc_url( $plugin_options['image'] ) . '" alt="" style="max-width: 100%;" />' : '';
            $display = ( $plugin_options['image'] ) ? '' : 'style="display: none;"';
        	?>
            <div class="wrap">
                <h2><?php _e( 'Aggiungi un Logo all\'admin e/o al Login', 'syrus-logo' ); ?></h2>
                <!-- Add Logo to Admin box begin-->
                <form method="post" action="options.php">
                    <?php settings_fields( 'wp_add_logo_to_admin' ); ?>

                    <table id="add-logo-table" class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e( 'Opzioni logo', 'syrus-logo' ); ?></th>
                            <td>
                                <fieldset>
                                	<label for="add-logo-on-login">
                                	<input name="wp_add_logo_to_admin[login]" id="add-logo-on-login" type="checkbox" <?php checked( esc_attr( $plugin_options['login'] ), 'on' ); ?>>
                                	<?php _e( 'Mostra il logo nella pagina di login', 'syrus-logo' ); ?></label>
                                	<br />
                                	<label for="add-logo-on-admin">
                                	<input name="wp_add_logo_to_admin[admin]" id="add-logo-on-admin" type="checkbox" <?php checked( esc_attr( $plugin_options['admin'] ), 'on' ); ?>>
                                	<?php _e( 'Mostra il logo in tutte le pagine admin', 'syrus-logo' ); ?></label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e( 'Carica logo', 'syrus-logo' ); ?></th>
                            <td>
                                <input type="hidden" id="add-logo-image" name="wp_add_logo_to_admin[image]" value="<?php echo esc_url( $plugin_options['image'] ); ?>" />
                                <div id="add-logo-image-container"><?php echo $image; ?></div>
                                <a href="#" class="select-image"><?php _e( 'Seleziona immagine', 'syrus-logo' ); ?></a>&nbsp;&nbsp;&nbsp;<a href="#" class="delete-image" <?php echo $display; ?>><?php _e( 'Cancella immagine', 'syrus-logo' ); ?></a>
                                <br />
                                <p class="description"><?php _e( 'Il tuo logo non dovrebbe superare i 320px per 80px oppure verrà ristretto nella pagina di login.', 'syrus-logo' ); ?></p>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(); ?>
                </form>
                <!-- Add Logo to Admin admin box end-->
            </div>
         <?php
         }

        /**
         * Set up the default options on activation
         *
         * This functions is referenced in 'register_activation_hook()'
         *
         * @since 1.6
         */
        public static function activate() {
            $default_option = array(
                'login' => 'on',
                'admin' => 'on',
                'image' => plugins_url( 'images/logo.png', __FILE__ )
            );

        	add_option( 'wp_add_logo_to_admin', $default_option );
        }

        /**
         * Remove all options on deactivation
         *
         * This functions is referenced in 'register_deactivation_hook()'
         *
         * @since 1.6
         */
        public static function deactivate() {
        	delete_option( 'wp_add_logo_to_admin' );
        }

        /**
         * Initialization of the plugin which creates the admin page
         *
         * This functions is attached to the 'admin_enqueue_scripts' action hook
         *
         * @since 1.6
         */
        public function admin_enqueue_scripts( $hook ) {
            $plugin_options = get_option( 'wp_add_logo_to_admin' );

            if ( 'settings_page_syrus-logo/syrus-logo' == $hook ) {
                wp_enqueue_media();
                wp_enqueue_script( 'add_logo_to_admin', plugins_url( 'js/add-logo-select-image.js', __FILE__ ), array( 'jquery', 'media-upload', 'media-views' ), ADD_LOGO_VERSION, true );
            }

            if ( 'on' == $plugin_options['admin'] ) {
                wp_enqueue_script( 'add_logo_jquery', plugins_url( 'js/add-logo.js', __FILE__ ), array( 'jquery' ), ADD_LOGO_VERSION, true );
                wp_localize_script( 'add_logo_jquery', 'add_logo_image', esc_url( $plugin_options['image'] ) );
                wp_enqueue_style( 'add_logo_to_admin', plugins_url( 'css/add-logo.css', __FILE__ ), '', ADD_LOGO_VERSION );
            }
        }

    } // END class WP_Plugin_Template
}

if ( class_exists( 'WP_Add_Logo_To_Admin' ) ) {
    /**
     * Installing the activation and deactivation hooks
     *
     * @since 1.6
     */
    register_activation_hook( __FILE__, array( 'WP_Add_Logo_To_Admin', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'WP_Add_Logo_To_Admin', 'deactivate' ) );

    // instantiate the plugin class
    $wp_add_logo_to_admin = new WP_Add_Logo_To_Admin();

    /**
     * Add settings link to plugin admin page
     *
     * @since 1.6
     */
    if ( isset( $wp_add_logo_to_admin ) ) {
        function add_logo_plugin_settings_link( $links ) {
            $settings_link = '<a href="options-general.php?page=syrus-logo/syrus-logo.php">' . __( 'Impostazioni', 'syrus-logo' ) . '</a>';
            array_unshift( $links, $settings_link );
            return $links;
        }

        $plugin = plugin_basename( __FILE__ );
        add_filter( "plugin_action_links_$plugin", 'add_logo_plugin_settings_link' );
    }
}
