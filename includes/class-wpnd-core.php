<?php
/**
 * Core orchestrator for WP Network Doctor.
 *
 * @package network-doctor-zarinafzar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPND_Core
 */
class WPND_Core {

	/**
	 * نمونه ابزار رفع عیب (Fixer)
	 *
	 * @var WPND_Fixer
	 */
	protected $fixer;

	/**
	 * نمونه پنل ادمین (Admin)
	 *
	 * @var WPND_Admin
	 */
	protected $admin;

	/**
	 * سازنده هسته و بارگذاری اجزا
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->fixer = new WPND_Fixer();
		$this->admin = new WPND_Admin( $this->fixer );
	}

	/**
	 * لود تمام وابستگی‌های پروژه
	 */
	private function load_dependencies() {
		require_once WPND_PLUGIN_DIR . 'includes/class-wpnd-monitor.php';
		require_once WPND_PLUGIN_DIR . 'includes/class-wpnd-network.php';
		if ( file_exists( WPND_PLUGIN_DIR . 'includes/class-wpnd-firewall.php' ) ) {
			require_once WPND_PLUGIN_DIR . 'includes/class-wpnd-firewall.php';
		}
	}

	/**
	 * اجرای اصلی و ثبت لایف‌سایکل افزونه
	 */
	public function run() {
		// راه‌اندازی مانیتورینگ شبکه و کرون‌جاب
		WPND_Network::init();
	}
}
