<?php
/**
 * Dashboard template rendering Network Status, Blocking Controls, and Logs.
 *
 * @package network-doctor-zarinafzar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'WP Network Doctor: Global Connectivity & Diagnostics', 'wp-network-doctor' ); ?></h1>

	<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
		<!-- ستون چپ: کنترل‌ها و وضعیت فعلی -->
		<div>
			<div class="postbox" style="padding: 16px;">
				<h2><?php esc_html_e( 'Current Network Health & Isolation Switch', 'wp-network-doctor' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Global Internet Status', 'wp-network-doctor' ); ?></th>
						<td>
							<?php if ( ! empty( $status['connected'] ) ) : ?>
								<span style="display: inline-block; padding: 4px 10px; background: #e7f7ed; color: #00a32a; font-weight: bold; border-radius: 4px; border: 1px solid #b7ebc6;">
									✔ <?php esc_html_e( 'CONNECTED', 'wp-network-doctor' ); ?> (<?php echo esc_html( $status['latency'] ); ?> ms)
								</span>
							<?php else : ?>
								<span style="display: inline-block; padding: 4px 10px; background: #fcf0f1; color: #d63638; font-weight: bold; border-radius: 4px; border: 1px solid #f7c5c7;">
									✖ <?php esc_html_e( 'DISCONNECTED (National Network Only)', 'wp-network-doctor' ); ?>
								</span>
							<?php endif; ?>
							<a href="<?php echo esc_url( $check_url ); ?>" class="button button-small" style="margin-left: 10px;">
								<?php esc_html_e( 'Check Now', 'wp-network-doctor' ); ?>
							</a>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Last Checked Endpoint', 'wp-network-doctor' ); ?></th>
						<td><code><?php echo esc_html( ! empty( $status['endpoint'] ) ? $status['endpoint'] : 'None' ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'External Traffic Block (Isolation Mode)', 'wp-network-doctor' ); ?></th>
						<td>
							<?php if ( $is_isolated ) : ?>
								<p style="color: #d63638; font-weight: bold; margin-top: 0;">
									<?php esc_html_e( 'Active: External HTTP calls are blocked instantly to prevent server hangs.', 'wp-network-doctor' ); ?>
								</p>
								<a href="<?php echo esc_url( $toggle_url ); ?>" class="button button-secondary">
									<?php esc_html_e( 'Disable Isolation (Allow External Requests)', 'wp-network-doctor' ); ?>
								</a>
							<?php else : ?>
								<p style="color: #646970; margin-top: 0;">
									<?php esc_html_e( 'Inactive: WordPress makes normal requests to international servers.', 'wp-network-doctor' ); ?>
								</p>
								<a href="<?php echo esc_url( $toggle_url ); ?>" class="button button-primary">
									<?php esc_html_e( 'Block External Connections', 'wp-network-doctor' ); ?>
								</a>
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>

			<!-- جدول گزارش تاریخچه رخدادها -->
			<div class="postbox" style="padding: 16px;">
				<h2><?php esc_html_e( 'Connectivity State History Log', 'wp-network-doctor' ); ?></h2>
				<p class="description"><?php esc_html_e( 'A history of state transitions (Connection gained or lost) logged by automatic background probes.', 'wp-network-doctor' ); ?></p>
				<table class="widefat fixed striped" style="margin-top: 10px;">
					<thead>
						<tr>
							<th style="width: 25%;"><?php esc_html_e( 'Timestamp', 'wp-network-doctor' ); ?></th>
							<th style="width: 25%;"><?php esc_html_e( 'Status', 'wp-network-doctor' ); ?></th>
							<th style="width: 20%;"><?php esc_html_e( 'Latency', 'wp-network-doctor' ); ?></th>
							<th><?php esc_html_e( 'Event Description', 'wp-network-doctor' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $history ) ) : ?>
							<?php foreach ( $history as $entry ) : ?>
								<tr>
									<td><?php echo esc_html( $entry['timestamp'] ); ?></td>
									<td>
										<?php if ( $entry['connected'] ) : ?>
											<span style="color: #00a32a; font-weight: bold;">● <?php esc_html_e( 'Connected', 'wp-network-doctor' ); ?></span>
										<?php else : ?>
											<span style="color: #d63638; font-weight: bold;">● <?php esc_html_e( 'Disconnected', 'wp-network-doctor' ); ?></span>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html( $entry['latency'] ); ?> ms</td>
									<td>
										<?php
										if ( $entry['connected'] ) {
											esc_html_e( 'Global connectivity restored.', 'wp-network-doctor' );
										} else {
											esc_html_e( 'Global connectivity lost / timed out.', 'wp-network-doctor' );
										}
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="4"><?php esc_html_e( 'No state transition events logged yet.', 'wp-network-doctor' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<!-- ستون راست: اینترسپتورهای آپدیت -->
		<div>
			<div class="postbox" style="padding: 16px;">
				<h2><?php esc_html_e( 'Update Pipeline Interceptors', 'wp-network-doctor' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Plugins hooked to repository transients:', 'wp-network-doctor' ); ?></p>
				<?php
				$interceptors = WPND_Monitor::get_update_interceptors();
				if ( ! empty( $interceptors ) ) :
				?>
					<ul style="margin: 10px 0; padding: 0; list-style: none;">
						<?php foreach ( $interceptors as $item ) : ?>
							<li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
								<strong style="display: block; font-family: monospace; font-size: 11px;"><?php echo esc_html( $item['name'] ); ?></strong>
								<span style="color: #888; font-size: 11px;"><?php echo esc_html( $item['file'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p style="color: #46b450; font-weight: bold;"><?php esc_html_e( '✔ No external interceptors detected.', 'wp-network-doctor' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
