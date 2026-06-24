<?php
/**
 * Cookie consent banner.
 *
 * Rendered in wp_footer, hidden by default (`hidden` attribute). consent.js
 * reveals it only when there is no stored choice, so it never re-prompts and
 * never blocks first paint. Fixed to the bottom of the viewport — it does not
 * affect the hero or LCP.
 *
 * Config comes from showtime_consent_config() (Showtime → Settings).
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$cfg = showtime_consent_config();

// Always guarantee a privacy-policy link in the banner. If the owner already
// put a link in the message, don't add a second one.
$message_has_link = false !== strpos( $cfg['message'], '<a ' );
?>
<div id="stp-consent" class="stp-consent" role="region" aria-label="<?php esc_attr_e( 'Cookie consent', 'showtime-pools' ); ?>" hidden>
	<div class="stp-consent__inner">
		<div class="stp-consent__body">
			<h2 class="stp-consent__title"><?php echo esc_html( $cfg['heading'] ); ?></h2>
			<p class="stp-consent__text">
				<?php echo wp_kses_post( $cfg['message'] ); ?>
				<?php if ( ! $message_has_link && $cfg['policy_url'] ) : ?>
					<a class="stp-consent__policy" href="<?php echo esc_url( $cfg['policy_url'] ); ?>"><?php esc_html_e( 'Privacy Policy', 'showtime-pools' ); ?></a>
				<?php endif; ?>
			</p>
		</div>

		<div class="stp-consent__actions">
			<button type="button" class="stp-consent__btn stp-consent__btn--ghost" data-stp-consent="prefs" aria-expanded="false" aria-controls="stp-consent-prefs">
				<?php echo esc_html( $cfg['prefs_label'] ); ?>
			</button>
			<button type="button" class="stp-consent__btn stp-consent__btn--ghost" data-stp-consent="reject">
				<?php echo esc_html( $cfg['reject_label'] ); ?>
			</button>
			<button type="button" class="stp-consent__btn stp-consent__btn--primary" data-stp-consent="accept">
				<?php echo esc_html( $cfg['accept_label'] ); ?>
			</button>
		</div>
	</div>

	<div id="stp-consent-prefs" class="stp-consent__prefs" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Cookie preferences', 'showtime-pools' ); ?>" hidden>
		<ul class="stp-consent__cats">
			<li class="stp-consent__cat">
				<label class="stp-consent__cat-label">
					<input type="checkbox" checked disabled>
					<span>
						<strong><?php esc_html_e( 'Strictly necessary', 'showtime-pools' ); ?></strong>
						<small><?php esc_html_e( 'Required for the site to work. Always on.', 'showtime-pools' ); ?></small>
					</span>
				</label>
			</li>
			<li class="stp-consent__cat">
				<label class="stp-consent__cat-label">
					<input type="checkbox" data-stp-cat="analytics">
					<span>
						<strong><?php esc_html_e( 'Analytics', 'showtime-pools' ); ?></strong>
						<small><?php esc_html_e( 'Helps us understand how visitors use the site.', 'showtime-pools' ); ?></small>
					</span>
				</label>
			</li>
			<li class="stp-consent__cat">
				<label class="stp-consent__cat-label">
					<input type="checkbox" data-stp-cat="marketing">
					<span>
						<strong><?php esc_html_e( 'Marketing', 'showtime-pools' ); ?></strong>
						<small><?php esc_html_e( 'Used to measure ads and show relevant offers.', 'showtime-pools' ); ?></small>
					</span>
				</label>
			</li>
		</ul>
		<div class="stp-consent__prefs-actions">
			<button type="button" class="stp-consent__btn stp-consent__btn--primary" data-stp-consent="save">
				<?php esc_html_e( 'Save preferences', 'showtime-pools' ); ?>
			</button>
		</div>
	</div>
</div>
