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

  public function __construct() {

    if(is_admin()){
      // Admin page calls
      add_action('admin_menu',                array($this,'addAdminMenu'));
      add_action('wp_ajax_store_admin_data',  array($this,'storeAdminData'));
      add_action('admin_enqueue_scripts',     array($this,'addAdminScripts'));
      // Shortcode
      add_shortcode('landbot', array($this, 'shortCode'));
    } else {
      add_action('wp_footer', array($this, 'printFooterScript'), 30);
    }
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

  private function landbotScript() {
    return 'https://static.landbot.io/landbot-widget/landbot-widget-1.0.0.js';
  }

  private function params($data) {
    return '?widget_hide_background='.get_object_vars($data)['hideBackground'].'&widget_hide_header='.get_object_vars($data)['hideHeader'].'';
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

    $embed = require('public/displayFormat/embed.php');
    $fullpage = require('public/displayFormat/fullpage.php');
    $liveChat = require('public/displayFormat/liveChat.php');
    $popup = require('public/displayFormat/popup.php');

	  $admin_options = array(
	    'ajax_url'      => admin_url( 'admin-ajax.php' ),
      '_nonce'        => wp_create_nonce( $this->_nonce ),
      'url'           => get_object_vars($data)['url'],
      'displayFormat' => get_object_vars($data)['displayFormat'],
      'hideBackground'=> get_object_vars($data)['hideBackground'],
      'hideHeader'    => get_object_vars($data)['hideHeader'],
      'widgetHeight'  => get_object_vars($data)['widgetHeight'],
      'positionTop'   => get_object_vars($data)['positionTop'],
      'shortCode'     => $shortCode,
      'popupCode'     => $popup($this->landbotScript(), $data, $this->params($data)),
      'fullpageCode'  => $fullpage($this->landbotScript(), $data, $this->params($data)),
      'liveChatCode'  => $liveChat($this->landbotScript(), $data, $this->params($data)),
      'embedCode'     => $embed($this->landbotScript(), $data, $this->params($data))
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

  /****** SHOW LANDBOT IN WORDPRESSPAGE *****/

  public function printFooterScript() {

    $data = $this->getDataConfigurationFromDB()[0];

    $embed = require('public/displayFormat/embed.php');
    $fullpage = require('public/displayFormat/fullpage.php');
    $liveChat = require('public/displayFormat/liveChat.php');
    $popup = require('public/displayFormat/popup.php');

    switch(get_object_vars($data)['displayFormat']) {
      case 'live chat':
        echo $liveChat($this->landbotScript(), $data, $this->params($data));
        break;
      case 'popup':
        echo $popup($this->landbotScript(), $data, $this->params($data));
        break;
      case 'embed':
        echo $embed($this->landbotScript(), $data, $this->params($data));
        break;
      case 'fullpage':
        echo $fullpage($this->landbotScript(), $data, $this->params($data));
        break;
      default:
        echo '';
    }
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
        
    $url = $_POST['authorization'];
    $displayFormat = strtolower($_POST['displayFormat']);
    $hideBackground = ($_POST['hideBackground'] === 'true');
    $hideHeader = ($_POST['hideHeader'] === 'true');
    $widgetHeight = intval($_POST['widgetHeight']);
    $positionTop = intval($_POST['positionTop']);

    if($this -> checkExistTableInDB($table_name)) {
      $this -> createTable($table_name);
      $this -> insertData($url, $displayFormat, $hideBackground, $hideHeader, $widgetHeight, $positionTop, $table_name);
    } else {
      $this -> updateData($url, $displayFormat, $hideBackground, $hideHeader, $widgetHeight, $positionTop, $table_name);
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
	    'url'         => get_object_vars($data)['url'],
      'format' => get_object_vars($data)['displayFormat'],
      'hide_background'=> get_object_vars($data)['hideBackground'] === '1' ? 'true' : 'false',
      'hide_header'    => get_object_vars($data)['hideHeader'] === '1' ? 'true' : 'false',
      'widget_height'  => get_object_vars($data)['widgetHeight'] === '0' ? null : get_object_vars($data)['widgetHeight']
 	  ), $atts );

	  return '[landbot url="'.$shortCode['url'].'" format="'.$shortCode['format'].'" hide_background="'.$shortCode['hide_background'].'" hide_header="'.$shortCode['hide_header'].'" widget_height="' . $shortCode['widget_height'] . '"]';  
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
      $data = $this->getWpdb()->get_results("SELECT url, displayFormat, hideBackground, hideHeader, widgetHeight, positionTop
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
      url tinytext,
      displayFormat text,
      hideBackground boolean,
      hideHeader boolean,
      widgetHeight int,
      positionTop int
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'jal_db_version', $this->getJalDbVersion() );
        
  }

  /**
   *
   * @param $url string
   * @param $displayFormat string
   * @param $hideBackground boolean
   * @param $hideHeader boolean
   * @param $widgetHeight integer
   * @param $table_name string
   * 
   * @return void
   */
  private function insertData($url, $displayFormat, $hideBackground, $hideHeader, $widgetHeight, $positionTop, $table_name) {
        
    $this->getWpdb()->insert( 
      $table_name, 
      array( 
        'url' => $url,
        'displayFormat' => $displayFormat,
        'hideBackground' =>  $hideBackground,
        'hideHeader' => $hideHeader,
        'widgetHeight' => $widgetHeight,
        'positionTop' => $positionTop
      ) 
    );
  }


  /**
   *
   * @param $url string
   * @param $displayFormat string
   * @param $hideBackground boolean
   * @param $hideHeader boolean
   * @param $widgetHeight integer
   * @param $table_name string
   * 
   * @return void
   */
  private function updateData($url, $displayFormat, $hideBackground, $hideHeader, $widgetHeight, $positionTop, $table_name) {
        
    $this->getWpdb()->update( 
      $table_name, 
      array( 
        'url' => $url,
        'displayFormat' => $displayFormat,
        'hideBackground' =>  $hideBackground,
        'hideHeader' => $hideHeader,
        'widgetHeight' => $widgetHeight,
        'positionTop' => $positionTop
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