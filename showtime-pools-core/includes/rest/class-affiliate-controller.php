<?php
/**
 * REST: POST /wp-json/showtime/v1/affiliate
 *
 * Receives the Partner Program signup form on /affiliate/, validates, applies
 * the same honeypot + timestamp + IP-rate-limit anti-spam as the contact form,
 * and forwards to GHL via the central forwarder with type = affiliate (which
 * routes to the dedicated affiliate webhook when configured).
 *
 * Auth: nonce header X-WP-Nonce (action wp_rest), supplied to JS via
 * ShowtimeConfig.nonce (wp_localize_script in the theme enqueue).
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Rest;

use Showtime\Integrations\Ghl;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

final class AffiliateController {

	private const NS         = 'showtime/v1';
	private const ROUTE      = '/affiliate';
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
					'full_name' => array( 'type' => 'string', 'required' => true ),
					'email'     => array( 'type' => 'string', 'required' => true ),
					'phone'     => array( 'type' => 'string', 'required' => true ),
					'website'   => array( 'type' => 'string' ),
					'promote'   => array( 'type' => 'array' ),
					'consent'   => array( 'type' => 'boolean' ),
					'page_url'  => array( 'type' => 'string' ),
					'loaded_at' => array( 'type' => 'integer' ),
					'hp_url'    => array( 'type' => 'string' ), // honeypot
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

		// 3. Rate limit by IP.
		$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
		$rate_key = 'showtime_affiliate_rate_' . md5( $ip );
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
		$full_name = sanitize_text_field( (string) ( $params['full_name'] ?? '' ) );
		$email     = sanitize_email( (string) ( $params['email'] ?? '' ) );
		$phone     = preg_replace( '/[^0-9+\-\s\(\)]/', '', (string) ( $params['phone'] ?? '' ) );
		$website   = esc_url_raw( (string) ( $params['website'] ?? '' ) );
		$consent   = ! empty( $params['consent'] );

		$promote_raw = $params['promote'] ?? array();
		$promote     = array();
		if ( is_array( $promote_raw ) ) {
			foreach ( $promote_raw as $opt ) {
				$opt = sanitize_text_field( (string) $opt );
				if ( '' !== $opt ) {
					$promote[] = $opt;
				}
			}
		}

		$errors = array();
		if ( strlen( $full_name ) < 2 )      { $errors['full_name'] = __( 'Please share your full name.', 'showtime-pools-core' ); }
		if ( ! is_email( $email ) )          { $errors['email']     = __( 'A valid email address is required.', 'showtime-pools-core' ); }
		if ( strlen( (string) $phone ) < 7 ) { $errors['phone']     = __( 'A valid phone number helps us respond fastest.', 'showtime-pools-core' ); }
		if ( empty( $promote ) )             { $errors['promote']   = __( 'Pick at least one way you will refer pool owners.', 'showtime-pools-core' ); }
		if ( ! $consent )                    { $errors['consent']   = __( 'Please agree to the partner terms to continue.', 'showtime-pools-core' ); }

		if ( $errors ) {
			return new WP_REST_Response(
				array(
					'ok'     => false,
					'errors' => $errors,
				),
				422
			);
		}

		set_transient( $rate_key, $count + 1, self::RATE_TTL );

		// 5. Forward to GHL (affiliate type → dedicated webhook when set).
		$result = Ghl::forward(
			Ghl::TYPE_AFFILIATE,
			array(
				'full_name' => $full_name,
				'email'     => $email,
				'phone'     => (string) $phone,
				'website'   => $website,
				'promote'   => $promote,
				'consent'   => $consent,
			),
			array(
				'page_url' => esc_url_raw( (string) ( $params['page_url'] ?? '' ) ),
				'referrer' => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
			)
		);

		// Never expose webhook errors to the visitor — friendly success either
		// way; the failure is logged for ops (see Ghl::log_failure).
		return new WP_REST_Response(
			array(
				'ok'           => true,
				'message'      => __( 'Thanks! We will review your application and send your partner link shortly.', 'showtime-pools-core' ),
				'forwarded_ok' => (bool) ( $result['ok'] ?? false ),
			),
			200
		);
	}

	private function silent_ok(): WP_REST_Response {
		return new WP_REST_Response(
			array(
				'ok'      => true,
				'message' => __( 'Thanks! We will review your application and send your partner link shortly.', 'showtime-pools-core' ),
			),
			200
		);
	}
}
