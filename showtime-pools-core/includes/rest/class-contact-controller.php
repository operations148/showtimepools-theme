<?php
/**
 * REST: POST /wp-json/showtime/v1/contact
 *
 * Receives the native contact form on /contact/, validates, applies a
 * honeypot + timestamp + IP-rate-limit check, and forwards to GHL via the
 * central forwarder. Returns a clean JSON envelope the client renders.
 *
 * Auth: nonce header X-WP-Nonce (action wp_rest). The form gets the nonce
 * via wp_localize_script (`ShowtimeConfig.nonce`).
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Rest;

use Showtime\Integrations\Ghl;
use Showtime\Security\Turnstile;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

final class ContactController {

	private const NS         = 'showtime/v1';
	private const ROUTE      = '/contact';
	private const RATE_MAX   = 5;
	private const RATE_TTL   = HOUR_IN_SECONDS;
	private const MIN_THINK  = 2; // seconds — bots submit instantly.

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_route' ) );
	}

	public function register_route(): void {
		register_rest_route(
			self::NS,
			self::ROUTE,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'name'      => array( 'type' => 'string', 'required' => true ),
					'email'     => array( 'type' => 'string', 'required' => true ),
					'phone'     => array( 'type' => 'string', 'required' => true ),
					'service'   => array( 'type' => 'string' ),
					'message'   => array( 'type' => 'string', 'required' => true ),
					'consent'   => array( 'type' => 'boolean' ),
					'page_url'  => array( 'type' => 'string' ),
					'loaded_at' => array( 'type' => 'integer' ),
					'hp_url'    => array( 'type' => 'string' ), // honeypot
					'turnstile_token' => array( 'type' => 'string' ),
				),
			)
		);
	}

	/**
	 * @return true|WP_Error
	 */
	public function check_permission( WP_REST_Request $request ) {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Bad request.', 'showtime-pools-core' ), array( 'status' => 403 ) );
		}
		return true;
	}

	public function handle( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_json_params();
		if ( ! is_array( $params ) || empty( $params ) ) {
			$params = $request->get_body_params();
		}
		$params = is_array( $params ) ? $params : array();

		// 1. Honeypot — bots fill this hidden field, humans don't.
		if ( ! empty( $params['hp_url'] ) ) {
			return $this->silent_ok();
		}

		// 2. Time-on-page check — submissions <2s after load are bots.
		$loaded_at = (int) ( $params['loaded_at'] ?? 0 );
		if ( $loaded_at > 0 ) {
			$elapsed = time() - $loaded_at;
			if ( $elapsed < self::MIN_THINK ) {
				return $this->silent_ok();
			}
		}

		// 3. Rate limit by IP (Cloudflare-aware — see Ghl::client_ip()).
		$ip       = Ghl::client_ip() ?: '0.0.0.0';
		$rate_key = 'showtime_contact_rate_' . md5( $ip );
		$count    = (int) get_transient( $rate_key );
		if ( $count >= self::RATE_MAX ) {
			return new WP_REST_Response(
				array(
					'ok'      => false,
					'message' => __( "You've sent a lot in the last hour. Try again later or call us directly.", 'showtime-pools-core' ),
				),
				429
			);
		}

		// 4. Validate.
		$name    = sanitize_text_field( (string) ( $params['name'] ?? '' ) );
		$email   = sanitize_email( (string) ( $params['email'] ?? '' ) );
		$phone   = preg_replace( '/[^0-9+\-\s\(\)]/', '', (string) ( $params['phone'] ?? '' ) );
		$service = sanitize_text_field( (string) ( $params['service'] ?? '' ) );
		$message = sanitize_textarea_field( (string) ( $params['message'] ?? '' ) );
		$consent = ! empty( $params['consent'] );

		$errors = array();
		if ( strlen( $name ) < 2 )    { $errors['name']    = __( 'Please share your name.', 'showtime-pools-core' ); }
		if ( ! is_email( $email ) )   { $errors['email']   = __( 'A valid email address is required.', 'showtime-pools-core' ); }
		if ( strlen( (string) $phone ) < 7 ) { $errors['phone'] = __( 'A valid phone number helps us respond fastest.', 'showtime-pools-core' ); }
		if ( strlen( $message ) < 10 ){ $errors['message'] = __( 'Tell us a bit more (10+ characters).', 'showtime-pools-core' ); }

		if ( $errors ) {
			return new WP_REST_Response(
				array(
					'ok'     => false,
					'errors' => $errors,
				),
				422
			);
		}

		// 4b. CAPTCHA — Cloudflare Turnstile. Skipped (graceful) until keys are
		// configured; fails closed once they are.
		if ( Turnstile::is_configured() ) {
			$token = sanitize_text_field( (string) ( $params['turnstile_token'] ?? '' ) );
			if ( ! Turnstile::verify( $token, $ip ) ) {
				return new WP_REST_Response(
					array(
						'ok'     => false,
						'errors' => array( 'turnstile' => __( 'Please complete the verification and try again.', 'showtime-pools-core' ) ),
					),
					422
				);
			}
		}

		set_transient( $rate_key, $count + 1, self::RATE_TTL );

		// 5. Forward to GHL.
		$result = Ghl::forward(
			Ghl::TYPE_CONTACT,
			array(
				'name'    => $name,
				'email'   => $email,
				'phone'   => (string) $phone,
				'service' => $service,
				'message' => $message,
				'consent' => $consent,
			),
			array(
				'page_url' => esc_url_raw( (string) ( $params['page_url'] ?? '' ) ),
				'referrer' => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
			)
		);

		// We never expose webhook errors to the visitor — they get a friendly
		// success either way; we log the failure for ops.
		return new WP_REST_Response(
			array(
				'ok'           => true,
				'message'      => __( 'Thanks. Steve or a senior tech will follow up within one business day.', 'showtime-pools-core' ),
				'forwarded_ok' => (bool) ( $result['ok'] ?? false ),
			),
			200
		);
	}

	private function silent_ok(): WP_REST_Response {
		return new WP_REST_Response(
			array(
				'ok'      => true,
				'message' => __( 'Thanks. Steve or a senior tech will follow up within one business day.', 'showtime-pools-core' ),
			),
			200
		);
	}
}
