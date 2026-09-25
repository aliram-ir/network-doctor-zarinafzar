<?php
/**
 * Global Connectivity Checker & Outgoing Network Isolator (Circuit Breaker).
 *
 * @package network-doctor-zarinafzar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPND_Firewall
 */
class WPND_Firewall {

	const OPTION_BLOCK_STATUS = 'wpnd_block_foreign_traffic';
	const OPTION_HEALTH_LOGS  = 'wpnd_connectivity_history';
	const CRON_HOOK           = 'wpnd_check_global_connectivity_event';

	/**
	 * ثبت فیلترها و کران‌جاب‌ها.
	 */
	public static function init() {
		// مسدودسازی درخواست‌های خارجی در صورت فعال بودن وضعیت
		add_filter( 'pre_http_request', array( __CLASS__, 'filter_outgoing_requests' ), 10, 3 );

		// هوک بررسی دوره‌ای اتصال
		add_action( self::CRON_HOOK, array( __CLASS__, 'run_connectivity_check' ) );

		// فعال‌سازی کران‌جاب دوره‌ای در صورت عدم وجود
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'hourly', self::CRON_HOOK );
		}
	}

	/**
	 * آیا مسدودسازی اینترنت خارجی در حال حاضر فعال است؟
	 *
	 * @return bool
	 */
	public static function is_block_enabled() {
		return 'yes' === get_option( self::OPTION_BLOCK_STATUS, 'no' );
	}

	/**
	 * تغییر وضعیت مسدودسازی.
	 *
	 * @param bool $status وضعیت جدید.
	 * @return void
	 */
	public static function set_block_status( $status ) {
		update_option( self::OPTION_BLOCK_STATUS, $status ? 'yes' : 'no', false );
	}

	/**
	 * تست زنده اتصال به اینترنت جهانی با یک منبع فوق‌العاده سبک و مطمئن.
	 *
	 * @return bool
	 */
	public static function test_global_connectivity() {
		// استفاده از چند نقطه تست سبک جهانی با تایم‌اوت خیلی کوتاه
		$test_targets = array(
			'https://1.1.1.1',
			'https://www.google.com/generate_204',
		);

		foreach ( $test_targets as $url ) {
			$response = wp_remote_get(
				$url,
				array(
					'timeout'    => 3,
					'sslverify'  => false,
					'user-agent' => 'WP-Network-Doctor/1.0',
				)
			);

			if ( ! is_wp_error( $response ) ) {
				$code = wp_remote_retrieve_response_code( $response );
				if ( $code >= 200 && $code < 400 ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * اجرای تست و لاگ‌کردن نتیجه همراه با زمان شمسی/میلادی.
	 *
	 * @return bool نتیجه وضعیت جاری اتصال
	 */
	public static function run_connectivity_check() {
		$is_connected = self::test_global_connectivity();
		$logs         = get_option( self::OPTION_HEALTH_LOGS, array() );

		if ( ! is_array( $logs ) ) {
			$logs = array();
		}

		// افزودن گزارش جدید به ابتدای آرایه
		array_unshift(
			$logs,
			array(
				'timestamp' => current_time( 'timestamp' ),
				'status'    => $is_connected,
			)
		);

		// نگه داشتن فقط ۱۰ لاگ آخر جهت بهینه‌سازی دیتابیس
		$logs = array_slice( $logs, 0, 10 );

		update_option( self::OPTION_HEALTH_LOGS, $logs, false );

		return $is_connected;
	}

	/**
	 * دریافت آخرین وضعیت ثبت شده اینترنت.
	 *
	 * @return array
	 */
	public static function get_latest_status() {
		$logs = get_option( self::OPTION_HEALTH_LOGS, array() );

		if ( empty( $logs ) ) {
			// اگر تا بحال تستی انجام نشده، یکبار فوری اجرا کن
			self::run_connectivity_check();
			$logs = get_option( self::OPTION_HEALTH_LOGS, array() );
		}

		return ! empty( $logs ) ? $logs[0] : array( 'timestamp' => time(), 'status' => false );
	}

	/**
	 * دریافت تاریخچه بررسی‌ها.
	 *
	 * @return array
	 */
	public static function get_history_logs() {
		return get_option( self::OPTION_HEALTH_LOGS, array() );
	}

	/**
	 * فیلتر کردن درخواست‌های HTTP وردپرس و مسدودسازی سریع مقاصد خارجی در صورت فعال بودن مسدودکننده.
	 *
	 * @param false|array|WP_Error $preempt آیا پاسخ زودتر شبیه‌سازی شود.
	 * @param array                $parsed_args آرگومان‌های درخواست.
	 * @param string               $url آدرس مقصد.
	 * @return false|WP_Error
	 */
	public static function filter_outgoing_requests( $preempt, $parsed_args, $url ) {
		// اگر مسدودسازی غیرفعال بود یا پاسخ قبلاً مقداردهی شده بود
		if ( ! self::is_block_enabled() || false !== $preempt ) {
			return $preempt;
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( empty( $host ) ) {
			return $preempt;
		}

		// استثناها: درخواست‌های لوکال، هاست فعلی، دامنه‌های دات آی‌آر (.ir) نباید بلاک شوند
		if (
			'localhost' === $host ||
			'127.0.0.1' === $host ||
			str_ends_with( strtolower( $host ), '.ir' ) ||
			str_contains( $host, wp_parse_url( home_url(), PHP_URL_HOST ) )
		) {
			return $preempt;
		}

		// مسدودسازی فوری و جلوگیری از هدر رفتن زمان درخواست (Fast Fail)
		return new WP_Error(
			'wpnd_blocked_foreign_request',
			__( 'External request blocked by WP Network Doctor (Global Internet Isolator is Active).', 'wp-network-doctor' )
		);
	}
}
