<?php

/**
 * 
 * This file runs when the plugin in uninstalled (deleted).
 * This will not run when the plugin is deactivated.
 * Ideally you will add all your clean-up scripts here
 * that will clean-up unused meta, options, etc. in the database.
 *
 */

// If plugin is not being uninstalled, exit (do nothing)
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Do something here if plugin is being uninstalled.
function removeDataBaseWhenDisablePlugin() {
  global $wpdb;
  $table_name = 'wp_landbot';
  $sql = "DROP TABLE IF EXISTS $table_name";
  $wpdb->query($sql);
}