<?php
/**
 * بخش مدیریت و داشبورد افزونه
 *
 * @package           Network_Doctor_By_ZarinAfzar
 * @subpackage        Network_Doctor_By_ZarinAfzar/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // جلوگیری از دسترسی مستقیم به فایل
}

/**
 * کلاس مدیریت پنل ادمین افزونه
 */
class WPND_Admin {

	/**
	 * سازنده کلاس ادمین
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
		add_action( 'admin_notices', array( $this, 'render_interceptor_notice' ) );
		add_action( 'admin_post_wpnd_ack_interceptors', array( $this, 'handle_acknowledge_interceptors' ) );
		add_action( 'admin_post_wpnd_manual_check', array( $this, 'handle_manual_check' ) );
		add_action( 'admin_post_wpnd_toggle_isolation', array( $this, 'handle_toggle_isolation' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * بارگذاری فایل‌های استایل و اسکریپت بخش ادمین
	 *
	 * @param string $hook_suffix شناسه صفحه فعلی ادمین.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( 'toplevel_page_network-doctor' !== $hook_suffix ) {
			return;
		}

		$css_file = WPND_PLUGIN_DIR . 'admin/css/admin.css';
		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				'wpnd-admin-css',
				WPND_PLUGIN_URL . 'admin/css/admin.css',
				array(),
				WPND_VERSION
			);
		}
	}

	/**
	 * ثبت منوی افزونه در پیشخوان وردپرس
	 */
	public function register_admin_menu() {
		add_menu_page(
			__( 'Network Doctor', 'network-doctor-zarinafzar' ),
			__( 'Network Doctor', 'network-doctor-zarinafzar' ),
			'manage_options',
			'network-doctor',
			array( $this, 'render_admin_page' ),
			'dashicons-networking',
			65
		);
	}

	/**
	 * ثبت ابزارک اختصاصی در داشبورد اصلی وردپرس
	 */
	public function register_dashboard_widget() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'wpnd_dashboard_widget',
			__( 'وضعیت اتصال شبکه (Network Doctor)', 'network-doctor-zarinafzar' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * رندر کردن ابزارک پیشخوان وردپرس
	 */
	public function render_dashboard_widget() {
		$status       = WPND_Network::get_current_status();
		$is_isolated  = WPND_Network::is_isolation_active();
		$interceptors = class_exists( 'WPND_Monitor' ) ? WPND_Monitor::get_update_interceptors() : array();

		$is_connected = ! empty( $status['connected'] );
		$status_text  = $is_connected ? __( 'متصل', 'network-doctor-zarinafzar' ) : __( 'قطع / خطا', 'network-doctor-zarinafzar' );
		$status_color = $is_connected ? '#46b450' : '#dc3232';
		$latency      = isset( $status['latency'] ) ? round( $status['latency'], 2 ) . ' ms' : '-';

		?>
		<div class="wpnd-dashboard-widget-content">
			<p>
				<strong><?php esc_html_e( 'وضعیت سرور:', 'network-doctor-zarinafzar' ); ?></strong>
				<span style="color: <?php echo esc_attr( $status_color ); ?>; font-weight: bold;">
					<?php echo esc_html( $status_text ); ?>
				</span>
				(<?php echo esc_html( $latency ); ?>)
			</p>

			<?php if ( $is_isolated ) : ?>
				<p style="color: #dba617; font-weight: bold;">
					<?php esc_html_e( 'حالت ایزوله شبکه فعال است (درخواست‌های خارجی مسدود هستند).', 'network-doctor-zarinafzar' ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $interceptors ) ) : ?>
				<p>
					<strong><?php esc_html_e( 'رهگیرهای بررسی به‌روزرسانی:', 'network-doctor-zarinafzar' ); ?></strong>
					<?php echo esc_html( count( $interceptors ) ); ?> <?php esc_html_e( 'مورد شناسایی شد.', 'network-doctor-zarinafzar' ); ?>
				</p>
			<?php endif; ?>

			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=network-doctor' ) ); ?>" class="button button-secondary">
					<?php esc_html_e( 'مشاهده داشبورد کامل', 'network-doctor-zarinafzar' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * نمایش اعلان ادمین در صورت تغییر رهگیرهای به‌روزرسانی
	 */
	public function render_interceptor_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! class_exists( 'WPND_Monitor' ) || ! WPND_Monitor::has_interceptors_changed() ) {
			return;
		}

		$ack_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=wpnd_ack_interceptors' ),
			'wpnd_ack_interceptors_action',
			'_wpnd_nonce'
		);

		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Network Doctor:', 'network-doctor-zarinafzar' ); ?></strong>
				<?php esc_html_e( 'تغییراتی در توابع مسدودکننده یا تغییردهندهٔ فرایند بررسی به‌روزرسانی وردپرس شناسایی شد.', 'network-doctor-zarinafzar' ); ?>
				<a href="<?php echo esc_url( $ack_url ); ?>" class="button button-small" style="margin-right: 10px;">
					<?php esc_html_e( 'تأیید و نادیده‌گرفتن', 'network-doctor-zarinafzar' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * تایید و به‌روزرسانی هش رهگیرها توسط مدیر سایت
	 */
	public function handle_acknowledge_interceptors() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'شما اجازه دسترسی به این بخش را ندارید.', 'network-doctor-zarinafzar' ) );
		}

		check_admin_referer( 'wpnd_ack_interceptors_action', '_wpnd_nonce' );

		if ( class_exists( 'WPND_Monitor' ) ) {
			WPND_Monitor::acknowledge_state_change();
		}

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
		exit;
	}

	/**
	 * اجرای تست دستی شبکه توسط کاربر
	 */
	public function handle_manual_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'شما اجازه دسترسی به این بخش را ندارید.', 'network-doctor-zarinafzar' ) );
		}

		check_admin_referer( 'wpnd_manual_check_action', '_wpnd_nonce' );

		if ( class_exists( 'WPND_Network' ) ) {
			WPND_Network::check_global_connectivity();
		}

		wp_safe_redirect( admin_url( 'admin.php?page=network-doctor&checked=1' ) );
		exit;
	}

	/**
	 * فعال/غیرفعال‌سازی حالت ایزوله شبکه
	 */
	public function handle_toggle_isolation() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'شما اجازه دسترسی به این بخش را ندارید.', 'network-doctor-zarinafzar' ) );
		}

		check_admin_referer( 'wpnd_toggle_isolation_action', '_wpnd_nonce' );

		if ( class_exists( 'WPND_Network' ) ) {
			$current = WPND_Network::is_isolation_active();
			WPND_Network::set_isolation_mode( ! $current );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=network-doctor&isolation_toggled=1' ) );
		exit;
	}

	/**
	 * رندر کردن صفحه اصلی تنظیمات و داشبورد افزونه
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'شما اجازه دسترسی به این بخش را ندارید.', 'network-doctor-zarinafzar' ) );
		}

		$status      = WPND_Network::get_current_status();
		$is_isolated = WPND_Network::is_isolation_active();
		$history     = WPND_Network::get_history();

		$check_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=wpnd_manual_check' ),
			'wpnd_manual_check_action',
			'_wpnd_nonce'
		);

		$toggle_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=wpnd_toggle_isolation' ),
			'wpnd_toggle_isolation_action',
			'_wpnd_nonce'
		);

		$template_path = WPND_PLUGIN_DIR . 'admin/templates/dashboard-page.php';

		if ( file_exists( $template_path ) ) {
			include $template_path;
		} else {
			echo '<div class="wrap"><h1>' . esc_html__( 'Network Doctor', 'network-doctor-zarinafzar' ) . '</h1>';
			echo '<p>' . esc_html__( 'فایل قالب داشبورد یافت نشد.', 'network-doctor-zarinafzar' ) . '</p></div>';
		}
	}
}
