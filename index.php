<?php
/**
 * Plugin Name:       Landbot
 * Description:       Landbot
 * Version:           1.1.0
 * Author:            Landbot team
 * Author URI:        https://landbot.io
 * Text Domain:       landbot
 */


/*
 * Plugin constants
 */
if(!defined('LANDBOT_PLUGIN_VERSION'))
	define('LANDBOT_PLUGIN_VERSION', '1.1.0');
if(!defined('LANDBOT_URL'))
	define('LANDBOT_URL', plugin_dir_url( __FILE__ ));
if(!defined('LANDBOT_PATH'))
	define('LANDBOT_PATH', plugin_dir_path( __FILE__ ));
if(!defined('LANDBOT_ENDPOINT'))
	define('LANDBOT_ENDPOINT', 'landbot.io');
if(!defined('LANDBOT_PROTOCOL'))
	define('LANDBOT_PROTOCOL', 'https');

/*
 * Main class
 */
/**
 * Class Landbot
 *
 * This class creates the option page and add the web app script
 */
class Landbot
{

	/**
	 * The security nonce
	 *
	 * @var string
	 */
	private $_nonce = 'landbot_admin';

	/**
	 * The option name
	 *
	 * @var string
	 */
    private $option_name = 'landbot_data';
	/**
	 * Landbot constructor.
     *  
     * The main plugin actions registered for WordPress
	 */
	public function __construct()
    {

		// Admin page calls
		add_action('admin_menu',                array($this,'addAdminMenu'));
		add_action('wp_ajax_store_admin_data',  array($this,'storeAdminData'));
		add_action('admin_enqueue_scripts',     array($this,'addAdminScripts'));

		// WC integration
        add_action('woocommerce_email_footer',  array($this,'wcFooter'));
        add_action( 'wp_enqueue_scripts', array($this, 'ajax_scripts') );

    }
    
    private function getWpdb() 
    {
        global $wpdb;

        return $wpdb;
    }

    private function getJalDbVersion()
    {
        global $jal_db_version;

        return $jal_db_version;
    }

	/**
	 * Returns the saved options data as an array
     *
     * @return array
	 */
	private function getData()
    {
	    return get_option($this->option_name, array());
    }

	/**
	 * Callback for the Ajax request
	 *
	 * Updates the options data
     *
     * @return void
	 */
	public function storeAdminData()
    {

		if (wp_verify_nonce($_POST['security'], $this->_nonce ) === false)
			die('Invalid Request! Reload your page please.');

        $table_name = $this->getWpdb()->prefix . 'landbot';
        
        // save params in DB table landbot
        $auth = $_POST['authorization'];

        $this -> createTable($table_name);
        $this -> insertToken($auth, $table_name);
		die();

    }

    /**
	 * create table in mysql
     *
     * @param $table_name string
     * 
     * @return void
	 */
    
    private function createTable($table_name) 
    {
	
        $charset_collate = $this->getWpdb()->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            token text
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $check = dbDelta( $sql );

        add_option( 'jal_db_version', $this->getJalDbVersion() );
        
    }

    /**
	 * insert token in landbot table
     *
     * @param $token string
     * @param $table_name string
     * 
     * @return void
	 */
    private function insertToken($token, $table_name) 
    {
        $this->getWpdb()->insert( 
            $table_name, 
            array( 
                'token' => $token,  
            ) 
        );

    }

	/**
	 * Adds Admin Scripts for the Ajax call
	 */
	public function addAdminScripts()
    {
	    wp_enqueue_style('landbot-admin', LANDBOT_URL. 'assets/css/admin.css', false, 1.0);

        wp_enqueue_script('polyfill', LANDBOT_URL. 'assets/js/polyfill.js', '', 1.1, true);
        wp_enqueue_script('service', LANDBOT_URL. 'assets/js/service.js', '', 1.1, true);
        wp_enqueue_script('errorHandler', LANDBOT_URL. 'assets/js/errorHandler.js', '', 1.1, true);
        wp_enqueue_script('utils', LANDBOT_URL. 'assets/js/utils.js', '', 1.1, true);
        
        wp_enqueue_script('landbot-admin', LANDBOT_URL. 'assets/js/admin.js', array(), 1.1);

		$admin_options = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_nonce'   => wp_create_nonce( $this->_nonce ),
		);

		wp_localize_script('landbot-admin', 'landbot_exchanger', $admin_options);

    }

	/**
	 * Adds the Landbot label to the WordPress Admin Sidebar Menu
	 */
	public function addAdminMenu()
    {
		add_menu_page(
			__( 'Landbot', 'landbot' ),
			__( 'Landbot', 'landbot' ),
			'manage_options',
			'landbot',
			array($this, 'adminLayout'),
			'dashicons-testimonial'
		);
	}

	/**
	 * Make an API call to the Landbot API and returns the response
     *
     * @param $private_key string
     *
     *
     * @return array
	 */
	private function getCustomers()
    {

        $data = array();
        
        $response = wp_remote_get( LANDBOT_PROTOCOL. '://api.'. LANDBOT_ENDPOINT .'/v1/customers/' ,
          array( 'timeout' => 3000,
          'headers' => array( 'Authorization' => $private_key) 
        ));

	    if (is_array($response) && !is_wp_error($response)) {
		    $data = json_decode($response['body'], true);
	    }

	    return $data;

    }

	/**
	 * Outputs the Admin Dashboard layout containing the form with all its options
     *
     * @return void
	 */
	public function adminLayout()
    {

		$data = $this->getData();

	    $api_response = $this->getCustomers($data['authorization']);

	    $not_ready = (empty($data['public_key']) || empty($api_response) || isset($api_response['error']));
	    $has_engager_preview = (isset($_GET['landbot-demo-engager']) && $_GET['landbot-demo-engager'] === 'go');

	    ?>

		<div class="wrap">

			<?php if ($has_engager_preview): ?>
                <p class="notice notice-warning p-10">
					<?php _e( 'The demo engager is enabled. You will see the widget, exactly as it will be displayed on your site.<br> The only difference is that until the preview is turned off it will always come back compared to the live version.', 'landbot' ); ?>
                </p>
			<?php endif; ?>


            <form id="landbot-admin-form" class="postbox">

                <div class="form-group inside">
                    <h1>
		                Add Landbot
                    </h1>

	                <?php if ($not_ready): ?>
                        <p>
                            <?php _e('Make sure you have a Landbot account first, it\'s free! 👍', 'landbot'); ?>
                            <?php _e('You can <a href="https://landbot.io" target="_blank">create an account here</a>.', 'landbot'); ?>
                            <br>
	                        <?php _e('Once the keys set and saved, if you do not see any option, please reload the page. Thank you, you rock 🤘', 'landbot'); ?>
                        </p>
                    <?php else: ?>
		                <?php _e('Access your <a href="https://landbot.io" target="_blank">Landbot dashboard here</a>.', 'landbot'); ?>
                    <?php endif; ?>

                        <div class="content-section">
                            <div>
                                <label>1. Copy and paste your landbot token here*</label>
                                <label>*You can find it under your Landbot > Share section</label>
                            </div>
                            <div class="authorization">
                                <div>
                                    TOKEN:
                                </div>
                                <div>
                                  <input
                                    class="regular-text" 
                                    name="authorization"
                                    id="authorization"
                                    placeholder="Token 7beqb8161dcbw715018i1f26axa8901b94az563b"
                                    type="text"
                                  />
                                </div>
                            </div>
                        </div>
                        <div class="content-section">
                            <div>
                                <label>2. Display Format</label>
                                <label>*The way Landbot is displayed</label>
                            </div>
                            <div>
                                <div onclick="checkDisplayFormat('FULLPAGE')" class="square-display-format display-format-color">
                                    <div>
                                      <img src="<?php echo plugin_dir_url( __FILE__ ) . '/assets/img/full.png'; ?>" alt="fullpage"/>
                                    </div>
                                    <div>
                                      FULLPAGE
                                    </div>  
                                </div>
                                <div onclick="checkDisplayFormat('POPUP')" class="square-display-format display-format-color">
                                    <div>
                                      <img src="<?php echo plugin_dir_url( __FILE__ ) . '/assets/img/popup.png'; ?>" alt="popup"/>
                                    </div>
                                    <div>
                                      POPUP
                                    </div>
                                </div>
                                <div onclick="checkDisplayFormat('EMBED')" class="square-display-format display-format-color">
                                    <div>
                                      <img src="<?php echo plugin_dir_url( __FILE__ ) . '/assets/img/embed.png'; ?>" alt="embed"/>
                                    </div>
                                    <div>
                                      EMBED
                                    </div>
                                </div>
                                <div onclick="checkDisplayFormat('LIVE CHAT')" class="square-display-format display-format-color">
                                    <div>
                                      <img src="<?php echo plugin_dir_url( __FILE__ ) . '/assets/img/LIVECHAT.png'; ?>" alt="LIVE CHAT"/>
                                    </div>
                                    <div>
                                      LIVE CHAT
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-section">
                            <div>
                                <label>3. More Options</label>
                            </div>
                            <div class="more-options">
                                <div>
                                    <div class="option-check">
                                        <div>
                                          Hide background
                                        </div>
                                        <div class="check-button" >
                                          <div onclick="checkMoreOptions(this, 'hideBackground')" class="square-click left"></div>
                                        </div>
                                    </div>
                                    <div class="option-check">
                                        <div>
                                          Hide header
                                        </div>
                                        <div class="check-button" >
                                          <div onclick="checkMoreOptions(this, 'hideHeader')" class="square-click left"></div>
                                        </div>
                                    </div>
                                </div>
                                <div id="embed-selected"></div>
                            </div>
                        </div>
                    <div id="alert-message"></div>
                </div>

                <hr>

                <div class="inside footer">

                    <button class="button button-primary" id="landbot-admin-save" type="submit">
                        Ok
                    </button>

                </div>
            </form>

		</div>

		<?php

	}
}

new Landbot();
