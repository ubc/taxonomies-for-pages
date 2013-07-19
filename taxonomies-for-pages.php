<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that also follow
 * WordPress coding standards and PHP best practices.
 *
 * @package   Taxonomies_For_Pages
 * @author    Julien <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Julien
 *
 * @wordpress-plugin
 * Plugin Name: Taxonomies for Pages
 * Plugin URI:  https://github.com/psmagicman/taxonomies-for-pages
 * Description: A WordPress plugin that adds categories and tags to pages.
 * Version:     1.0.0
 * Author:      Julien
 * Author URI:  https://github.com/psmagicman
 * Text Domain: plugin-name-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */



// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die( -1 );
}

define( 'TXP_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'TXP_BASENAME', plugin_basename( __FILE__ ) );
define( 'TXP_DIR_URL', plugins_url( '', TXP_BASENAME ) );
define( 'TXP_VIEWS_PATH', TXP_DIR_PATH . 'views/' );
define( 'TXP_LIB_PATH', TXP_DIR_PATH . 'lib/' );
define( 'TXP_JS_URL', TXP_DIR_URL . 'js/' );
define( 'TXP_CSS_URL', TXP_DIR_URL . 'css/' );

require_once( TXP_LIB_PATH . 'class.txp.php' );

register_activation_hook( __FILE__, array( 'TXP', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'TXP', 'deactivate' ) );

TXP::get_instance();