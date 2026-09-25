<?php
/**
 * Plugin Name:       Network Doctor by ZarinAfzar
 * Plugin URI:        https://zarinafzar.com
 * Description:       Diagnose outbound HTTP/API connections, inspect endpoints, purge update transients, and patch restrictive network filters.
 * Version:           1.2.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            ZarinAfzar
 * Author URI:        https://zarinafzar.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       network-doctor-zarinafzar
 * Domain Path:       /languages
 */

// جلوگیری از دسترسی مستقیم به فایل
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// تعریف ثابت‌های عمومی افزونه
define( 'WPND_VERSION', '1.2.0' );
define( 'WPND_PLUGIN_FILE', __FILE__ );
define( 'WPND_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPND_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * بارگذاری فایل‌های ترجمه محلی بر اساس استاندارد مخزن وردپرس
 */
function wpnd_load_textdomain() {
	load_plugin_textdomain(
		'network-doctor-zarinafzar',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'wpnd_load_textdomain', 5 );

/**
 * بارگذاری فایل‌های زیرساختی افزونه
 */
require_once WPND_PLUGIN_DIR . 'includes/class-wpnd-fixer.php';
require_once WPND_PLUGIN_DIR . 'includes/class-wpnd-admin.php';
require_once WPND_PLUGIN_DIR . 'includes/class-wpnd-core.php';

/**
 * راه‌اندازی نمونه اصلی افزونه پس از بارگذاری کامل هسته
 */
function wpnd_run_plugin() {
	$plugin = new WPND_Core();
	$plugin->run();
}
add_action( 'plugins_loaded', 'wpnd_run_plugin', 10 );
