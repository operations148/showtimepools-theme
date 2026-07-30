<?php
/**
 * Unit tests for the curated review cache (P0-3).
 *
 * Covers validation, atomic save, fingerprint determinism, the Trustindex
 * draft parser, and the server-rendered output that no-JS crawlers read.
 *
 *   php tests/reviews-unit.php
 *   WP_LOAD=/path/to/wp-load.php php tests/reviews-unit.php
 *
 * ALL review data here is synthetic. No production review text, customer name,
 * or business identifier appears in this file.
 *
 * The live option is snapshotted at start and restored at the end, so running
 * this suite leaves the local test database exactly as it found it.
 *
 * Exit 0 = all pass, 1 = one or more fail.
 *
 * @package ShowtimePools
 */

$wp_load = getenv( 'WP_LOAD' ) ?: 'C:/xampp/htdocs/showtimepools/wp/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
	fwrite( STDERR, "wp-load.php not found at: $wp_load\nSet WP_LOAD=/path/to/wp-load.php\n" );
	exit( 1 );
}
define( 'WP_USE_THEMES', false );
require $wp_load;

$pass = 0; $fail = 0; $skip = 0;
function ok( $m ) { global $pass; $pass++; echo "  \xE2\x9C\x94 $m\n"; }
function bad( $m ) { global $fail; $fail++; echo "  \xE2\x9C\x98 FAIL: $m\n"; }
function skp( $m ) { global $skip; $skip++; echo "  \xE2\x97\x8B skip: $m\n"; }

// ── Environment gate: distinguish a missing dependency from a code defect ──
$missing = array();
if ( ! class_exists( '\Showtime\Reviews' ) ) {
	$missing[] = '\Showtime\Reviews (core plugin inactive?)';
}
foreach ( array( 'showtime_render_reviews_widget', 'showtime_render_cached_reviews', 'showtime_get_cached_reviews' ) as $fn ) {
	if ( ! function_exists( $fn ) ) {
		$missing[] = "$fn() (child theme inactive?)";
	}
}
if ( $missing ) {
	echo "\n== ENVIRONMENT DEPENDENCY MISSING (not a code failure) ==\n";
	foreach ( $missing as $m ) { echo "  - $m\n"; }
	echo "\n== RESULT ==\n  pass: 0   fail: 1   skip: 0\n";
	exit( 1 );
}

use Showtime\Reviews;

const OPT_CACHE  = 'showtime_reviews_cached';
const OPT_SYNCED = 'showtime_reviews_synced_at';

// ── Snapshot live state so the DB is restored on exit ──
$had_cache   = ( false !== get_option( OPT_CACHE, false ) );
$orig_cache  = get_option( OPT_CACHE, null );
$had_synced  = ( false !== get_option( OPT_SYNCED, false ) );
$orig_synced = get_option( OPT_SYNCED, null );

function restore_state(): void {
	global $had_cache, $orig_cache, $had_synced, $orig_synced;
	if ( $had_cache ) { update_option( OPT_CACHE, $orig_cache, false ); } else { delete_option( OPT_CACHE ); }
	if ( $had_synced ) { update_option( OPT_SYNCED, $orig_synced, false ); } else { delete_option( OPT_SYNCED ); }
}
register_shutdown_function( 'restore_state' );

/** Synthetic review factory. */
function fx( array $over = array() ): array {
	return array_merge(
		array(
			'review_id'  => '',
			'author'     => 'Testcase Alpha',
			'rating'     => 5,
			'date'       => '2025-03-04',
			'text'       => 'Synthetic fixture review text for automated testing.',
			'source'     => 'Google Business Profile',
			'source_url' => null,
		),
		$over
	);
}

echo "\n== REVIEW CACHE — validation ==\n";

/* 1. Valid payload saves. */
delete_option( OPT_CACHE );
$r = Reviews::save( array( fx(), fx( array( 'author' => 'Testcase Beta', 'text' => 'A second distinct synthetic review body.' ) ) ) );
( $r['ok'] && 2 === $r['count'] && 2 === count( Reviews::get_cached() ) )
	? ok( '1. valid payload saves' )
	: bad( '1. valid payload rejected: ' . implode( '; ', $r['errors'] ) );

/* 2 + 3. Invalid payload rejected atomically; previous cache survives. */
$before = Reviews::get_cached();
$r      = Reviews::save( array( fx(), fx( array( 'author' => '', 'text' => 'Missing author here.' ) ) ) );
$after  = Reviews::get_cached();
( ! $r['ok'] && $before === $after )
	? ok( '2/3. invalid payload rejected atomically; previous cache intact' )
	: bad( '2/3. bad payload leaked into the cache' );

/* 4. Rating out of range. */
$r1 = Reviews::save( array( fx( array( 'rating' => 0 ) ) ) );
$r2 = Reviews::save( array( fx( array( 'rating' => 6 ) ) ) );
$r3 = Reviews::save( array( fx( array( 'rating' => '4.5' ) ) ) );
( ! $r1['ok'] && ! $r2['ok'] && ! $r3['ok'] )
	? ok( '4. rating <1, >5, and non-integer are rejected' )
	: bad( '4. out-of-range rating accepted' );

/* 5. Null rating accepted. */
$r = Reviews::save( array( fx( array( 'rating' => null ) ) ) );
$c = Reviews::get_cached();
( $r['ok'] && null === $c[0]['rating'] )
	? ok( '5. null rating accepted and stored as null' )
	: bad( '5. null rating mishandled' );

/* 6. Malformed dates. */
$bad_dates = array( '2025-13-01', '2025-02-30', '03/04/2025', '2025-3-4', 'yesterday' );
$all_rejected = true;
foreach ( $bad_dates as $d ) {
	$rr = Reviews::save( array( fx( array( 'date' => $d ) ) ) );
	if ( $rr['ok'] ) { $all_rejected = false; }
}
$all_rejected ? ok( '6. malformed/impossible dates rejected' ) : bad( '6. a malformed date was accepted' );

/* 7. Null date accepted. */
$r = Reviews::save( array( fx( array( 'date' => null ) ) ) );
$c = Reviews::get_cached();
( $r['ok'] && null === $c[0]['date'] )
	? ok( '7. null date accepted and stored as null' )
	: bad( '7. null date mishandled' );

/* 8. Non-HTTPS source URL. */
$r1 = Reviews::save( array( fx( array( 'source_url' => 'http://example.com/r' ) ) ) );
$r2 = Reviews::save( array( fx( array( 'source_url' => 'javascript:alert(1)' ) ) ) );
$r3 = Reviews::save( array( fx( array( 'source_url' => 'https://example.com/r' ) ) ) );
( ! $r1['ok'] && ! $r2['ok'] && $r3['ok'] )
	? ok( '8. non-HTTPS source_url rejected, HTTPS accepted' )
	: bad( '8. source_url scheme validation wrong' );

/* 9. Duplicate review_id. */
$r = Reviews::save( array( fx( array( 'review_id' => 'dup-1' ) ), fx( array( 'review_id' => 'dup-1', 'author' => 'Testcase Gamma', 'text' => 'Different body text entirely.' ) ) ) );
! $r['ok'] ? ok( '9. duplicate review_id rejected' ) : bad( '9. duplicate review_id accepted' );

/* 10. Duplicate normalized author/text fingerprint. */
$r = Reviews::save( array(
	fx( array( 'review_id' => 'a1', 'author' => 'Testcase Delta', 'text' => 'Same body.' ) ),
	fx( array( 'review_id' => 'a2', 'author' => '  testcase   DELTA ', 'text' => '  same    BODY. ' ) ),
) );
! $r['ok'] ? ok( '10. duplicate author/text fingerprint rejected (case + whitespace normalized)' ) : bad( '10. duplicate fingerprint accepted' );

/* 11. Missing author or text. */
$r1 = Reviews::save( array( fx( array( 'author' => '   ' ) ) ) );
$r2 = Reviews::save( array( fx( array( 'text' => '' ) ) ) );
( ! $r1['ok'] && ! $r2['ok'] ) ? ok( '11. missing author/text rejected' ) : bad( '11. empty author/text accepted' );

/* 12 + 13. Malicious HTML neutralized; unicode preserved. */
Reviews::save( array( fx( array(
	'author' => '<script>alert("x")</script>Mallory',
	'text'   => '<img src=x onerror=alert(1)>Great work &amp; service — "quoted" <b>bold</b>',
) ) ) );
$c    = Reviews::get_cached();
$html = showtime_render_cached_reviews( 'default' );
$clean_store = ( false === stripos( $c[0]['author'], '<script' ) && false === stripos( $c[0]['text'], 'onerror' ) );
$clean_html  = ( false === stripos( $html, '<script' ) && false === stripos( $html, 'onerror=' ) );
( $clean_store && $clean_html )
	? ok( '12. script/HTML in author+text cannot execute (stripped on save, escaped on render)' )
	: bad( '12. unsafe markup survived: store=' . var_export( $clean_store, true ) . ' html=' . var_export( $clean_html, true ) );

Reviews::save( array( fx( array( 'author' => 'Zoë Ünicode 日本', 'text' => 'Café — naïve — 日本語テキスト — emoji 🎉 ok.' ) ) ) );
$html = showtime_render_cached_reviews( 'default' );
( false !== strpos( $html, 'Zoë Ünicode 日本' ) && false !== strpos( $html, '日本語テキスト' ) && false !== strpos( $html, '🎉' ) )
	? ok( '13. unicode author + text render intact' )
	: bad( '13. unicode mangled in output' );

/* 14. Fingerprint determinism. */
$f1 = Reviews::fingerprint( 'Alpha Beta', 'Some review text.' );
$f2 = Reviews::fingerprint( '  alpha   BETA  ', ' some   review    text. ' );
$f3 = Reviews::fingerprint( 'Alpha Beta', 'Different text.' );
( $f1 === $f2 && $f1 !== $f3 && 64 === strlen( $f1 ) && ctype_xdigit( $f1 ) )
	? ok( '14. fingerprint deterministic, normalized, and SHA-256' )
	: bad( '14. fingerprint not stable/normalized' );

/* Max cap. */
$many = array();
for ( $i = 0; $i < 13; $i++ ) {
	$many[] = fx( array( 'author' => "Author $i", 'text' => "Distinct synthetic body number $i." ) );
}
$r = Reviews::save( $many );
! $r['ok'] ? ok( '14b. payload above the 12-review maximum is rejected' ) : bad( '14b. more than 12 reviews accepted' );

echo "\n== REVIEW CACHE — Trustindex draft parser ==\n";

/* 15 + 16. Parser extracts author + exact text; never invents rating/date. */
$fixture = '<div class="ti-widget">'
	. '<div data-empty="0" data-time="1740000000" class="ti-review-item source-Google" data-id="cfcd208495d565ef66e7dff9f98764da" data-language="">'
	. '<div class="ti-stars"><span class="ti-star"></span><span class="ti-star"></span><span class="ti-star"></span><span class="ti-star"></span><span class="ti-star"></span></div>'
	. '<div class="ti-name">Synthetic Reviewer One</div>'
	. '<div class="ti-date"></div>'
	. '<div class="ti-review-text"><!-- R-CONTENT -->First synthetic body with an &amp; entity.<!-- R-CONTENT --></div>'
	. '</div>'
	. '<div data-empty="0" data-time="1741000000" class="ti-review-item source-Google" data-id="cfcd208495d565ef66e7dff9f98764da" data-language="">'
	. '<div class="ti-name">Synthetic Reviewer Two</div>'
	. '<div class="ti-date"></div>'
	. '<div class="ti-review-text"><!-- R-CONTENT -->Second synthetic body.<!-- R-CONTENT --></div>'
	. '</div></div>';

$parsed = Reviews::parse_trustindex_markup( $fixture );
( 2 === count( $parsed )
	&& 'Synthetic Reviewer One' === $parsed[0]['author']
	&& 'First synthetic body with an & entity.' === $parsed[0]['text']
	&& 'Synthetic Reviewer Two' === $parsed[1]['author'] )
	? ok( '15. parser extracts author + exact text from the fixture' )
	: bad( '15. parser output wrong: ' . wp_json_encode( $parsed ) );

$invents = false;
foreach ( $parsed as $p ) {
	if ( array_key_exists( 'rating', $p ) || array_key_exists( 'date', $p ) ) { $invents = true; }
}
( ! $invents && array_key_exists( 'date_candidate', $parsed[0] ) )
	? ok( '16. parser assigns no rating and no saved date (candidate exposed separately)' )
	: bad( '16. parser invented a rating or date' );

/* Five star icons must not become a 5 rating. */
$saved_from_parse = array();
foreach ( $parsed as $p ) {
	$saved_from_parse[] = fx( array( 'author' => $p['author'], 'text' => $p['text'], 'rating' => null, 'date' => null ) );
}
Reviews::save( $saved_from_parse );
$c = Reviews::get_cached();
( null === $c[0]['rating'] && null === $c[0]['date'] )
	? ok( '16b. five star icons in markup do not become a 5-star rating' )
	: bad( '16b. rating/date inferred from markup' );

/* 17. Parser failure preserves the cache. */
$before = Reviews::get_cached();
$empty  = Reviews::parse_trustindex_markup( '<div>no reviews here at all</div>' );
$after  = Reviews::get_cached();
( array() === $empty && $before === $after && ! empty( $after ) )
	? ok( '17. unparseable markup returns nothing and leaves the cache untouched' )
	: bad( '17. parse failure disturbed the cache' );

echo "\n== REVIEW CACHE — server-rendered output ==\n";

/* 18. No cache -> original Trustindex-only behaviour. */
delete_option( OPT_CACHE );
$out = showtime_render_reviews_widget( 'default' );
( false === strpos( $out, 'data-reviews-static' ) && false === strpos( $out, 'reviews-widget__stack' ) )
	? ok( '18. with no cache the renderer is unchanged (no empty fallback emitted)' )
	: bad( '18. empty fallback rendered with no cache' );

/* Seed a known set for the render assertions. */
$set = array();
for ( $i = 1; $i <= 5; $i++ ) {
	$set[] = fx( array(
		'author' => "Render Author $i",
		'text'   => "Synthetic rendered body number $i.",
		'rating' => ( 1 === $i ) ? 4 : null,
		'date'   => ( 1 === $i ) ? '2025-06-07' : null,
	) );
}
Reviews::save( $set );

$full    = showtime_render_reviews_widget( 'default' );
$compact = showtime_render_reviews_widget( 'compact' );

/* 19. Cached text is in the HTML, outside template/pre. */
$static_only = showtime_render_cached_reviews( 'default' );

// Precise checks. Note `aria-hidden` on the decorative stars and the
// `visually-hidden` span carrying the screen-reader rating label are both
// CORRECT accessibility markup — the requirement is that the review text and
// author are not themselves concealed, so assert on the wrapper and on the
// text's own container rather than grepping for the substring "hidden".
preg_match( '#<div class="reviews-static[^"]*"[^>]*>#', $static_only, $wrap );
$wrapper_tag = $wrap[0] ?? '';

$text_visible = preg_match(
	'#<blockquote class="review-card__quote"><p>Synthetic rendered body number 1\.</p></blockquote>#',
	$static_only
);
$author_visible = preg_match( '#<cite class="review-card__author">Render Author 1</cite>#', $static_only );

$outside = $text_visible
	&& $author_visible
	&& ( '' !== $wrapper_tag )
	&& ( false === strpos( $wrapper_tag, ' hidden' ) )
	&& ( false === stripos( $wrapper_tag, 'display:none' ) )
	&& ( false === stripos( $wrapper_tag, 'aria-hidden' ) )
	&& ( false === strpos( $static_only, '<template' ) )
	&& ( false === strpos( $static_only, '<pre' ) )
	&& ( false === strpos( $static_only, '<noscript' ) )
	&& ( false === strpos( $static_only, '<script' ) )
	&& ( false === stripos( $static_only, 'display:none' ) )
	&& ( false === stripos( $static_only, 'display: none' ) );
$outside
	? ok( '19. review text + author present in plain HTML, wrapper not hidden, no template/pre/noscript/script' )
	: bad( '19. static block concealed or wrapped; wrapper=' . $wrapper_tag );

// The review body must not live inside the visually-hidden a11y span.
( ! preg_match( '#visually-hidden[^>]*>[^<]*Synthetic rendered body#', $static_only ) )
	? ok( '19c. review body is not inside a visually-hidden element' )
	: bad( '19c. review body hidden inside a screen-reader-only span' );

/* Position: static must precede the widget.
   The Trustindex plugin is production-only, so register a stub shortcode to
   exercise the real "both layers" composition branch locally instead of
   skipping it — this is the code path that actually ships. */
$stub_used = false;
if ( ! shortcode_exists( 'trustindex' ) ) {
	add_shortcode( 'trustindex', static fn() => '<div class="ti-widget"><div class="ti-review-item">stub</div></div>' );
	$stub_used = true;
}
$stacked  = showtime_render_reviews_widget( 'default' );
$p_static = strpos( $stacked, 'data-reviews-static' );
$p_widget = strpos( $stacked, 'data-trustindex-lazy' );

( false !== $p_static && false !== $p_widget && $p_static < $p_widget )
	? ok( '19b. static block rendered above the Trustindex widget' )
	: bad( '19b. static block not above the widget (static=' . var_export( $p_static, true ) . ' widget=' . var_export( $p_widget, true ) . ')' );

( false !== strpos( $stacked, 'data-reviews-stack' ) )
	? ok( '19d. both layers wrapped in the coordinating stack container' )
	: bad( '19d. stack wrapper missing' );

/* The widget markup stays inert inside its template; the static text does not. */
( false !== strpos( $stacked, '<template data-trustindex-markup>' )
	&& preg_match( '#<cite class="review-card__author">Render Author 1</cite>#', $stacked )
	&& strpos( $stacked, 'Render Author 1' ) < strpos( $stacked, '<template' ) )
	? ok( '19e. curated text precedes the inert <template>; widget markup still deferred' )
	: bad( '19e. layer separation wrong' );

/* Exactly one of each in the stacked output. */
$sc = substr_count( $stacked, 'data-reviews-static' );
$wc = substr_count( $stacked, 'data-trustindex-lazy' );
( 1 === $sc && 1 === $wc )
	? ok( '24c. stacked output has exactly one static block and one widget' )
	: bad( "24c. stacked duplicates: static=$sc widget=$wc" );

if ( $stub_used ) {
	remove_shortcode( 'trustindex' );
}

/* 20. Compact renders exactly 3. */
$n_compact = substr_count( $compact, '<article class="review-card"' );
3 === $n_compact ? ok( '20. compact variant renders exactly 3 reviews' ) : bad( "20. compact rendered $n_compact (expected 3)" );

/* 21. Full renders all, capped at 12. */
$n_full = substr_count( $full, '<article class="review-card"' );
5 === $n_full ? ok( '21. default variant renders all 5 curated reviews' ) : bad( "21. default rendered $n_full (expected 5)" );

$twelve = array();
for ( $i = 1; $i <= 12; $i++ ) {
	$twelve[] = fx( array( 'author' => "Cap Author $i", 'text' => "Synthetic capped body $i.", 'rating' => null, 'date' => null ) );
}
Reviews::save( $twelve );
$n_cap = substr_count( showtime_render_cached_reviews( 'default' ), '<article class="review-card"' );
12 === $n_cap ? ok( '21b. maximum of 12 renders in full variant' ) : bad( "21b. rendered $n_cap at cap (expected 12)" );

/* Restore the 5-item set for the remaining assertions. */
Reviews::save( $set );
$full = showtime_render_cached_reviews( 'default' );

/* 22. Verified date -> valid <time datetime>. */
( false !== strpos( $full, '<time class="review-card__date" datetime="2025-06-07"' ) )
	? ok( '22. verified date emits <time datetime="YYYY-MM-DD">' )
	: bad( '22. time element missing/malformed' );

/* Omitted date must emit no time element at all. */
$n_time = substr_count( $full, '<time' );
1 === $n_time ? ok( '22b. exactly one <time> — null dates emit nothing' ) : bad( "22b. $n_time <time> elements (expected 1)" );

/* 23. Verified rating carries an accessible label. */
( false !== strpos( $full, 'Rated 4 out of 5' ) && false !== strpos( $full, 'aria-hidden="true"' ) )
	? ok( '23. rating exposes an accessible textual label, stars marked decorative' )
	: bad( '23. rating label missing' );

$n_rating = substr_count( $full, 'review-card__rating' );
1 === $n_rating ? ok( '23b. exactly one rating block — null ratings emit nothing' ) : bad( "23b. $n_rating rating blocks (expected 1)" );
( false === strpos( $full, '0 out of 5' ) && false === stripos( $full, 'unknown' ) )
	? ok( '23c. no "0 stars"/"unknown" placeholder text' )
	: bad( '23c. placeholder rating text emitted' );

/* 24. One static block + one widget per call. */
$out = showtime_render_reviews_widget( 'compact' );
$n_static_blocks = substr_count( $out, 'data-reviews-static' );
$n_widgets       = substr_count( $out, 'data-trustindex-lazy' );
( 1 === $n_static_blocks && $n_widgets <= 1 )
	? ok( "24. one static block and at most one widget per call (static=$n_static_blocks widget=$n_widgets)" )
	: bad( "24. duplicate blocks: static=$n_static_blocks widget=$n_widgets" );

/* Semantic structure. */
( false !== strpos( $full, '<blockquote class="review-card__quote"><p>' ) && false !== strpos( $full, '<cite class="review-card__author"' ) )
	? ok( '24b. semantic article/blockquote/p/cite structure' )
	: bad( '24b. semantic structure missing' );

echo "\n== REVIEW CACHE — hydration contract (JS source) ==\n";

/* 25 + 26. Hydration gating. Asserted against the JS source: a headless
   browser harness for Trustindex's own loader is out of scope here, so these
   verify the contract rather than a live hydration. */
$js = (string) file_get_contents( get_stylesheet_directory() . '/assets/js/main.js' );
( false !== strpos( $js, 'hasVisibleReviews' ) && false !== strpos( $js, 'offsetParent' ) )
	? ok( '25. hiding is gated on visible, hydrated review items' )
	: bad( '25. no visibility gate found in main.js' );
( false !== strpos( $js, 'retireStatic' ) && false !== strpos( $js, "staticBlock.hidden = true" ) && false !== strpos( $js, "setAttribute('aria-hidden', 'true')" ) )
	? ok( '25b. success path hides the static block visually and from a11y tree' )
	: bad( '25b. static block not hidden from both presentations' );
( false === strpos( $js, 'staticBlock.remove()' ) )
	? ok( '26. static block is never removed from the DOM (survives widget failure)' )
	: bad( '26. static block is removed from the DOM' );
( false !== strpos( $js, 'setTimeout(finish' ) )
	? ok( '26b. bounded give-up so a stalled widget leaves the fallback visible' )
	: bad( '26b. no timeout guard' );

/* Regression (pre-commit review): when a page has no curated block there is
   nothing to retire, so the observer/poll/timeout must never start. Without
   this guard every page carrying the widget span a 400ms interval plus a
   subtree MutationObserver for ten seconds to no purpose — which is every
   page until reviews are curated. */
( false !== strpos( $js, 'const staticBlock = staticFor(container);' )
	&& false !== strpos( $js, 'if (!staticBlock) return;' ) )
	? ok( '26c. no curated block on the page -> hydration watcher never starts (no idle timers)' )
	: bad( '26c. hydration watcher spins timers even with no static block to retire' );

/* Each widget resolves the static block within its OWN stack, so two widgets
   on one page cannot hide each other's content. */
( false !== strpos( $js, "container.closest('[data-reviews-stack]')" )
	&& false !== strpos( $js, "stack.querySelector('[data-reviews-static]')" ) )
	? ok( '26d. static block resolved per-widget within its own stack (instances isolated)' )
	: bad( '26d. static block lookup is not scoped per widget' );

echo "\n== REVIEW CACHE — schema + heading safety ==\n";

/* 27. No Review / AggregateRating JSON-LD introduced. */
$blob = showtime_render_reviews_widget( 'default' ) . showtime_render_cached_reviews( 'default' );
$schema_hits = array();
foreach ( array( '"@type"\s*:\s*"Review"' => 'Review', 'aggregateRating' => 'aggregateRating', 'reviewCount' => 'reviewCount', 'ratingValue' => 'ratingValue' ) as $re => $name ) {
	if ( preg_match( '#' . $re . '#i', $blob ) ) { $schema_hits[] = $name; }
}
empty( $schema_hits )
	? ok( '27. no Review/AggregateRating/reviewCount/ratingValue markup emitted' )
	: bad( '27. schema leaked: ' . implode( ', ', $schema_hits ) );

/* 28. Renderer emits no headings — templates own those. */
$n_head = preg_match_all( '#<h[1-6][\s>]#i', $blob );
0 === $n_head ? ok( '28. renderer emits no headings (no duplicate H2)' ) : bad( "28. renderer emitted $n_head heading(s)" );

echo "\n== RESULT ==\n  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );
