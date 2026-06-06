<?php
/**
 * Cloudflare Turnstile — server-side verification for public forms.
 *
 * Keys are admin-configured (Showtime Pools → Settings). When they are not
 * set, is_configured() returns false and callers skip verification so forms
 * keep working until the keys are added (graceful degradation). When keys ARE
 * set, verify() fails closed: a missing/invalid token is rejected.
 *
 * Site key is public (rendered into the widget). Secret key is write-only and
 * never echoed back to the browser.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Security;

defined( 'ABSPATH' ) || exit;

final class Turnstile {

	private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

	public static function site_key(): string {
		return (string) get_option( 'showtime_turnstile_site_key', '' );
	}

	private static function secret(): string {
		return (string) get_option( 'showtime_turnstile_secret', '' );
	}

	/**
	 * True only when both keys are present — callers skip verification otherwise.
	 */
	public static function is_configured(): bool {
		return '' !== self::site_key() && '' !== self::secret();
	}

	/**
	 * Verify a Turnstile response token against Cloudflare. Fails closed:
	 * returns false on empty token, transport error, or unsuccessful verdict.
	 *
	 * @param string $token The cf-turnstile-response value from the form.
	 * @param string $ip    Resolved client IP (see Ghl::client_ip()).
	 */
	public static function verify( string $token, string $ip ): bool {
		$token = trim( $token );
		if ( '' === $token ) {
			return false;
		}

		$body = array(
			'secret'   => self::secret(),
			'response' => $token,
		);
		if ( '' !== $ip ) {
			$body['remoteip'] = $ip;
		}

		$response = wp_remote_post(
			self::VERIFY_URL,
			array(
				'body'    => $body,
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );

		return is_array( $data ) && ! empty( $data['success'] );
	}
}
