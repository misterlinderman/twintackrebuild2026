<?php
/**
 * Plugin Name: TwinTack Local Sandbox
 * Description: Blocks outbound integrations on Local (WP_ENVIRONMENT_TYPE=local). Safe to leave installed — no effect on production.
 * Version: 1.0.0
 * Author: TwinTack
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Only run in Local / non-production environments.
 */
function twintack_is_local_sandbox(): bool {
	if ( defined( 'TWINTACK_DISABLE_SANDBOX' ) && TWINTACK_DISABLE_SANDBOX ) {
		return false;
	}

	return wp_get_environment_type() === 'local';
}

/**
 * Block outbound HTTP to production integration hosts.
 */
function twintack_sandbox_block_outbound_http( $pre, $args, $url ) {
	if ( ! twintack_is_local_sandbox() || ! is_string( $url ) ) {
		return $pre;
	}

	$blocked_hosts = array(
		'hook.us1.make.com',
		'hook.us2.make.com',
		'hook.eu1.make.com',
		'api.stripe.com',
		'connect.stripe.com',
		'a.klaviyo.com',
		'api.klaviyo.com',
		'graph.facebook.com',
		'quickbooks.api.intuit.com',
		'sell.amazon.com',
		'mws.amazonservices.com',
	);

	$host = wp_parse_url( $url, PHP_URL_HOST );

	foreach ( $blocked_hosts as $blocked ) {
		if ( $host === $blocked || ( is_string( $host ) && str_ends_with( $host, '.' . $blocked ) ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[TwinTack Sandbox] Blocked outbound request: ' . $url );
			}
			return new WP_Error( 'twintack_local_sandbox', 'Outbound request blocked in local environment.' );
		}
	}

	return $pre;
}
add_filter( 'pre_http_request', 'twintack_sandbox_block_outbound_http', 10, 3 );

/**
 * Prevent WooCommerce transactional emails from reaching real customers.
 */
function twintack_sandbox_disable_wc_emails( $enabled, $email ) {
	if ( twintack_is_local_sandbox() ) {
		return false;
	}
	return $enabled;
}
add_filter( 'woocommerce_email_enabled_customer_processing_order', 'twintack_sandbox_disable_wc_emails', 10, 2 );
add_filter( 'woocommerce_email_enabled_customer_completed_order', 'twintack_sandbox_disable_wc_emails', 10, 2 );
add_filter( 'woocommerce_email_enabled_customer_invoice', 'twintack_sandbox_disable_wc_emails', 10, 2 );
add_filter( 'woocommerce_email_enabled_customer_note', 'twintack_sandbox_disable_wc_emails', 10, 2 );

/**
 * Route wp_mail to Local's Mailpit instead of sending externally.
 */
function twintack_sandbox_mailpit_smtp( $phpmailer ) {
	if ( ! twintack_is_local_sandbox() ) {
		return;
	}

	$phpmailer->isSMTP();
	$phpmailer->Host     = 'localhost';
	$phpmailer->SMTPAuth   = false;
	$phpmailer->Port       = 10046; // Mailpit SMTP for this Local site — check php.ini sendmail_path if mail stops working.
	$phpmailer->SMTPSecure = '';
}
add_action( 'phpmailer_init', 'twintack_sandbox_mailpit_smtp' );

/**
 * Admin reminder that sandbox mode is active.
 */
function twintack_sandbox_admin_notice() {
	if ( ! twintack_is_local_sandbox() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="notice notice-info">
		<p><strong>TwinTack Local Sandbox</strong> is active — outbound webhooks, integration APIs, and customer emails are blocked or routed to Mailpit.</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'twintack_sandbox_admin_notice' );
