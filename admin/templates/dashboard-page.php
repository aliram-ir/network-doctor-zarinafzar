<?php
/**
 * قالب داشبورد جهت نمایش وضعیت شبکه، کنترل‌های ایزوله‌سازی و تاریخچه رویدادها
 *
 * @package Network_Doctor_By_ZarinAfzar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Network Doctor: Global Connectivity & Diagnostics', 'network-doctor-zarinafzar' ); ?></h1>

	<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
		<!-- ستون اول: کنترل‌ها و وضعیت شبکه -->
		<div>
			<div class="postbox" style="padding: 16px;">
				<h2><?php esc_html_e( 'Current Network Health & Isolation Switch', 'network-doctor-zarinafzar' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Global Internet Status', 'network-doctor-zarinafzar' ); ?></th>
						<td>
							<?php if ( ! empty( $status['connected'] ) ) : ?>
								<span style="display: inline-block; padding: 4px 10px; background: #e7f7ed; color: #00a32a; font-weight: bold; border-radius: 4px; border: 1px solid #b7ebc6;">
									✔ <?php esc_html_e( 'CONNECTED', 'network-doctor-zarinafzar' ); ?> (<?php echo esc_html( $status['latency'] ); ?> ms)
								</span>
							<?php else : ?>
								<span style="display: inline-block; padding: 4px 10px; background: #fcf0f1; color: #d63638; font-weight: bold; border-radius: 4px; border: 1px solid #f7c5c7;">
									✖ <?php esc_html_e( 'DISCONNECTED (National Network Only)', 'network-doctor-zarinafzar' ); ?>
								</span>
							<?php endif; ?>
							<a href="<?php echo esc_url( $check_url ); ?>" class="button button-small" style="margin-left: 10px;">
								<?php esc_html_e( 'Check Now', 'network-doctor-zarinafzar' ); ?>
							</a>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Last Checked Endpoint', 'network-doctor-zarinafzar' ); ?></th>
						<td><code><?php echo esc_html( ! empty( $status['endpoint'] ) ? $status['endpoint'] : 'None' ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'External Traffic Block (Isolation Mode)', 'network-doctor-zarinafzar' ); ?></th>
						<td>
							<?php if ( $is_isolated ) : ?>
								<p style="color: #d63638; font-weight: bold; margin-top: 0;">
									<?php esc_html_e( 'Active: External HTTP calls are blocked instantly to prevent server hangs.', 'network-doctor-zarinafzar' ); ?>
								</p>
								<a href="<?php echo esc_url( $toggle_url ); ?>" class="button button-secondary">
									<?php esc_html_e( 'Disable Isolation (Allow External Requests)', 'network-doctor-zarinafzar' ); ?>
								</a>
							<?php else : ?>
								<p style="color: #646970; margin-top: 0;">
									<?php esc_html_e( 'Inactive: WordPress makes normal requests to international servers.', 'network-doctor-zarinafzar' ); ?>
								</p>
								<a href="<?php echo esc_url( $toggle_url ); ?>" class="button button-primary">
									<?php esc_html_e( 'Block External Connections', 'network-doctor-zarinafzar' ); ?>
								</a>
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>

			<!-- جدول گزارش تاریخچه رخدادها -->
			<div class="postbox" style="padding: 16px;">
				<h2><?php esc_html_e( 'Connectivity State History Log', 'network-doctor-zarinafzar' ); ?></h2>
				<p class="description"><?php esc_html_e( 'A history of state transitions (Connection gained or lost) logged by automatic background probes.', 'network-doctor-zarinafzar' ); ?></p>
				<table class="widefat fixed striped" style="margin-top: 10px;">
					<thead>
						<tr>
							<th style="width: 25%;"><?php esc_html_e( 'Timestamp', 'network-doctor-zarinafzar' ); ?></th>
							<th style="width: 25%;"><?php esc_html_e( 'Status', 'network-doctor-zarinafzar' ); ?></th>
							<th style="width: 20%;"><?php esc_html_e( 'Latency', 'network-doctor-zarinafzar' ); ?></th>
							<th><?php esc_html_e( 'Event Description', 'network-doctor-zarinafzar' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $history ) ) : ?>
							<?php foreach ( $history as $entry ) : ?>
								<tr>
									<td><?php echo esc_html( $entry['timestamp'] ); ?></td>
									<td>
										<?php if ( ! empty( $entry['connected'] ) ) : ?>
											<span style="color: #00a32a; font-weight: bold;">● <?php esc_html_e( 'Connected', 'network-doctor-zarinafzar' ); ?></span>
										<?php else : ?>
											<span style="color: #d63638; font-weight: bold;">● <?php esc_html_e( 'Disconnected', 'network-doctor-zarinafzar' ); ?></span>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html( isset( $entry['latency'] ) ? $entry['latency'] : 0 ); ?> ms</td>
									<td>
										<?php
										if ( ! empty( $entry['connected'] ) ) {
											esc_html_e( 'Global connectivity restored.', 'network-doctor-zarinafzar' );
										} else {
											esc_html_e( 'Global connectivity lost / timed out.', 'network-doctor-zarinafzar' );
										}
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="4"><?php esc_html_e( 'No state transition events logged yet.', 'network-doctor-zarinafzar' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<!-- ستون دوم: خلاصه وضعیت سیستم و راهنما -->
		<div>
			<div class="postbox" style="padding: 16px;">
				<h2><?php esc_html_e( 'Diagnostic Overview', 'network-doctor-zarinafzar' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Network monitoring parameters and operational status:', 'network-doctor-zarinafzar' ); ?></p>
				<ul style="margin: 15px 0 0; padding: 0; list-style: none;">
					<li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
						<strong><?php esc_html_e( 'cURL Availability:', 'network-doctor-zarinafzar' ); ?></strong>
						<span style="float: right; color: <?php echo function_exists( 'curl_version' ) ? '#00a32a' : '#d63638'; ?>;">
							<?php echo function_exists( 'curl_version' ) ? esc_html__( 'Enabled', 'network-doctor-zarinafzar' ) : esc_html__( 'Disabled', 'network-doctor-zarinafzar' ); ?>
						</span>
					</li>
					<li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
						<strong><?php esc_html_e( 'Default Probe Timeout:', 'network-doctor-zarinafzar' ); ?></strong>
						<span style="float: right; color: #50575e;">3.0s</span>
					</li>
					<li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
						<strong><?php esc_html_e( 'Active Shield Filter:', 'network-doctor-zarinafzar' ); ?></strong>
						<span style="float: right; color: #50575e;">pre_http_request</span>
					</li>
					<li style="padding: 8px 0;">
						<strong><?php esc_html_e( 'Text Domain:', 'network-doctor-zarinafzar' ); ?></strong>
						<span style="float: right; color: #50575e;">network-doctor-zarinafzar</span>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
