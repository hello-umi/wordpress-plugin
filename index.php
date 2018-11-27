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

class Landbot {

  private $_nonce = 'landbot_admin';
  private $option_name = 'landbot_data';

  public function __construct() {
    // Admin page calls
    add_action('admin_menu',                array($this,'addAdminMenu'));
    add_action('wp_ajax_store_admin_data',  array($this,'storeAdminData'));
    add_action('admin_enqueue_scripts',     array($this,'addAdminScripts'));
    // WC integration
    add_action( 'wp_enqueue_scripts', array($this, 'ajax_scripts') );
    // Remove plugin
    register_deactivation_hook( __FILE__, array($this, 'removeDataBaseWhenDisablePlugin'));
  }
    
  private function getWpdb() {
    global $wpdb;

    return $wpdb;
  }

  private function getJalDbVersion() {
    global $jal_db_version;

    return $jal_db_version;
  }

  private function getTableName() {
    return $this->getWpdb()->prefix . 'landbot';  
  }

  function removeDataBaseWhenDisablePlugin() {
    $table_name = $this->getTableName();
    $sql = "DROP TABLE IF EXISTS $table_name";
    $this->getWpdb()->query($sql);
    delete_option("my_plugin_db_version");
  }   
  /**
   * Returns the saved options data as an array
   *
   * @return array
   */
  private function getData() {
    return get_option($this->option_name, array());
  }

  /**
   * Callback for the Ajax request
   *
   * Updates the options data
   *
   * @return void
   */
  public function storeAdminData() {

    if (wp_verify_nonce($_POST['security'], $this->_nonce ) === false)
	  die('Invalid Request! Reload your page please.');

    $table_name = $this->getTableName();
        
    $token = $_POST['authorization'];
    $displayFormat = strtolower($_POST['displayFormat']);
    $hideBackground = ($_POST['hideBackground'] === 'true');
    $hideHeader = ($_POST['hideHeader'] === 'true');
    $widgetHeight = intval($_POST['widgetHeight']);

    if($this -> checkExistTableInDB($table_name)) {
      $this -> createTable($table_name);
      $this -> insertData($token, $displayFormat, $hideBackground, $hideHeader, $widgetHeight, $table_name);
    } else {
      $this -> updateData($token, $displayFormat, $hideBackground, $hideHeader, $widgetHeight, $table_name);
    }
        
    die();

  }

  /**
  * create table in mysql
  *
  * @param $table_name string
  * 
  * @return boolean
  */
  private function checkExistTableInDB($table_name) {
    return $this->getWpdb()->get_var("SHOW TABLES LIKE '$table_name'") != $table_name;
  }

  /**
   * create table in mysql
   *
   * @param $table_name string
   * 
   * @return void
   */  
  private function createTable($table_name) {
    $charset_collate = $this->getWpdb()->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      token text,
      displayFormat text,
      hideBackground boolean,
      hideHeader boolean,
      widgetHeight int
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'jal_db_version', $this->getJalDbVersion() );
        
  }

  /**
   * insert data in landbot table
   *
   * @param $token string
   * @param $displayFormat string
   * @param $hideBackground boolean
   * @param $hideHeader boolean
   * @param $widgetHeight integer
   * @param $table_name string
   * 
   * @return void
   */
  private function insertData($token, $displayFormat, $hideBackground, $hideHeader, $widgetHeight, $table_name) {
        
    $this->getWpdb()->insert( 
      $table_name, 
      array( 
        'token' => $token,
        'displayFormat' => $displayFormat,
        'hideBackground' =>  $hideBackground,
        'hideHeader' => $hideHeader,
        'widgetHeight' => $widgetHeight 
      ) 
    );
    $this->getWpdb()->show_errors();
    echo $this->getWpdb()->last_query;
  }


  /**
   * insert data in landbot table
   *
   * @param $token string
   * @param $displayFormat string
   * @param $hideBackground boolean
   * @param $hideHeader boolean
   * @param $widgetHeight integer
   * @param $table_name string
   * 
   * @return void
   */
  private function updateData($token, $displayFormat, $hideBackground, $hideHeader, $widgetHeight, $table_name) {
        
    $this->getWpdb()->update( 
      $table_name, 
      array( 
        'token' => $token,
        'displayFormat' => $displayFormat,
        'hideBackground' =>  $hideBackground,
        'hideHeader' => $hideHeader,
        'widgetHeight' => $widgetHeight 
      ),
      array( 
        'id' => 1
      )
    );

    $this->getWpdb()->show_errors();
    echo $this->getWpdb()->last_query;
  }

  /**
  * Adds Admin Scripts for the Ajax call
  */
  public function addAdminScripts() {
	wp_enqueue_style('landbot-admin', LANDBOT_URL. 'assets/css/admin.css', false, 1.0);
    wp_enqueue_script('landbot-admin', LANDBOT_URL. 'assets/js/admin.js', array(), 1.1);

    wp_enqueue_script('polyfill', LANDBOT_URL. 'assets/js/polyfill.js', '', 1.1, true);
    wp_enqueue_script('service', LANDBOT_URL. 'assets/js/service.js', '', 1.1, true);
    wp_enqueue_script('errorHandler', LANDBOT_URL. 'assets/js/errorHandler.js', '', 1.1, true);
    wp_enqueue_script('utils', LANDBOT_URL. 'assets/js/utils.js', '', 1.1, true);
        

	$admin_options = array(
	  'ajax_url' => admin_url( 'admin-ajax.php' ),
	  '_nonce'   => wp_create_nonce( $this->_nonce ),
	);

	wp_localize_script('landbot-admin', 'landbot_exchanger', $admin_options);

  }

  /**
  * Adds the Landbot label to the WordPress Admin Sidebar Menu
  */
  public function addAdminMenu() {
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
  private function getCustomers() {

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
  public function adminLayout() {


  ?>

    <div class="wrap">
      <form id="landbot-admin-form" class="postbox">
        <div class="form-group inside">
          <h1>Add Landbot</h1>
          <p>You can <a href="https://landbot.io" target="_blank">create an account here.</a></p>
                           
	      <div class="content-section">
            <div>
              <label>1. Copy and paste your landbot token here*</label>
              <label>*You can find it under your Landbot > Share section</label>
            </div>
            <div class="authorization">
              <div> TOKEN: </div>
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
                <div> FULLPAGE </div>  
              </div>
              <div onclick="checkDisplayFormat('POPUP')" class="square-display-format display-format-color">
                <div>
                  <img src="<?php echo plugin_dir_url( __FILE__ ) . '/assets/img/popup.png'; ?>" alt="popup"/>
                </div>
                <div> POPUP </div>
              </div>
              <div onclick="checkDisplayFormat('EMBED')" class="square-display-format display-format-color">
                <div>
                  <img src="<?php echo plugin_dir_url( __FILE__ ) . '/assets/img/embed.png'; ?>" alt="embed"/>
                </div>
                <div> EMBED </div>
              </div>
              <div onclick="checkDisplayFormat('LIVE CHAT')" class="square-display-format display-format-color">
                <div>
                  <img src="<?php echo plugin_dir_url( __FILE__ ) . '/assets/img/LIVECHAT.png'; ?>" alt="LIVE CHAT"/>
                </div>
                <div> LIVE CHAT </div>
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
