<?php
/**
 * Global Network connectivity status, health-check cron, and history logger.
 *
 * @package network-doctor-zarinafzar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPND_Network
 */
class WPND_Network {

	const OPTION_STATUS    = 'wpnd_global_connectivity_status';
	const OPTION_HISTORY   = 'wpnd_connectivity_history';
	const OPTION_ISOLATION = 'wpnd_isolation_mode_enabled';
	const CRON_HOOK        = 'wpnd_check_global_network_cron';

	/**
	 * سرورهای مرجع برای پایش آنلاین بودن شبکه جهانی
	 */
	private static $reference_endpoints = array(
		'https://api.wordpress.org/stats/wordpress/1.0/',
		'https://1.1.1.1/cdn-cgi/trace',
		'https://www.google.com/generate_204',
	);

	/**
	 * مقداردهی اولیه هوک‌ها
	 */
	public static function init() {
		// تعریف و اتصال به کرون‌جاب وردپرس
		add_action( self::CRON_HOOK, array( __CLASS__, 'perform_scheduled_check' ) );

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'wpnd_three_minutes', self::CRON_HOOK );
		}

		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_intervals' ) );

		// اگر ایزولاسیون فعال است، درخواست‌های خارجی را بلاک کن
		if ( self::is_isolation_active() ) {
			add_filter( 'pre_http_request', array( __CLASS__, 'block_external_http_requests' ), 1, 3 );
		}
	}

	/**
	 * بازه زمانی ۳ دقیقه‌ای
	 */
	public static function add_cron_intervals( $schedules ) {
		$schedules['wpnd_three_minutes'] = array(
			'interval' => 180,
			'display'  => esc_html__( 'Every 3 Minutes', 'wp-network-doctor' ),
		);
		return $schedules;
	}

	/**
	 * تست زنده اتصال جهانی
	 */
	public static function check_global_connectivity() {
		$is_connected = false;
		$latency_ms   = 0;
		$reachable_ep = '';

		foreach ( self::$reference_endpoints as $endpoint ) {
			$start_time = microtime( true );
			$response   = wp_remote_get(
				$endpoint,
				array(
					'timeout'     => 3,
					'redirection' => 2,
					'sslverify'   => false,
					'user-agent'  => 'WP-Network-Doctor-Probe/' . WPND_VERSION,
				)
			);
			$end_time = microtime( true );

			if ( ! is_wp_error( $response ) ) {
				$status_code = wp_remote_retrieve_response_code( $response );
				if ( $status_code >= 200 && $status_code < 400 ) {
					$is_connected = true;
					$latency_ms   = round( ( $end_time - $start_time ) * 1000 );
					$reachable_ep = $endpoint;
					break;
				}
			}
		}

		$current_time = current_time( 'mysql' );
		$status_data  = array(
			'connected'    => $is_connected,
			'latency'      => $latency_ms,
			'endpoint'     => $reachable_ep,
			'last_checked' => $current_time,
		);

		$previous_status = get_option( self::OPTION_STATUS, null );
		if ( null === $previous_status || $previous_status['connected'] !== $is_connected ) {
			self::log_status_change( $is_connected, $latency_ms, $current_time );
		}

		update_option( self::OPTION_STATUS, $status_data, false );
		return $status_data;
	}

	/**
	 * اجرای چک خودکار در پس‌زمینه
	 */
	public static function perform_scheduled_check() {
		self::check_global_connectivity();
	}

	/**
	 * ثبت لاگ در تاریخچه رویدادها
	 */
	private static function log_status_change( $connected, $latency, $timestamp ) {
		$history = get_option( self::OPTION_HISTORY, array() );
		if ( ! is_array( $history ) ) {
			$history = array();
		}

		array_unshift(
			$history,
			array(
				'timestamp' => $timestamp,
				'connected' => (bool) $connected,
				'latency'   => $latency,
			)
		);

		if ( count( $history ) > 50 ) {
			$history = array_slice( $history, 0, 50 );
		}

		update_option( self::OPTION_HISTORY, $history, false );
	}

	/**
	 * دریافت آخرین وضعیت
	 */
	public static function get_current_status() {
		$status = get_option( self::OPTION_STATUS, false );
		if ( ! $status ) {
			return self::check_global_connectivity();
		}
		return $status;
	}

	/**
	 * دریافت تاریخچه
	 */
	public static function get_history() {
		$history = get_option( self::OPTION_HISTORY, array() );
		return is_array( $history ) ? $history : array();
	}

	/**
	 * وضعیت ایزولاسیون
	 */
	public static function is_isolation_active() {
		return (bool) get_option( self::OPTION_ISOLATION, false );
	}

	/**
	 * سوییچ حالت ایزولاسیون
	 */
	public static function set_isolation_mode( $enable ) {
		update_option( self::OPTION_ISOLATION, (bool) $enable, false );
	}

	/**
	 * فایروال مسدودساز اتصالات کند یا قطع خارجی
	 */
	public static function block_external_http_requests( $preempt, $parsed_args, $url ) {
		$host      = wp_parse_url( $url, PHP_URL_HOST );
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );

		if ( $host === $site_host || 'localhost' === $host || '127.0.0.1' === $host ) {
			return $preempt;
		}

		return new WP_Error(
			'wpnd_external_network_blocked',
			esc_html__( 'WP Network Doctor: External network access is temporarily isolated to prevent timeout delays.', 'wp-network-doctor' )
		);
	}
}
