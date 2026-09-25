<?php
/**
 * کلاس مدیریت بخش پیشخوان و اعلان‌های افزونه
 *
 * @package WP_Network_Doctor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPND_Admin {

	/**
	 * نمونه کلاس فیکسر
	 *
	 * @var WPND_Fixer
	 */
	protected $fixer;

	/**
	 * سازنده کلاس
	 *
	 * @param WPND_Fixer|null $fixer
	 */
	public function __construct( $fixer = null ) {
		$this->fixer = $fixer;
		$this->init_hooks();
	}

	/**
	 * ثبت هوک‌های بخش مدیریت
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_notices', array( $this, 'render_interceptor_notice' ) );
		add_action( 'admin_post_wpnd_ack_interceptors', array( $this, 'handle_ack_interceptors' ) );
		add_action( 'admin_post_wpnd_toggle_isolation', array( $this, 'handle_toggle_isolation' ) );
		add_action( 'admin_post_wpnd_manual_check', array( $this, 'handle_manual_check' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * بارگذاری استایل‌ها
	 */
	public function enqueue_assets( $hook ) {
		if ( 'tools_page_wpnd-dashboard' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wpnd-admin-css',
			WPND_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			WPND_VERSION
		);
	}

	/**
	 * ثبت منوی افزونه در ابزارها
	 */
	public function register_menu() {
		add_management_page(
			__( 'Network Doctor', 'network-doctor-zarinafzar' ),
			__( 'Network Doctor', 'network-doctor-zarinafzar' ),
			'manage_options',
			'wpnd-dashboard',
			array( $this, 'render_dashboard' )
		);
	}

	/**
	 * نمایش اعلان تغییر در هوک‌های آپدیت
	 */
	public function render_interceptor_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! WPND_Monitor::has_interceptors_changed() ) {
			return;
		}

		$ack_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=wpnd_ack_interceptors' ),
			'wpnd_ack_action',
			'wpnd_nonce'
		);

		$dashboard_url = admin_url( 'tools.php?page=wpnd-dashboard' );
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Network Doctor:', 'network-doctor-zarinafzar' ); ?></strong>
				<?php esc_html_e( 'The structure of hooks attached to the WordPress update process has changed! A commercial plugin or theme with a custom update server might have been added, removed, or updated.', 'network-doctor-zarinafzar' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $dashboard_url ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Inspect Interceptors', 'network-doctor-zarinafzar' ); ?>
				</a>
				<a href="<?php echo esc_url( $ack_url ); ?>" class="button button-primary" style="margin-right: 5px;">
					<?php esc_html_e( 'Acknowledge & Dismiss', 'network-doctor-zarinafzar' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * مدیریت درخواست تایید و بستن هشدار
	 */
	public function handle_ack_interceptors() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'network-doctor-zarinafzar' ) );
		}

		check_admin_referer( 'wpnd_ack_action', 'wpnd_nonce' );

		WPND_Monitor::acknowledge_state_change();

		$referer = wp_get_referer();
		$redirect_url = $referer ? $referer : admin_url( 'tools.php?page=wpnd-dashboard' );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * نمایش صفحه اصلی داشبورد افزونه
	 */
	public function render_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$network_results = WPND_Network::test_all_endpoints();
		$interceptors    = WPND_Monitor::get_update_interceptors();
		$isolation_mode  = get_option( 'wpnd_isolation_mode', '0' );

		include WPND_PLUGIN_DIR . 'templates/dashboard.php';
	}

	/**
	 * مدیریت سوییچ ایزولاسیون هوک‌ها
	 */
	public function handle_toggle_isolation() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'network-doctor-zarinafzar' ) );
		}

		check_admin_referer( 'wpnd_isolation_action', 'wpnd_nonce' );

		$current = get_option( 'wpnd_isolation_mode', '0' );
		update_option( 'wpnd_isolation_mode', ( '1' === $current ? '0' : '1' ) );

		wp_safe_redirect( admin_url( 'tools.php?page=wpnd-dashboard' ) );
		exit;
	}

	/**
	 * بررسی دستی وضعیت شبکه
	 */
	public function handle_manual_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'network-doctor-zarinafzar' ) );
		}

		check_admin_referer( 'wpnd_check_action', 'wpnd_nonce' );

		wp_safe_redirect( admin_url( 'tools.php?page=wpnd-dashboard' ) );
		exit;
	}
}
