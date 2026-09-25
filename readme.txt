=== Network Doctor by ZarinAfzar ===
Contributors: aliramir
Tags: network, connectivity, diagnostic, curl, zarinafzar
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Network diagnostic tool to monitor server outbound connectivity, detect latency, and isolate external HTTP requests.

== Description ==

Network Doctor by ZarinAfzar is a lightweight, developer-grade diagnostic tool designed to pinpoint outbound network timeouts, test WordPress.org repository API reachability, monitor update blockers/interceptors, and clear stale transients.

Developed by Ali Ramezani (ZarinAfzar.com).

= Features =
* **Global Connectivity Probe**: Tests connection to standard reference endpoints and reports real-time latency.
* **Update Interceptor Monitor**: Identifies third-party plugins intercepting `pre_set_site_transient_update_plugins`.
* **Transient & Cache Cleaner**: Allows one-click flushing of update transients to resolve false "Update Failed" statuses.
* **Temporary Isolation Mode**: Gives administrators control to temporarily suppress outbound calls when external network timeouts freeze the dashboard.
* **Dashboard Widget**: Keep an eye on network health directly from the main WordPress Dashboard.

== Installation ==

1. Upload the `network-doctor-zarinafzar` folder to the `/wp-content/plugins/` directory, or install directly via the WordPress Plugins menu.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to **Tools > Network Doctor** to inspect your site connectivity and diagnostics.

== Frequently Asked Questions ==

= Does this plugin block all traffic permanently? =
No. The isolation mode is purely an administrative override intended for troubleshooting dashboard timeouts and is completely disabled by default.

= Is any personal data sent to external servers? =
No personal, user, or site-identifying data is transmitted. Connectivity checks only ping standard public endpoints to assess HTTP status and latency.

== Screenshots ==

1. Main diagnostics dashboard with network reachability metrics.
2. WordPress Dashboard network overview widget.

== Changelog ==

= 1.2.0 =
* Initial official release.
