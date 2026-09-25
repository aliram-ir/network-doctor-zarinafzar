<?php
/**
 * Fired when the plugin is deleted via the WordPress admin.
 *
 * @package network-doctor-zarinafzar
 */

// اگر مستقیم فراخوانی شده و از طریق فرآیند حذف رسمی وردپرس نیست، اجرا متوقف شود
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// پاکسازی تنظیمات ذخیره‌شده افزونه در جدول wp_options
delete_option( 'wpnd_fix_enabled' );
delete_option( 'wpnd_custom_timeout' );
delete_site_option( 'wpnd_fix_enabled' );
delete_site_option( 'wpnd_custom_timeout' );
