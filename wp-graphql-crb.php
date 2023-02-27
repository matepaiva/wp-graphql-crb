<?php
/**
 * Plugin Name: WP GraphQL Carbon Fields
 * Description: A Wordpress wrapper to expose Carbon Fields to WpGraphQL queries
 * Version: 1.0.0
 * License: GPL2
 * Text Domain: wp-graphql-crb
 */

add_action('after_setup_theme', 'wp_graphql_crb_boot_plugin');
function wp_graphql_crb_boot_plugin() {
  require_once( __DIR__ . '/src/Container.php');
  require_once( __DIR__ . '/src/Field.php');
  require_once( __DIR__ . '/src/MetaResolver.php');
}
