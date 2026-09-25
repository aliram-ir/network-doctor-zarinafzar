<?php
/**
 * کلاس پایش و رهگیری هوک‌های آپدیت وردپرس
 *
 * @package Network_Doctor_ZarinAfzar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPND_Monitor {

	/**
	 * کلید نگهداری هش در دیتابیس
	 */
	const OPTION_KEY = 'wpnd_last_interceptors_hash';

	/**
	 * استخراج لیست رهگیرهای هوک با مشخصات پایدار
	 *
	 * @return array
	 */
	public static function get_update_interceptors() {
		global $wp_filter;

		$hook_name    = 'pre_set_site_transient_update_plugins';
		$interceptors = array();

		if ( ! isset( $wp_filter[ $hook_name ] ) ) {
			return $interceptors;
		}

		$hook      = $wp_filter[ $hook_name ];
		$callbacks = isset( $hook->callbacks ) ? $hook->callbacks : array();

		foreach ( $callbacks as $priority => $functions ) {
			foreach ( $functions as $key => $callback_data ) {
				$function = $callback_data['function'];
				$name     = '';
				$file     = '';

				if ( is_array( $function ) ) {
					$object = $function[0];
					$method = $function[1];

					if ( is_object( $object ) ) {
						$class_name = get_class( $object );
						$name       = $class_name . '->' . $method;
					} else {
						$class_name = $object;
						$name       = $class_name . '::' . $method;
					}

					try {
						$reflection = new ReflectionClass( $class_name );
						$file       = $reflection->getFileName();
					} catch ( Exception $e ) {
						$file = '';
					}
				} elseif ( is_string( $function ) ) {
					$name = $function;
					try {
						$reflection = new ReflectionFunction( $function );
						$file       = $reflection->getFileName();
					} catch ( Exception $e ) {
						$file = '';
					}
				} elseif ( is_a( $function, 'Closure' ) ) {
					$name = 'Closure (Anonymous Function)';
					try {
						$reflection = new ReflectionFunction( $function );
						$file       = $reflection->getFileName();
					} catch ( Exception $e ) {
						$file = '';
					}
				}

				if ( $file ) {
					$file = str_replace( WP_CONTENT_DIR, '', $file );
					$file = ltrim( $file, '/\\' );
				}

				// فقط ویژگی‌های پایدار را ثبت می‌کنیم و شناسه پویا شیء در حافظه حذف شده است
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
	 * بررسی تغییر در لیست هوک‌های آپدیت نسبت به هش ذخیره‌شده
	 *
	 * @return bool
	 */
	public static function has_interceptors_changed() {
		$current_interceptors = self::get_update_interceptors();

		if ( empty( $current_interceptors ) ) {
			return false;
		}

		$current_hash = md5( wp_json_encode( $current_interceptors ) );
		$saved_hash   = get_option( self::OPTION_KEY, false );

		if ( false === $saved_hash ) {
			update_option( self::OPTION_KEY, $current_hash, 'no' );
			return false;
		}

		return ( $current_hash !== $saved_hash );
	}

	/**
	 * تایید و ذخیره هش جاری هوک‌ها به عنوان وضعیت معتبر
	 *
	 * @return bool
	 */
	public static function acknowledge_state_change() {
		$current_interceptors = self::get_update_interceptors();
		$current_hash         = md5( wp_json_encode( $current_interceptors ) );
		return update_option( self::OPTION_KEY, $current_hash, 'no' );
	}
}
