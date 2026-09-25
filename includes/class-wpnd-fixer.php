<?php
/**
 * Handles HTTP request modifications, timeout increases, and User-Agent customization.
 *
 * @package network-doctor-zarinafzar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPND_Fixer {

	/**
	 * ثبت فیلترهای هسته HTTP وردپرس در صورت فعال بودن اصلاحات
	 */
	public function init() {
		if ( $this->is_fix_enabled() ) {
			add_filter( 'http_request_timeout', [ $this, 'modify_request_timeout' ], 999 );
			add_filter( 'http_request_args', [ $this, 'modify_request_args' ], 999, 2 );
		}
	}

	/**
	 * بررسی وضعیت فعال/غیرفعال بودن اصلاحات
	 *
	 * @return bool
	 */
	public function is_fix_enabled() {
		return (bool) get_option( 'wpnd_fix_enabled', false );
	}

	/**
	 * دریافت زمان تایم‌اوت مشخص‌شده بر حسب ثانیه
	 *
	 * @return int
	 */
	public function get_timeout_value() {
		return (int) get_option( 'wpnd_custom_timeout', 30 );
	}

	/**
	 * تغییر مهلت زمانی (Timeout) درخواست‌های cURL وردپرس
	 *
	 * @param int $timeout زمان پیش‌فرض هسته وردپرس
	 * @return int
	 */
	public function modify_request_timeout( $timeout ) {
		return $this->get_timeout_value();
	}

	/**
	 * تغییر User-Agent و پارامترهای درخواست برای دور زدن فایروال‌ها و بلاک‌های شبکه
	 *
	 * @param array  $parsed_args آرگومان‌های درخواست cURL
	 * @param string $url آدرس مقصد
	 * @return array
	 */
	public function modify_request_args( $parsed_args, $url ) {
		// لیست دامنه‌ها و سرویس‌های هدف جهت اعمال پچ شبکه
		$monitored_domains = [
			'elementor.com',
			'wordpress.org',
			'github.com',
			'raw.githubusercontent.com',
			'google.com',
			'googleapis.com',
			'google-analytics.com',
			'googletagmanager.com',
			'1.1.1.1',
		];

		$should_patch = false;

		foreach ( $monitored_domains as $domain ) {
			if ( strpos( $url, $domain ) !== false ) {
				$should_patch = true;
				break;
			}
		}

		if ( $should_patch ) {
			// ۱. شبیه‌سازی دقیق User-Agent یک مرورگر معتبر جهت عبور از سیستم‌های WAF
			$parsed_args['user-agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

			// ۲. تنظیم هدرهای استاندارد Accept جهت جلوگیری از مسدودی‌های مبنی بر درخواست‌های رباتیک
			if ( ! isset( $parsed_args['headers'] ) || ! is_array( $parsed_args['headers'] ) ) {
				$parsed_args['headers'] = [];
			}

			$parsed_args['headers']['Accept']          = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
			$parsed_args['headers']['Accept-Language'] = 'en-US,en;q=0.9';

			// ۳. اطمینان از مقدار تایم‌اوت در آرگومان‌های خروجی
			$parsed_args['timeout'] = $this->get_timeout_value();
		}

		return $parsed_args;
	}
}
