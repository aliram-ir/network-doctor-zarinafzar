<?php
/**
 * کلاس پایش و مانیتورینگ هوک‌های آپدیت وردپرس
 *
 * @package WP_Network_Doctor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPND_Monitor {

	/**
	 * کلید آپشن برای ذخیره هش وضعیت هوک‌ها
	 */
	const OPTION_KEY = 'wpnd_last_interceptors_hash';

	/**
	 * دریافت لیست رهگیرها و توابع متصل به هوک آپدیت
	 *
	 * @return array
	 */
	public static function get_update_interceptors() {
		global $wp_filter;

		$hook_name = 'pre_set_site_transient_update_plugins';
		$interceptors = array();

		if ( ! isset( $wp_filter[ $hook_name ] ) ) {
			return $interceptors;
		}

		$hook = $wp_filter[ $hook_name ];
		$callbacks = isset( $hook->callbacks ) ? $hook->callbacks : array();

		foreach ( $callbacks as $priority => $functions ) {
			foreach ( $functions as $key => $callback_data ) {
				$function = $callback_data['function'];
				$name = '';
				$file = '';

				if ( is_array( $function ) ) {
					$object = $function[0];
					$method = $function[1];

					if ( is_object( $object ) ) {
						$class_name = get_class( $object );
						$name = $class_name . '->' . $method;
					} else {
						$class_name = $object;
						$name = $class_name . '::' . $method;
					}

					try {
						$reflection = new ReflectionClass( $class_name );
						$file = $reflection->getFileName();
					} catch ( Exception $e ) {
						$file = '';
					}
				} elseif ( is_string( $function ) ) {
					$name = $function;
					try {
						$reflection = new ReflectionFunction( $function );
						$file = $reflection->getFileName();
					} catch ( Exception $e ) {
						$file = '';
					}
				} elseif ( is_a( $function, 'Closure' ) ) {
					$name = 'Closure (Anonymous Function)';
					try {
						$reflection = new ReflectionFunction( $function );
						$file = $reflection->getFileName();
					} catch ( Exception $e ) {
						$file = '';
					}
				}

				if ( $file ) {
					$file = str_replace( WP_CONTENT_DIR, '', $file );
					$file = ltrim( $file, '/\\' );
				}

				// فقط از مشخصات پایدار برای شناسایی استفاده می‌کنیم نه شناسه حافظه شیء
				$interceptors[] = array(
					'name'     => $name,
					'file'     => $file,
					'priority' => $priority,
				);
			}
		}

		return $interceptors;
	}

	/**
	 * بررسی اینکه آیا لیست هوک‌ها نسبت به آخرین وضعیت تایید شده تغییر کرده است یا خیر
	 *
	 * @return bool
	 */
	public static function has_interceptors_changed() {
		$current_interceptors = self::get_update_interceptors();
		
		// اگر هیچ هوکی غیر از حالت پیش‌فرض وردپرس نبود نیازی به هشدار نیست
		if ( empty( $current_interceptors ) ) {
			return false;
		}

		$current_hash = md5( wp_json_encode( $current_interceptors ) );
		$saved_hash   = get_option( self::OPTION_KEY, false );

		// بار اول هش جاری را ذخیره می‌کنیم تا بلافاصله کاربر با هشدار روبرو نشود
		if ( false === $saved_hash ) {
			update_option( self::OPTION_KEY, $current_hash, 'no' );
			return false;
		}

		return ( $current_hash !== $saved_hash );
	}

	/**
	 * تایید و همگام‌سازی وضعیت جاری هوک‌ها
	 *
	 * @return bool
	 */
	public static function acknowledge_state_change() {
		$current_interceptors = self::get_update_interceptors();
		$current_hash         = md5( wp_json_encode( $current_interceptors ) );
		return update_option( self::OPTION_KEY, $current_hash, 'no' );
	}
}
