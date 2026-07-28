<?php
/**
 * Accessibility smoke test — image alternatives + article content accuracy.
 *
 *   SHOWTIME_BASE_URL="https://showtimepools.com" php tests/a11y-smoke.php
 *   SHOWTIME_BASE_URL="http://localhost/showtimepools/wp" php tests/a11y-smoke.php
 *
 * Checks, per page:
 *   - every <img> has an alt ATTRIBUTE (a missing attribute is always a defect);
 *   - alt text is never a filename, never generic ("image"/"photo of"), and
 *     stays within a sane length;
 *   - alt="" is accepted ONLY where the image is genuinely decorative — it is
 *     hidden from assistive tech (self/ancestor aria-hidden) or it sits inside
 *     a link that already has an accessible name from its own text;
 *   - every linked image is inside a link with a non-empty accessible name.
 *
 * Plus pump-article content accuracy: no "Austin TX", and the corrected H2
 * present exactly once. Those two are DATABASE-owned (post_content) and can
 * only pass once the article is edited in wp-admin — see the deployment notes.
 *
 * Exit 0 = all pass, 1 = one or more fail.
 *
 * @package ShowtimePools
 */

$base = rtrim( getenv( 'SHOWTIME_BASE_URL' ) ?: 'http://localhost/showtimepools/wp', '/' );
$pass = 0; $fail = 0; $skip = 0;
function ok( $m ) { global $pass; $pass++; echo "  \xE2\x9C\x94 $m\n"; }
function bad( $m ) { global $fail; $fail++; echo "  \xE2\x9C\x98 FAIL: $m\n"; }
function skp( $m ) { global $skip; $skip++; echo "  \xE2\x97\x8B skip: $m\n"; }

function a11y_fetch( string $url ): array {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS      => 5,
		CURLOPT_TIMEOUT        => 20,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_USERAGENT      => 'showtime-a11y/1.0',
	) );
	$body = (string) curl_exec( $ch );
	$code = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );
	return array( 'code' => $code, 'body' => $body );
}

/** Strip tags/entities to test whether a link has real text. */
function a11y_text( string $html ): string {
	return trim( html_entity_decode( wp_strip_tags_lite( $html ), ENT_QUOTES ) );
}
function wp_strip_tags_lite( string $html ): string {
	return preg_replace( '#<[^>]*>#', ' ', $html );
}

/**
 * Is this <img> legitimately decorative?
 * True when it (or an unclosed ancestor <a>/<div>/<figure> before it) is
 * aria-hidden, or when it sits inside a link that already has visible text.
 */
function a11y_decorative_ok( string $html, int $img_pos, string $img_tag ): array {
	if ( preg_match( '#aria-hidden=["\']true["\']#i', $img_tag ) ) {
		return array( true, 'self aria-hidden' );
	}

	// Nearest enclosing <a> that has not been closed before the image.
	$before = substr( $html, 0, $img_pos );
	if ( preg_match_all( '#<a\b[^>]*>#is', $before, $m, PREG_OFFSET_CAPTURE ) ) {
		$last     = end( $m[0] );
		$open_tag = $last[0];
		$open_at  = $last[1];
		$closed   = strpos( substr( $before, $open_at ), '</a>' );
		if ( false === $closed ) {
			// We are inside this link.
			if ( preg_match( '#aria-hidden=["\']true["\']#i', $open_tag ) ) {
				return array( true, 'ancestor <a> aria-hidden' );
			}
			// Does the link contain its own text (e.g. a card title)?
			$after     = substr( $html, $img_pos );
			$close_at  = strpos( $after, '</a>' );
			$link_html = false !== $close_at ? substr( $after, 0, $close_at ) : '';
			$link_html = substr( $before, $open_at ) . $link_html;
			if ( '' !== a11y_text( preg_replace( '#<img\b[^>]*>#i', '', $link_html ) ) ) {
				return array( true, 'link has its own text' );
			}
			return array( false, 'inside a link with NO other text' );
		}
	}

	// Ancestor element (figure/div/section) carrying aria-hidden, unclosed.
	$tail = substr( $before, -1500 );
	if ( preg_match( '#<(?:div|figure|section|span)\b[^>]*aria-hidden=["\']true["\'][^>]*>(?:(?!</(?:div|figure|section|span)>).)*$#is', $tail ) ) {
		return array( true, 'ancestor block aria-hidden' );
	}

	return array( false, 'not hidden, not inside a named link' );
}

$looks_like_filename = static function ( string $alt ): bool {
	if ( preg_match( '#\.(jpe?g|png|webp|gif|svg|avif)\b#i', $alt ) ) {
		return true;
	}
	// e.g. "clean-swimming-pool-in-a-sunny-backyard-4211d78c"
	return (bool) preg_match( '#^[a-z0-9]+(?:[-_][a-z0-9]+){4,}$#i', trim( $alt ) );
};
$is_generic = static function ( string $alt ): bool {
	$a = strtolower( trim( $alt ) );
	if ( in_array( $a, array( 'image', 'photo', 'picture', 'img', 'graphic', 'logo' ), true ) ) {
		return true;
	}
	return (bool) preg_match( '#^(image|photo|picture)\s+of\b#i', $a );
};

echo "\n== ACCESSIBILITY SMOKE TEST — base: $base ==\n";

$pages = array(
	'/'                                   => 'homepage',
	'/pool-pump-repair-services/'         => 'pump article',
	'/services/pool-leak-detection/'      => 'service page',
	'/service-areas/sherman-oaks/'        => 'service-area page',
	'/complete-pool-maintenance-guide-los-angeles/' => 'blog article',
);
// Not seeded in the local test DB; a 404 there is expected, not a defect.
$live_only = array( 'pump article', 'blog article' );
$is_local  = false !== stripos( $base, 'localhost' );

foreach ( $pages as $path => $label ) {
	echo "\n[$label] $path\n";
	$r = a11y_fetch( $base . $path );
	if ( 200 !== $r['code'] ) {
		in_array( $label, $live_only, true )
			? skp( "$label HTTP {$r['code']} — live-only fixture" )
			: bad( "$label HTTP {$r['code']}" );
		continue;
	}
	$html = $r['body'];

	preg_match_all( '#<img\b[^>]*>#i', $html, $im, PREG_OFFSET_CAPTURE );
	$total = count( $im[0] );
	$problems = array();
	$decorative = 0;
	$described  = 0;

	foreach ( $im[0] as $entry ) {
		$tag = $entry[0];
		$pos = $entry[1];
		$src = preg_match( '#\bsrc=["\']([^"\']*)["\']#i', $tag, $s ) ? $s[1] : '';
		$file = basename( parse_url( $src, PHP_URL_PATH ) ?: $src );

		if ( ! preg_match( '#\balt=#i', $tag ) ) {
			$problems[] = "$file — MISSING alt attribute";
			continue;
		}
		preg_match( '#\balt=["\']([^"\']*)["\']#i', $tag, $a );
		$alt = isset( $a[1] ) ? trim( $a[1] ) : '';

		if ( '' === $alt ) {
			list( $okDec, $why ) = a11y_decorative_ok( $html, $pos, $tag );
			$okDec ? $decorative++ : $problems[] = "$file — empty alt but $why";
			continue;
		}

		$described++;
		if ( $looks_like_filename( $alt ) ) {
			$problems[] = "$file — alt looks like a filename: \"$alt\"";
		} elseif ( $is_generic( $alt ) ) {
			$problems[] = "$file — generic alt: \"$alt\"";
		} elseif ( mb_strlen( $alt ) > 125 ) {
			$problems[] = "$file — alt too long (" . mb_strlen( $alt ) . " chars)";
		}
	}

	if ( $problems ) {
		foreach ( $problems as $p ) { bad( "$label: $p" ); }
	} else {
		ok( "$label: $total images — $described described, $decorative decorative, 0 defects" );
	}
}

/* --- Pump article content accuracy (DATABASE-owned: post_content) --- */
echo "\n[pump article content]\n";
$pump = a11y_fetch( $base . '/pool-pump-repair-services/' );
if ( 200 !== $pump['code'] ) {
	skp( "pump article HTTP {$pump['code']} — live-only fixture" );
} else {
	$wanted = 'What Are the Features of Emergency Pool Pump Repair in Los Angeles County?';
	false === stripos( $pump['body'], 'Austin TX' )
		? ok( 'no "Austin TX"' )
		: bad( '"Austin TX" still present (post_content — fix in wp-admin)' );

	$n = substr_count( $pump['body'], $wanted );
	1 === $n
		? ok( 'corrected H2 present exactly once' )
		: bad( "corrected H2 found $n time(s), expected 1 (post_content — fix in wp-admin)" );
}

echo "\n== RESULT ==\n  pass: $pass   skip: $skip   fail: $fail\n";
exit( $fail > 0 ? 1 : 0 );
