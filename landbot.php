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
    // Shortcode
    add_shortcode('landbot', array($this, 'shortCode'));
  }

  /******* GLOBAL VALUES AND OTHERS ********/
    
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

  /******* INITIAL CONFIG ********/

   /**
  * Adds Admin Scripts for the Ajax call
  */
  public function addAdminScripts() {
	  wp_enqueue_style('landbot-admin', LANDBOT_URL. 'admin/css/admin.css', false, 1.0);
    wp_enqueue_script('landbot-admin', LANDBOT_URL. 'admin/js/admin.js', array(), 1.1);

    wp_enqueue_script('polyfill', LANDBOT_URL. 'admin/js/polyfill.js', '', 1.1, true);
    wp_enqueue_script('service', LANDBOT_URL. 'admin/js/service.js', '', 1.1, true);
    wp_enqueue_script('errorHandler', LANDBOT_URL. 'admin/js/errorHandler.js', '', 1.1, true);
    wp_enqueue_script('utils', LANDBOT_URL. 'admin/js/utils.js', '', 1.1, true);
    
    $data = $this->getDataConfigurationFromDB()[0];

    $shortCode = $this->shortCode($data);

	  $admin_options = array(
	    'ajax_url'      => admin_url( 'admin-ajax.php' ),
      '_nonce'        => wp_create_nonce( $this->_nonce ),
      'token'         => get_object_vars($data)['token'],
      'displayFormat' => get_object_vars($data)['displayFormat'],
      'hideBackground'=> get_object_vars($data)['hideBackground'],
      'hideHeader'    => get_object_vars($data)['hideHeader'],
      'widgetHeight'  => get_object_vars($data)['widgetHeight'],
      'shortCode'     => $shortCode,
      'shorcodeExist' => $shortCodeExist
	  );

	  wp_localize_script('landbot-admin', 'landbot_constants', $admin_options);

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

    $data = $this->getDataConfigurationFromDB()[0];

    $shortCode = $this->shortCode($data);
        
    die();

  }

  /******* UTILS ********/

  /**
   * Generate short code
   */
  private function shortCode($data) {
    $shortCode = shortcode_atts( array(
	    'url'         => 'https://api.landbot.io/',
      'displayFormat' => get_object_vars($data)['displayFormat'],
      'hideBackground'=> (get_object_vars($data)['hideBackground'] === '1'),
      'hideHeader'    => (get_object_vars($data)['hideHeader'] === '1'),
      'widgetHeight'  => get_object_vars($data)['widgetHeight'],
 	  ), $atts );

	  return $shortCode;  
  }

  /******* DATA BASE QUERYS ********/

  /**
  *
  * @param $table_name string
  * 
  * @return boolean
  */
  private function checkExistTableInDB($table_name) {
    return $this->getWpdb()->get_var("SHOW TABLES LIKE '$table_name'") != $table_name;
  }

  /**
  * Get data saved in db
  * 
  * @return array elements config
  */
  private function getDataConfigurationFromDB() {
    $table_name = $this->getTableName();
    if(!$this -> checkExistTableInDB($table_name)) {
      $data = $this->getWpdb()->get_results("SELECT token, displayFormat, hideBackground, hideHeader, widgetHeight 
                                              FROM $table_name WHERE id=1", OBJECT);
      return $data;
    }    
  }

  /**
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
  }


  /**
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
  }

  /******* API CALLS ********/

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

  /******* RENDER TEMPLATE ********/

  /**
  * Outputs the Admin Dashboard layout containing the form with all its options
  *
  * @return void
  */
  public function adminLayout() {
    $configurationTemplate = plugin_dir_path( __FILE__ ) . 'admin/template/configuration.php';
    include_once $configurationTemplate;
  }
}

new Landbot();