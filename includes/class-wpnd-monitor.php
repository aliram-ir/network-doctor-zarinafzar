<?php
/**
 * کلاس پایش و ثبت لاگ درخواست‌های شبکه و رویدادهای تشخیصی
 *
 * @package Network_Doctor_By_ZarinAfzar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * کلاس مانیتورینگ و رهگیری وضعیت شبکه و درخواست‌ها
 */
class WPND_Monitor {

	/**
	 * کلید نگهداری لاگ‌ها در جدول گزینه‌های دیتابیس
	 */
	const LOG_OPTION_KEY = 'wpnd_diagnostic_logs';

	/**
	 * حداکثر تعداد لاگ‌های ذخیره‌شده
	 */
	const MAX_LOG_ENTRIES = 50;

	/**
	 * کلید ذخیره هش وضعیت مانیتورینگ
	 */
	const STATE_HASH_KEY = 'wpnd_monitor_state_hash';

	/**
	 * دریافت تاریخچه درخواست‌های ثبت‌شده شبکه
	 *
	 * @return array
	 */
	public static function get_recent_logs() {
		$logs = get_option( self::LOG_OPTION_KEY, array() );
		return is_array( $logs ) ? $logs : array();
	}

	/**
	 * ثبت یک رویداد تشخیصی یا نتیجه تست اتصال
	 *
	 * @param string $endpoint آدرس مقصد یا عنوان تست.
	 * @param string $status   وضعیت (موفق، ناموفق، مسدود).
	 * @param float  $latency  مدت زمان پاسخ‌دهی به ثانیه.
	 * @param string $message  پیام توضیحی تکمیلی.
	 * @return bool
	 */
	public static function log_event( $endpoint, $status, $latency = 0.0, $message = '' ) {
		$logs = self::get_recent_logs();

		$new_entry = array(
			'timestamp' => current_time( 'timestamp' ),
			'time_text' => current_time( 'mysql' ),
			'endpoint'  => sanitize_text_field( $endpoint ),
			'status'    => sanitize_text_field( $status ),
			'latency'   => (float) $latency,
			'message'   => sanitize_text_field( $message ),
		);

		array_unshift( $logs, $new_entry );

		if ( count( $logs ) > self::MAX_LOG_ENTRIES ) {
			$logs = array_slice( $logs, 0, self::MAX_LOG_ENTRIES );
		}

		return update_option( self::LOG_OPTION_KEY, $logs, 'no' );
	}

	/**
	 * پاکسازی تاریخچه رویدادهای ثبت‌شده
	 *
	 * @return bool
	 */
	public static function clear_logs() {
		return delete_option( self::LOG_OPTION_KEY );
	}

	/**
	 * متد سازگاری رابط کاربری جهت استخراج فهرست رهگیرها
	 * توجه: برای انطباق کامل با قوانین مخزن رسمی وردپرس، رهگیری مستقیم ترنزینت‌های آپدیت حذف گردیده است.
	 *
	 * @return array
	 */
	public static function get_update_interceptors() {
		return array();
	}

	/**
	 * بررسی وجود تغییر در وضعیت مانیتورینگ
	 *
	 * @return bool
	 */
	public static function has_interceptors_changed() {
		return false;
	}

	/**
	 * تایید و به‌روزرسانی وضعیت مانیتورینگ
	 *
	 * @return bool
	 */
	public static function acknowledge_state_change() {
		return update_option( self::STATE_HASH_KEY, time(), 'no' );
	}
}
