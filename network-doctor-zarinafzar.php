<?php
/**
 * Plugin Name:       Network Doctor by ZarinAfzar
 * Description:       Diagnose outbound HTTP/API connections, measure endpoint latency, and isolate external requests during network failures.
 * Version:           1.2.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Ali Ramezani (ZarinAfzar)
 * Author URI:        https://zarinafzar.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       network-doctor-zarinafzar
 * Domain Path:       /languages
 *
 * @package Network_Doctor_By_ZarinAfzar
 */

// جلوگیری از دسترسی مستقیم به پرونده
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// تعریف ثوابت اصلی افزونه
define( 'WPND_VERSION', '1.2.0' );
define( 'WPND_PLUGIN_FILE', __FILE__ );
define( 'WPND_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPND_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * بارگذاری فایل‌های ترجمه محلی افزونه
 *
 * @return void
 */
function wpnd_load_textdomain() {
	load_plugin_textdomain(
		'network-doctor-zarinafzar',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'wpnd_load_textdomain', 5 );

// بارگذاری کلاس‌های پایه و ماژول‌های افزونه
require_once WPND_PLUGIN_DIR . 'includes/class-wpnd-network.php';
require_once WPND_PLUGIN_DIR . 'includes/class-wpnd-monitor.php';
require_once WPND_PLUGIN_DIR . 'includes/class-wpnd-firewall.php';
require_once WPND_PLUGIN_DIR . 'includes/class-wpnd-fixer.php';
require_once WPND_PLUGIN_DIR . 'includes/class-wpnd-admin.php';
require_once WPND_PLUGIN_DIR . 'includes/class-wpnd-core.php';

/**
 * اجرای هسته اصلی افزونه پس از بارگذاری کامل وردپرس
 *
 * @return void
 */
function wpnd_init_core() {
	$core = new WPND_Core();
	$core->run();
}
add_action( 'plugins_loaded', 'wpnd_init_core', 10 );
