<?php
/**
 * GHL outbound — the only place WP talks to GoHighLevel.
 *
 * Per project rule: GHL is source of truth, WP is write-only. This class is
 * the single forwarder; every entry point (REST contact controller, FF hook,
 * AI chat lead, newsletter form) routes through `forward()` so we get a
 * uniform payload shape, uniform retry/log behavior, and one place to add
 * HMAC signing or batching later.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Integrations;

defined( 'ABSPATH' ) || exit;

final class Ghl {

	public const TYPE_CONTACT     = 'contact';
	public const TYPE_FLUENTFORM  = 'fluentform';
	public const TYPE_CHAT_LEAD   = 'chat_lead';
	public const TYPE_NEWSLETTER  = 'newsletter';

	/**
	 * Forward a payload to the configured GHL webhook.
	 *
	 * @param string                $type One of self::TYPE_*
	 * @param array<string, mixed>  $data Form data (already validated/sanitized).
	 * @param array<string, string> $context Extra metadata (page_url, referrer, utm_*).
	 *
	 * @return array{ok:bool, code?:int, reason?:string, detail?:string}
	 */
	public static function forward( string $type, array $data, array $context = array() ): array {
		$url = (string) get_option( 'showtime_ghl_webhook_url', '' );
		if ( '' === $url || ! wp_http_validate_url( $url ) ) {
			return array(
				'ok'     => false,
				'reason' => 'webhook_not_configured',
			);
		}

		$payload = array(
			'source'       => parse_url( home_url(), PHP_URL_HOST ) ?: 'showtimepools.com',
			'type'         => $type,
			'submitted_at' => current_time( 'c' ),
			'ip'           => self::client_ip(),
			'user_agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			'context'      => $context,
			'data'         => $data,
		);

		/**
		 * Filter the outbound GHL payload. Subsystems can attach UTM, A/B test,
		 * or fingerprint data without subclassing.
		 *
		 * @param array<string, mixed> $payload
		 * @param string               $type
		 */
		$payload = (array) apply_filters( 'showtime/ghl/payload', $payload, $type );

		$body    = (string) wp_json_encode( $payload );
		$headers = array(
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
		);

		$secret = (string) get_option( 'showtime_ghl_webhook_secret', '' );
		if ( '' !== $secret ) {
			$headers['X-Showtime-Signature'] = 'sha256=' . hash_hmac( 'sha256', $body, $secret );
		}

		$response = wp_remote_post(
			$url,
			array(
				'body'    => $body,
				'headers' => $headers,
				'timeout' => 10,
				'blocking'=> true,
			)
		);

		if ( is_wp_error( $response ) ) {
			self::log_failure( $type, 'http_error', $response->get_error_message(), $payload );
			return array(
				'ok'     => false,
				'reason' => 'http_error',
				'detail' => $response->get_error_message(),
			);
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$ok   = ( $code >= 200 && $code < 300 );

		if ( ! $ok ) {
			self::log_failure( $type, 'non_2xx', (string) $code, $payload );
		}

		do_action( 'showtime/ghl/forwarded', $type, $payload, $ok, $code );

		return array(
			'ok'   => $ok,
			'code' => $code,
		);
	}

	/**
	 * REMOTE_ADDR only — we do not trust X-Forwarded-For unless behind a
	 * known proxy. Cloudflare adds CF-Connecting-IP; if that header is
	 * present we trust it (Cloudflare strips client-set headers).
	 */
	private static function client_ip(): string {
		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		}
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}

	private static function log_failure( string $type, string $reason, string $detail, array $payload ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log(
				sprintf(
					'[Showtime GHL] forward failed type=%s reason=%s detail=%s',
					$type,
					$reason,
					$detail
				)
			);
		}
	}
}
