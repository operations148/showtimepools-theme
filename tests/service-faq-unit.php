<?php
/**
 * Service-page FAQ regression tests.
 *
 * Covers the nine net-new FAQs added to six existing service pages: that they
 * live in the code-managed registry, render server-side inside the existing
 * `<details>` accordion, appear exactly once on their own page and nowhere
 * else, stay in lockstep with the single FAQPage node, and that nothing else —
 * homepage FAQs, non-target services, existing questions, metadata, or the GHL
 * widget — moved.
 *
 *   php tests/service-faq-unit.php
 *   WP_LOAD=/path/to/wp-load.php php tests/service-faq-unit.php
 *
 * Read-only: creates no posts and writes no options.
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
function skip( $m ) { global $skip; $skip++; echo "  ~ SKIP: $m\n"; }

function fetch_body( string $url ): string {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT        => 25,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_USERAGENT      => 'showtime-service-faq-test/1.0',
	) );
	$b = (string) curl_exec( $ch );
	curl_close( $ch );
	return $b;
}

/** Strip every JSON-LD block so "visible" means what a reader actually sees. */
function visible_html( string $body ): string {
	return (string) preg_replace( '#<script type="application/ld\+json">.*?</script>#s', '', $body );
}

/** Decode the page's FAQPage node, or null. Also reports how many exist. */
function faq_nodes( string $body ): array {
	preg_match_all( '#<script type="application/ld\+json">(.*?)</script>#s', $body, $m );
	$nodes = array();
	foreach ( $m[1] ?? array() as $json ) {
		$d = json_decode( $json, true );
		if ( is_array( $d ) && 'FAQPage' === ( $d['@type'] ?? '' ) ) { $nodes[] = $d; }
	}
	return $nodes;
}

if ( ! class_exists( '\Showtime\Services' ) ) {
	bad( '\Showtime\Services not loaded — is showtime-pools-core active?' );
	echo "\n== RESULT ==\n  pass: 0   fail: 1   skip: 0\n";
	exit( 1 );
}

$base = rtrim( (string) get_option( 'home' ), '/' );

// The nine approved additions, keyed by the service they belong to. The answer
// fragment is a distinctive tail of the approved answer, so a truncated or
// reworded answer fails.
$ADDED = array(
	'pool-repairs-plumbing' => array(
		array(
			'q' => 'Do you repair older pool systems when parts are discontinued?',
			'a' => 'we provide a written repair-versus-replacement comparison before any work is approved.',
		),
	),
	'weekly-pool-maintenance' => array(
		array(
			'q' => 'Do you provide weekly pool service for HOAs, apartment communities, and other shared-use pools?',
			'a' => 'any tenant or guest access requirements with the property manager in the written service plan.',
		),
	),
	'equipment-installation-upgrades' => array(
		array(
			'q' => 'Are variable-speed pool pump motors required in California?',
			'a' => 'we verify the motor type, horsepower, and compliant equipment selection during the assessment.',
		),
	),
	'pool-remodeling-resurfacing' => array(
		array(
			'q' => 'Can you convert my pool to a saltwater system during a remodel?',
			'a' => 'compatibility and warranty requirements are confirmed in the written quote.',
		),
		array(
			'q' => 'How are payments structured for a pool remodeling project?',
			'a' => 'documented in writing and requires approval before the extra work begins.',
		),
		array(
			'q' => 'What happens if hidden damage is discovered after the pool is drained or demolition begins?',
			'a' => 'No additional work proceeds until the owner approves it.',
		),
		array(
			'q' => 'Do you remodel pools for HOAs, apartment communities, or other commercial properties?',
			'a' => 'so the property manager understands the sequence before work begins.',
		),
	),
	'custom-pool-design-construction' => array(
		array(
			'q' => 'Will inspections be required during my new pool project?',
			'a' => 'We coordinate the applicable inspections and include them in the project schedule.',
		),
	),
	'outdoor-living-hardscape' => array(
		array(
			'q' => 'What should I do before hardscape construction begins?',
			'a' => 'Do not remove fixed items unless the project team requests it.',
		),
	),
);

// Contract FAQ count per target page after the additions.
$EXPECTED = array(
	'pool-repairs-plumbing'           => 10,
	'weekly-pool-maintenance'         => 10,
	'equipment-installation-upgrades' => 10,
	'pool-remodeling-resurfacing'     => 13,
	'custom-pool-design-construction' => 10,
	'outdoor-living-hardscape'        => 10,
);

$all_services = \Showtime\Services::all();
$by_slug      = array();
foreach ( $all_services as $svc ) { $by_slug[ (string) $svc['slug'] ] = $svc; }

echo "\n== REGISTRY — the nine approved FAQs ==\n";

/* 1. All nine exist in the authoritative registry with their exact wording. */
$missing = array();
foreach ( $ADDED as $slug => $items ) {
	$faqs = (array) ( $by_slug[ $slug ]['default_faqs'] ?? array() );
	$qs   = array_column( $faqs, 'q' );
	foreach ( $items as $item ) {
		$idx = array_search( $item['q'], $qs, true );
		if ( false === $idx ) { $missing[] = "$slug :: {$item['q']}"; continue; }
		if ( false === strpos( (string) $faqs[ $idx ]['a'], $item['a'] ) ) {
			$missing[] = "$slug :: answer text differs for {$item['q']}";
		}
	}
}
empty( $missing )
	? ok( '1. all 9 approved FAQs exist in the registry with their approved answers' )
	: bad( '1. ' . implode( '; ', $missing ) );

/* 2. Each added FAQ is mapped ONLY to its assigned service. */
$mismapped = array();
foreach ( $ADDED as $owner => $items ) {
	foreach ( $items as $item ) {
		foreach ( $by_slug as $slug => $svc ) {
			if ( $slug === $owner ) { continue; }
			$qs = array_column( (array) ( $svc['default_faqs'] ?? array() ), 'q' );
			if ( in_array( $item['q'], $qs, true ) ) { $mismapped[] = "'{$item['q']}' also on $slug"; }
		}
	}
}
empty( $mismapped )
	? ok( '2. every added FAQ is mapped to exactly one service' )
	: bad( '2. ' . implode( '; ', $mismapped ) );

/* 5-10. Contract FAQ counts per target page. */
$count_bad = array();
foreach ( $EXPECTED as $slug => $n ) {
	$actual = count( (array) ( $by_slug[ $slug ]['default_faqs'] ?? array() ) );
	if ( $actual !== $n ) { $count_bad[] = "$slug=$actual (expected $n)"; }
}
empty( $count_bad )
	? ok( '5-10. contract FAQ counts: repairs 10, weekly 10, equipment 10, remodeling 13, construction 10, hardscape 10' )
	: bad( '5-10. ' . implode( ', ', $count_bad ) );

/* 12 + 13. Non-target services untouched, and every PRE-EXISTING question and
 * answer on the target services is byte-for-byte unchanged. The committed
 * version at HEAD is the baseline, so this catches an accidental reword. */
$baseline = null;
$tmp      = tempnam( sys_get_temp_dir(), 'svcbase' ) . '.php';
$repo     = dirname( __DIR__ );
$cmd      = 'git -C ' . escapeshellarg( $repo ) . ' show HEAD:showtime-pools-core/includes/data/services.php';
$out      = shell_exec( $cmd );
if ( is_string( $out ) && '' !== trim( $out ) ) {
	file_put_contents( $tmp, $out );
	$baseline = include $tmp;
	@unlink( $tmp );
}
if ( ! is_array( $baseline ) ) {
	skip( '12-13. could not read the committed services.php baseline via git' );
} else {
	$base_by = array();
	foreach ( $baseline as $svc ) { $base_by[ (string) $svc['slug'] ] = $svc; }

	$untouched_bad = array();
	foreach ( $by_slug as $slug => $svc ) {
		if ( isset( $EXPECTED[ $slug ] ) ) { continue; }
		if ( wp_json_encode( $svc ) !== wp_json_encode( $base_by[ $slug ] ?? null ) ) { $untouched_bad[] = $slug; }
	}
	empty( $untouched_bad )
		? ok( '12. all ' . ( count( $by_slug ) - count( $EXPECTED ) ) . ' non-target services are unchanged' )
		: bad( '12. changed non-target services: ' . implode( ', ', $untouched_bad ) );

	$changed = array();
	foreach ( $by_slug as $slug => $svc ) {
		$prev = (array) ( $base_by[ $slug ]['default_faqs'] ?? array() );
		$curr = array();
		foreach ( (array) ( $svc['default_faqs'] ?? array() ) as $f ) { $curr[ $f['q'] ] = $f['a']; }
		foreach ( $prev as $f ) {
			if ( ! array_key_exists( $f['q'], $curr ) )  { $changed[] = "$slug: removed '{$f['q']}'"; continue; }
			if ( $curr[ $f['q'] ] !== $f['a'] )          { $changed[] = "$slug: reworded '{$f['q']}'"; }
		}
		// Target services must differ ONLY in default_faqs.
		if ( isset( $EXPECTED[ $slug ] ) ) {
			$a = $svc; $b = (array) ( $base_by[ $slug ] ?? array() );
			unset( $a['default_faqs'], $b['default_faqs'] );
			if ( wp_json_encode( $a ) !== wp_json_encode( $b ) ) { $changed[] = "$slug: a non-FAQ field changed"; }
		}
	}
	empty( $changed )
		? ok( '13. every pre-existing question and answer is byte-for-byte unchanged; only default_faqs grew' )
		: bad( '13. ' . implode( '; ', $changed ) );
}

/* 14. None of the rejected secondary-site wording entered the registry. */
$blob   = strtolower( (string) wp_json_encode( $all_services ) );
$banned = array(
	'winteriz', 'pool opening', 'pool closing', 'quarterly plant',
	'landscaping maintenance plan', 'every project qualifies', 'up to 70%',
	'property value', '1-4 week', "1\u{2013}4 week", 'eco-friendly', 'eco - friendly', 'contractos',
);
$hits = array();
foreach ( $banned as $needle ) {
	if ( false !== strpos( $blob, strtolower( $needle ) ) ) { $hits[] = $needle; }
}
empty( $hits )
	? ok( '14. no rejected secondary-site wording appears in the registry' )
	: bad( '14. rejected wording present: ' . implode( ', ', $hits ) );

/* 14b. The nine new answers invent no percentages, deposits or dollar figures. */
$fab = array();
foreach ( $ADDED as $slug => $items ) {
	$faqs = (array) ( $by_slug[ $slug ]['default_faqs'] ?? array() );
	foreach ( $faqs as $f ) {
		foreach ( $items as $item ) {
			if ( $f['q'] !== $item['q'] ) { continue; }
			if ( preg_match( '/\d+\s?%|\$[\d,]+/', (string) $f['a'] ) ) { $fab[] = "$slug :: {$f['q']}"; }
		}
	}
}
empty( $fab )
	? ok( '14b. no invented percentage, deposit or dollar figure in the nine new answers' )
	: bad( '14b. ' . implode( ', ', $fab ) );

echo "\n== RENDERED HTML (server-side, no JavaScript executed) ==\n";

$bodies = array();
foreach ( $EXPECTED as $slug => $n ) { $bodies[ $slug ] = fetch_body( "$base/services/$slug/" ); }

/* 3 + 4. Each added question appears EXACTLY ONCE in the visible document, with
 * its complete answer, in the server-rendered HTML. */
$render_bad = array();
foreach ( $ADDED as $slug => $items ) {
	$vis = visible_html( $bodies[ $slug ] );
	foreach ( $items as $item ) {
		$needle = htmlspecialchars( $item['q'], ENT_QUOTES );
		$n      = substr_count( $vis, $needle );
		if ( 0 === $n ) { $n = substr_count( $vis, $item['q'] ); }
		if ( 1 !== $n ) { $render_bad[] = "$slug: question x$n"; }
		$plain = html_entity_decode( wp_strip_all_tags( $vis ) );
		if ( false === strpos( $plain, $item['a'] ) ) { $render_bad[] = "$slug: answer missing for '{$item['q']}'"; }
	}
}
empty( $render_bad )
	? ok( '3-4. every added question appears exactly once, with its complete answer, in server-rendered HTML' )
	: bad( '3-4. ' . implode( '; ', $render_bad ) );

/* 5-10 rendered. Visible accordion item count matches the contract. */
$vis_bad = array();
foreach ( $EXPECTED as $slug => $n ) {
	$c = preg_match_all( '#<details class="faq__item"#', $bodies[ $slug ] );
	if ( $c !== $n ) { $vis_bad[] = "$slug rendered $c (expected $n)"; }
}
empty( $vis_bad )
	? ok( '5-10b. rendered accordion item counts match the contract on all six pages' )
	: bad( '5-10b. ' . implode( ', ', $vis_bad ) );

/* 18. With JavaScript disabled the markup is identical — the accordion is a
 * native <details>, so every answer ships in the initial HTML. Asserted by
 * confirming each answer body is present in the raw response. */
$nojs_bad = array();
foreach ( $EXPECTED as $slug => $n ) {
	$answers = preg_match_all( '#<div class="faq__a">(.*?)</div>#s', $bodies[ $slug ], $am );
	if ( $answers !== $n ) { $nojs_bad[] = "$slug: $answers answer bodies (expected $n)"; continue; }
	foreach ( $am[1] as $a ) {
		if ( strlen( trim( wp_strip_all_tags( $a ) ) ) < 40 ) { $nojs_bad[] = "$slug: an answer body is empty in raw HTML"; }
	}
}
empty( $nojs_bad )
	? ok( '18. every answer body ships in the raw HTML — full content without JavaScript' )
	: bad( '18. ' . implode( '; ', $nojs_bad ) );

/* 15. No secondary-site URL anywhere in the rendered main site. */
$leak = 0;
foreach ( $bodies as $b ) { $leak += substr_count( strtolower( $b ), 'showtimepoolservice' ); }
$leak += substr_count( strtolower( fetch_body( "$base/" ) ), 'showtimepoolservice' );
0 === $leak
	? ok( '15. no showtimepoolservice.com URL appears in rendered main-site HTML' )
	: bad( "15. $leak secondary-site references found" );

/* 16. No duplicate id attributes on any target page, and the accordion needs
 * none (native <details> carries its own semantics). */
$dup_bad = array();
foreach ( $bodies as $slug => $b ) {
	preg_match_all( '#\sid="([^"]+)"#', $b, $im );
	$ids  = $im[1] ?? array();
	$dupe = array_values( array_unique( array_diff_assoc( $ids, array_unique( $ids ) ) ) );
	if ( $dupe ) { $dup_bad[] = "$slug: " . implode( ',', $dupe ); }
}
empty( $dup_bad )
	? ok( '16. no duplicate id attributes on any of the six target pages' )
	: bad( '16. ' . implode( '; ', $dup_bad ) );

/* 17. The accordion keeps its accessible structure: one <summary> per
 * <details>, each with a real answer container. <summary> is natively
 * focusable and Enter/Space toggles it, so no ARIA shim is required. */
$a11y_bad = array();
foreach ( $EXPECTED as $slug => $n ) {
	$d = preg_match_all( '#<details class="faq__item#', $bodies[ $slug ] );
	$s = preg_match_all( '#<summary class="faq__q">#', $bodies[ $slug ] );
	$a = preg_match_all( '#<div class="faq__a">#', $bodies[ $slug ] );
	if ( $d !== $n || $s !== $n || $a !== $n ) { $a11y_bad[] = "$slug: details=$d summary=$s answers=$a"; }
}
empty( $a11y_bad )
	? ok( '17. every FAQ is a native <details>/<summary> pair with its own answer container' )
	: bad( '17. ' . implode( '; ', $a11y_bad ) );

/* No second H1 was introduced. */
$h1_bad = array();
foreach ( $bodies as $slug => $b ) {
	$h = preg_match_all( '#<h1[\s>]#', $b );
	if ( 1 !== $h ) { $h1_bad[] = "$slug=$h"; }
}
empty( $h1_bad )
	? ok( '17b. exactly one H1 on every target page' )
	: bad( '17b. H1 counts: ' . implode( ', ', $h1_bad ) );

echo "\n== STRUCTURED DATA ==\n";

/* 19. Exactly one FAQPage node per target page. */
$node_bad = array();
foreach ( $bodies as $slug => $b ) {
	$n = count( faq_nodes( $b ) );
	if ( 1 !== $n ) { $node_bad[] = "$slug=$n"; }
}
empty( $node_bad )
	? ok( '19. exactly one FAQPage node on every target page' )
	: bad( '19. FAQPage node counts: ' . implode( ', ', $node_bad ) );

/* 20 + 21. Every schema question/answer is visible on the page, verbatim.
 *
 * NOTE ON COUNTS: this component deliberately folds the visible "Common
 * problems" Q&A into the same FAQPage entity set as the accordion (see
 * template-parts/service/schema.php). So the schema question count equals
 * accordion + deduped problems, not accordion alone. That is pre-existing and
 * intentional; what matters for correctness is that NOTHING in the schema is
 * hidden or invented, which is what is asserted here. */
$parity_bad = array();
foreach ( $bodies as $slug => $b ) {
	$nodes = faq_nodes( $b );
	if ( ! $nodes ) { $parity_bad[] = "$slug: no FAQPage"; continue; }
	$vis   = visible_html( $b );
	$plain = html_entity_decode( wp_strip_all_tags( $vis ) );
	$accordion = preg_match_all( '#<details class="faq__item"#', $b );
	$problems  = preg_match_all( '#class="svc-problems__q"#', $b );
	$schemaQ   = count( $nodes[0]['mainEntity'] ?? array() );

	foreach ( $nodes[0]['mainEntity'] ?? array() as $entity ) {
		$q = (string) ( $entity['name'] ?? '' );
		$a = (string) ( $entity['acceptedAnswer']['text'] ?? '' );
		if ( false === strpos( $vis, htmlspecialchars( $q, ENT_QUOTES ) ) && false === strpos( $vis, $q ) ) {
			$parity_bad[] = "$slug: schema question not visible — '" . substr( $q, 0, 44 ) . "'";
		}
		if ( '' !== $a && false === strpos( $plain, $a ) ) {
			$parity_bad[] = "$slug: schema answer not visible — '" . substr( $q, 0, 44 ) . "'";
		}
	}
	// Schema must account for exactly the two visible sources, nothing more.
	if ( $schemaQ > $accordion + $problems ) {
		$parity_bad[] = "$slug: schema has $schemaQ questions but only " . ( $accordion + $problems ) . ' visible Q&As';
	}
	if ( $schemaQ < $accordion ) {
		$parity_bad[] = "$slug: schema ($schemaQ) omits accordion questions ($accordion)";
	}
}
empty( $parity_bad )
	? ok( '20-21. every schema question and answer is visible on its page, verbatim, with no invented entries' )
	: bad( '20-21. ' . implode( '; ', $parity_bad ) );

/* 22. No Product / Review / AggregateRating / rating schema introduced. The
 * pre-existing Service.offers price block is untouched and NOT asserted away. */
$schema_bad = array();
foreach ( $bodies as $slug => $b ) {
	foreach ( array( '"@type":"Product"', '"@type":"Review"', 'aggregateRating', 'ratingValue', 'reviewCount' ) as $needle ) {
		if ( false !== stripos( $b, $needle ) ) { $schema_bad[] = "$slug:$needle"; }
	}
}
empty( $schema_bad )
	? ok( '22. no Product, Review, AggregateRating, ratingValue or reviewCount schema on any target page' )
	: bad( '22. ' . implode( ', ', $schema_bad ) );

echo "\n== SCOPE PROTECTION ==\n";

/* 11. The homepage FAQ is untouched and carries none of the new questions. */
$home     = fetch_body( "$base/" );
$home_n   = preg_match_all( '#<details class="faq__item#', $home );
$home_leak = 0;
foreach ( $ADDED as $items ) {
	foreach ( $items as $item ) { $home_leak += substr_count( $home, htmlspecialchars( $item['q'], ENT_QUOTES ) ); }
}
( 9 === $home_n && 0 === $home_leak )
	? ok( '11. homepage FAQ still has its own 9 items and none of the new questions' )
	: bad( "11. homepage faq items=$home_n (expected 9), new-question leaks=$home_leak" );

/* 12b. Rendered non-target service pages carry none of the new questions. */
$other_bad = array();
foreach ( $by_slug as $slug => $svc ) {
	if ( isset( $EXPECTED[ $slug ] ) ) { continue; }
	$b = fetch_body( "$base/services/$slug/" );
	foreach ( $ADDED as $items ) {
		foreach ( $items as $item ) {
			if ( false !== strpos( $b, htmlspecialchars( $item['q'], ENT_QUOTES ) ) ) { $other_bad[] = "$slug"; }
		}
	}
}
empty( $other_bad )
	? ok( '12b. no non-target service page renders any of the nine new questions' )
	: bad( '12b. leaked onto: ' . implode( ', ', array_unique( $other_bad ) ) );

/* 23. Canonical, robots, OG and Twitter singletons unchanged on target pages. */
$meta_bad = array();
foreach ( $bodies as $slug => $b ) {
	$checks = array(
		'canonical'   => '#<link rel="canonical"#',
		'robots'      => "#<meta name=['\"]robots#",
		'og:title'    => '#property="og:title"#',
		'og:image'    => '#property="og:image"#',
		'twitter:title' => '#name="twitter:title"#',
		'twitter:image' => '#name="twitter:image"#',
	);
	foreach ( $checks as $label => $re ) {
		if ( 1 !== preg_match_all( $re, $b ) ) { $meta_bad[] = "$slug:$label"; }
	}
}
empty( $meta_bad )
	? ok( '23. canonical, robots, OG and Twitter remain exactly one each on every target page' )
	: bad( '23. ' . implode( ', ', $meta_bad ) );

/* 24. The GHL widget ID and loader are unchanged on service pages. */
$ghl_bad = array();
foreach ( $bodies as $slug => $b ) {
	if ( 1 !== substr_count( $b, '69b32c236a7fada7ea40faca' ) )                                { $ghl_bad[] = "$slug: widget id"; }
	if ( 1 !== substr_count( $b, 'https://widgets.leadconnectorhq.com/loader.js' ) )            { $ghl_bad[] = "$slug: loader"; }
	if ( 1 !== substr_count( $b, 'https://widgets.leadconnectorhq.com/chat-widget/loader.js' ) ) { $ghl_bad[] = "$slug: resources"; }
}
empty( $ghl_bad )
	? ok( '24. GHL widget ID, loader and resources URL are unchanged on all six pages' )
	: bad( '24. ' . implode( ', ', $ghl_bad ) );

echo "\n== RESULT ==\n  pass: $pass   fail: $fail   skip: $skip\n";
exit( $fail > 0 ? 1 : 0 );
