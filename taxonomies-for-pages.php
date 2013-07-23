<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that also follow
 * WordPress coding standards and PHP best practices.
 *
 * @package   Taxonomies_For_Pages
 * @author    Julien <julien.law@ubc.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Julien
 *
 * @wordpress-plugin
 * Plugin Name: Taxonomies for Pages
 * Plugin URI:  https://github.com/ubc/taxonomies-for-pages
 * Description: A WordPress plugin that adds categories and tags to pages.
 * Version:     2.0.1
 * Author:      psmagicman
 * Author URI:  https://github.com/ubc
 * Text Domain: plugin-name-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */



// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( -1 );
}

define( 'TAXONOMIES_FOR_PAGES_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'TAXONOMIES_FOR_PAGES_BASENAME', plugin_basename( __FILE__ ) );
define( 'TAXONOMIES_FOR_PAGES_DIR_URL', plugins_url( '', TAXONOMIES_FOR_PAGES_BASENAME ) );
define( 'TAXONOMIES_FOR_PAGES_LIB_PATH', TAXONOMIES_FOR_PAGES_DIR_PATH . 'lib/' );

require_once( TAXONOMIES_FOR_PAGES_LIB_PATH . 'class.taxonomies-for-pages.php' );

register_activation_hook( __FILE__, array( 'TAXONOMIES_FOR_PAGES', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'TAXONOMIES_FOR_PAGES', 'deactivate' ) );

TAXONOMIES_FOR_PAGES::get_instance();