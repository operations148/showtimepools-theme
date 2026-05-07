<?php
/**
 * Uninstall: clean up plugin options ONLY. We do not delete CPT post data —
 * destroying customer projects/reviews on a misclick would be catastrophic.
 * If a user truly wants the CPT data gone, they delete it manually first.
 *
 * @package ShowtimePoolsCore
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$options = array(
	'showtime_ghl_webhook_url',
	'showtime_ghl_webhook_secret',
	'showtime_openai_api_key',
	'showtime_openai_assistant_id',
	'showtime_mapbox_token',
	'showtime_gbp_account_id',
	'showtime_gbp_location_id',
	'showtime_chat_rate_session',
	'showtime_chat_rate_daily',
);

foreach ( $options as $opt ) {
	delete_option( $opt );
}

// Clear scheduled cron jobs we registered.
$crons = array( 'showtime_gbp_sync', 'showtime_chat_log_purge' );
foreach ( $crons as $hook ) {
	wp_clear_scheduled_hook( $hook );
}
