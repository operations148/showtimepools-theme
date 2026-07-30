<?php
/**
 * Curated review cache — validation, normalization, storage, and parsing.
 *
 * P0-3. The live Trustindex widget renders its reviews inside two nested inert
 * layers (our <template>, then Trustindex's own hidden <pre><template>), so a
 * no-JS or partial-JS crawler sees only the heading and CTA. This class owns a
 * small, manually curated cache that the theme renders as ordinary semantic
 * HTML in the initial response, with the Trustindex widget kept below it as
 * progressive enhancement.
 *
 * Responsibility split (see CLAUDE.md architecture notes):
 *   - CORE PLUGIN (this file + Admin\ReviewsPage): import, validate, save.
 *   - CHILD THEME (inc/reviews-widget.php): read-only rendering.
 * The options are deliberately stored under bare `showtime_*` names — the same
 * convention as showtime_reviews_shortcode — so the theme can get_option() them
 * directly and keep rendering even if this plugin is deactivated. Nothing here
 * calls a theme function; the dependency only ever points plugin -> options.
 *
 * There is no automatic review API in this project. Nothing in this class polls
 * Google, GBP, or Trustindex's service; the "draft" importer only re-runs the
 * already-configured shortcode locally and reads the markup it returns.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime;

defined( 'ABSPATH' ) || exit;

final class Reviews {

	/** Curated review array. autoload=false — only read on review surfaces. */
	public const OPTION_CACHE = 'showtime_reviews_cached';

	/** Unix timestamp of the last successful save. */
	public const OPTION_SYNCED = 'showtime_reviews_synced_at';

	/** Hard ceiling on curated reviews. */
	public const MAX_REVIEWS = 12;

	/** How many render in the compact variant (homepage, hubs, area pages). */
	public const COMPACT_COUNT = 3;

	/** Upper bound on a single review body, in characters. */
	public const MAX_TEXT_LEN = 2000;

	/** Upper bound on an author display name, in characters. */
	public const MAX_AUTHOR_LEN = 120;

	/** Accepted `source` values. */
	public const ALLOWED_SOURCES = array(
		'Google Business Profile',
	);

	// ─────────────────────────────────────────────────────────────────────
	// Normalization + fingerprinting
	// ─────────────────────────────────────────────────────────────────────

	/**
	 * Collapse a string to its comparable form: lowercased, whitespace
	 * normalized, trimmed. Used only for dedup — never for display.
	 */
	public static function normalize( string $value ): string {
		$value = (string) preg_replace( '/\s+/u', ' ', $value );
		$value = trim( $value );
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
	}

	/**
	 * Deterministic SHA-256 fingerprint of author + text.
	 *
	 * Used as the review_id when the source provides no stable identifier.
	 * The Trustindex static markup exposes a data-id that is identical across
	 * every review (the MD5 of "0"), so it is never usable as an ID — this
	 * fingerprint is the fallback in practice, not the exception.
	 */
	public static function fingerprint( string $author, string $text ): string {
		return hash( 'sha256', self::normalize( $author ) . "\x1f" . self::normalize( $text ) );
	}

	// ─────────────────────────────────────────────────────────────────────
	// Validation
	// ─────────────────────────────────────────────────────────────────────

	/**
	 * Validate and normalize a full payload, atomically.
	 *
	 * Either every row is valid and you get the cleaned set back, or the whole
	 * payload is rejected with the reasons. There is no partial result: a bad
	 * row must never be able to displace a good cache.
	 *
	 * @param mixed $rows Raw decoded payload.
	 * @return array{ok:bool, reviews:array<int,array<string,mixed>>, errors:array<int,string>}
	 */
	public static function validate_payload( $rows ): array {
		$errors  = array();
		$clean   = array();
		$ids     = array();
		$prints  = array();

		if ( ! is_array( $rows ) ) {
			return array(
				'ok'      => false,
				'reviews' => array(),
				'errors'  => array( __( 'Payload must be a JSON array of reviews.', 'showtime-pools-core' ) ),
			);
		}

		// Reject associative/objecty payloads — the contract is a list.
		if ( ! empty( $rows ) && array_keys( $rows ) !== range( 0, count( $rows ) - 1 ) ) {
			return array(
				'ok'      => false,
				'reviews' => array(),
				'errors'  => array( __( 'Payload must be a JSON array (list) of reviews, not an object.', 'showtime-pools-core' ) ),
			);
		}

		if ( count( $rows ) > self::MAX_REVIEWS ) {
			$errors[] = sprintf(
				/* translators: 1: submitted count, 2: maximum */
				__( '%1$d reviews submitted; the maximum is %2$d.', 'showtime-pools-core' ),
				count( $rows ),
				self::MAX_REVIEWS
			);
		}

		foreach ( $rows as $i => $row ) {
			$n = (int) $i + 1;

			if ( ! is_array( $row ) ) {
				$errors[] = sprintf( /* translators: %d: row number */ __( 'Row %d: not an object.', 'showtime-pools-core' ), $n );
				continue;
			}

			// ── author (required) ──
			$author = isset( $row['author'] ) && is_scalar( $row['author'] )
				? sanitize_text_field( wp_strip_all_tags( (string) $row['author'] ) )
				: '';
			$author = trim( $author );
			if ( '' === $author ) {
				$errors[] = sprintf( /* translators: %d: row number */ __( 'Row %d: author is required.', 'showtime-pools-core' ), $n );
			} elseif ( mb_strlen( $author ) > self::MAX_AUTHOR_LEN ) {
				$errors[] = sprintf( /* translators: %d: row number */ __( 'Row %d: author exceeds the maximum length.', 'showtime-pools-core' ), $n );
			}

			// ── text (required) ──
			$text = isset( $row['text'] ) && is_scalar( $row['text'] )
				? sanitize_textarea_field( wp_strip_all_tags( (string) $row['text'] ) )
				: '';
			$text = trim( $text );
			if ( '' === $text ) {
				$errors[] = sprintf( /* translators: %d: row number */ __( 'Row %d: review text is required.', 'showtime-pools-core' ), $n );
			} elseif ( mb_strlen( $text ) > self::MAX_TEXT_LEN ) {
				$errors[] = sprintf( /* translators: %d: row number */ __( 'Row %d: review text exceeds the maximum length.', 'showtime-pools-core' ), $n );
			}

			// ── rating (optional; integer 1-5 or null) ──
			$rating = null;
			if ( isset( $row['rating'] ) && null !== $row['rating'] && '' !== $row['rating'] ) {
				$raw = $row['rating'];
				// Accept ints and integer-valued strings only. "4.5", true, and
				// arrays are all rejected — a rating is never inferred.
				if ( is_int( $raw ) || ( is_string( $raw ) && ctype_digit( trim( $raw ) ) ) ) {
					$rating = (int) $raw;
					if ( $rating < 1 || $rating > 5 ) {
						$errors[] = sprintf( /* translators: %d: row number */ __( 'Row %d: rating must be an integer from 1 to 5.', 'showtime-pools-core' ), $n );
						$rating   = null;
					}
				} else {
					$errors[] = sprintf( /* translators: %d: row number */ __( 'Row %d: rating must be an integer from 1 to 5, or null.', 'showtime-pools-core' ), $n );
				}
			}

			// ── date (optional; real calendar date in YYYY-MM-DD or null) ──
			$date = null;
			if ( isset( $row['date'] ) && null !== $row['date'] && '' !== $row['date'] ) {
				$raw = is_scalar( $row['date'] ) ? trim( (string) $row['date'] ) : '';
				if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $m )
					&& checkdate( (int) $m[2], (int) $m[3], (int) $m[1] ) ) {
					$date = $raw;
				} else {
					$errors[] = sprintf( /* translators: %d: row number */ __( 'Row %d: date must be a valid calendar date as YYYY-MM-DD, or null.', 'showtime-pools-core' ), $n );
				}
			}

			// ── source (defaults to the only allowed value) ──
			$source = isset( $row['source'] ) && is_scalar( $row['source'] )
				? sanitize_text_field( wp_strip_all_tags( (string) $row['source'] ) )
				: '';
			$source = '' === $source ? self::ALLOWED_SOURCES[0] : $source;
			if ( ! in_array( $source, self::ALLOWED_SOURCES, true ) ) {
				$errors[] = sprintf( /* translators: %d: row number */ __( 'Row %d: source is not an allowed value.', 'showtime-pools-core' ), $n );
			}

			// ── source_url (optional; HTTPS only) ──
			$source_url = null;
			if ( isset( $row['source_url'] ) && null !== $row['source_url'] && '' !== $row['source_url'] ) {
				$raw = is_scalar( $row['source_url'] ) ? trim( (string) $row['source_url'] ) : '';
				$url = esc_url_raw( $raw, array( 'https' ) );
				if ( '' !== $url && 0 === stripos( $url, 'https://' ) ) {
					$source_url = $url;
				} else {
					$errors[] = sprintf( /* translators: %d: row number */ __( 'Row %d: source_url must be an HTTPS URL, or null.', 'showtime-pools-core' ), $n );
				}
			}

			// Stop before dedup if this row is already broken.
			if ( '' === $author || '' === $text ) {
				continue;
			}

			// ── identity + dedup ──
			$print = self::fingerprint( $author, $text );
			$id    = isset( $row['review_id'] ) && is_scalar( $row['review_id'] )
				? sanitize_text_field( wp_strip_all_tags( (string) $row['review_id'] ) )
				: '';
			$id    = trim( $id );
			if ( '' === $id ) {
				$id = $print;
			}

			if ( isset( $ids[ $id ] ) ) {
				$errors[] = sprintf( /* translators: %d: row number */ __( 'Row %d: duplicate review_id.', 'showtime-pools-core' ), $n );
				continue;
			}
			if ( isset( $prints[ $print ] ) ) {
				$errors[] = sprintf( /* translators: %d: row number */ __( 'Row %d: duplicate review (same author and text).', 'showtime-pools-core' ), $n );
				continue;
			}
			$ids[ $id ]       = true;
			$prints[ $print ] = true;

			$clean[] = array(
				'review_id'  => $id,
				'author'     => $author,
				'rating'     => $rating,
				'date'       => $date,
				'text'       => $text,
				'source'     => $source,
				'source_url' => $source_url,
			);
		}

		if ( empty( $errors ) && empty( $clean ) ) {
			$errors[] = __( 'No reviews to save.', 'showtime-pools-core' );
		}

		return array(
			'ok'      => empty( $errors ),
			'reviews' => empty( $errors ) ? $clean : array(),
			'errors'  => $errors,
		);
	}

	// ─────────────────────────────────────────────────────────────────────
	// Storage
	// ─────────────────────────────────────────────────────────────────────

	/**
	 * Read the curated cache. Always returns a list, never a broken shape.
	 */
	public static function get_cached(): array {
		$raw = get_option( self::OPTION_CACHE, array() );
		if ( ! is_array( $raw ) ) {
			return array();
		}
		$out = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$author = isset( $row['author'] ) ? (string) $row['author'] : '';
			$text   = isset( $row['text'] ) ? (string) $row['text'] : '';
			if ( '' === trim( $author ) || '' === trim( $text ) ) {
				continue;
			}
			$out[] = $row;
		}
		return array_slice( $out, 0, self::MAX_REVIEWS );
	}

	/**
	 * Validate then atomically replace the cache.
	 *
	 * On any validation failure nothing is written and the previous cache is
	 * left exactly as it was.
	 *
	 * @return array{ok:bool, errors:array<int,string>, count:int}
	 */
	public static function save( $rows ): array {
		$result = self::validate_payload( $rows );
		if ( ! $result['ok'] ) {
			return array(
				'ok'     => false,
				'errors' => $result['errors'],
				'count'  => 0,
			);
		}

		update_option( self::OPTION_CACHE, $result['reviews'], false );
		update_option( self::OPTION_SYNCED, time(), false );

		return array(
			'ok'     => true,
			'errors' => array(),
			'count'  => count( $result['reviews'] ),
		);
	}

	/** Unix timestamp of the last successful save, or 0. */
	public static function synced_at(): int {
		return (int) get_option( self::OPTION_SYNCED, 0 );
	}

	// ─────────────────────────────────────────────────────────────────────
	// Trustindex draft import (local shortcode only — no external request)
	// ─────────────────────────────────────────────────────────────────────

	/**
	 * Resolve the configured widget shortcode.
	 *
	 * Mirrors the theme's fall-through, reading the same options. Duplicated
	 * (rather than calling the theme helper) so the plugin never depends on
	 * the child theme being active.
	 */
	public static function configured_shortcode(): string {
		$shortcode = (string) get_option( 'showtime_reviews_shortcode', '' );
		if ( '' === $shortcode ) {
			$shortcode = '[trustindex no-registration=google]';
		}
		return $shortcode;
	}

	/**
	 * Run the configured shortcode locally and return its markup.
	 *
	 * This executes the already-installed plugin's shortcode through
	 * do_shortcode(); it makes no outbound HTTP request of its own.
	 */
	public static function render_widget_markup(): string {
		$shortcode = self::configured_shortcode();
		$rendered  = trim( (string) do_shortcode( $shortcode ) );
		// do_shortcode returns the literal string when unregistered.
		return $rendered === $shortcode ? '' : $rendered;
	}

	/**
	 * Extract draft candidates from Trustindex's static markup.
	 *
	 * ONLY the two fields the investigation proved reliable are extracted:
	 * author and exact review text.
	 *
	 * Deliberately NOT extracted as saved values:
	 *   - rating: every review renders five identical star images regardless of
	 *     its true score, so a count of star icons proves nothing.
	 *   - date: the human-readable date div is empty until JS runs. A raw
	 *     data-time epoch is returned separately as `date_candidate` for an
	 *     administrator to eyeball, and is never written to the saved date.
	 *   - data-id: identical on every review, so it is never an identifier.
	 *
	 * @return array<int,array{author:string,text:string,date_candidate:?string}>
	 */
	public static function parse_trustindex_markup( string $html ): array {
		$out = array();
		if ( '' === trim( $html ) ) {
			return $out;
		}

		// Locate each review block. Attribute order varies, so match on the
		// class token inside any opening div and slice between offsets.
		if ( ! preg_match_all( '/<div[^>]*\bti-review-item\b[^>]*>/i', $html, $m, PREG_OFFSET_CAPTURE ) ) {
			return $out;
		}

		$opens = $m[0];
		$total = count( $opens );

		for ( $i = 0; $i < $total; $i++ ) {
			$tag     = (string) $opens[ $i ][0];
			$start   = (int) $opens[ $i ][1];
			$end     = ( $i + 1 < $total ) ? (int) $opens[ $i + 1 ][1] : strlen( $html );
			$segment = substr( $html, $start, $end - $start );

			// Author — <div class="ti-name">Name</div>
			$author = '';
			if ( preg_match( '/<div[^>]*\bti-name\b[^>]*>(.*?)<\/div>/is', $segment, $am ) ) {
				$author = self::clean_extracted( $am[1] );
			}

			// Text — delimited by paired <!-- R-CONTENT --> comments.
			$text = '';
			if ( preg_match( '/<!--\s*R-CONTENT\s*-->(.*?)<!--\s*R-CONTENT\s*-->/is', $segment, $tm ) ) {
				$text = self::clean_extracted( $tm[1] );
			}

			if ( '' === $author || '' === $text ) {
				continue;
			}

			// Unverified date candidate only — never promoted to `date`.
			$candidate = null;
			if ( preg_match( '/data-time="(\d+)"/', $tag, $dm ) ) {
				$ts = (int) $dm[1];
				if ( $ts > 0 ) {
					$candidate = gmdate( 'Y-m-d', $ts );
				}
			}

			$out[] = array(
				'author'         => $author,
				'text'           => $text,
				'date_candidate' => $candidate,
			);
		}

		return $out;
	}

	/** Strip markup + decode entities from an extracted fragment. */
	private static function clean_extracted( string $fragment ): string {
		$fragment = preg_replace( '/<br\s*\/?>/i', "\n", $fragment );
		$fragment = wp_strip_all_tags( (string) $fragment );
		$fragment = html_entity_decode( $fragment, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$fragment = (string) preg_replace( '/[ \t]+/', ' ', $fragment );
		return trim( $fragment );
	}
}
