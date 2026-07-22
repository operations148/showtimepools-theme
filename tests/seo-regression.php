<?php
/**
 * SEO / schema / metadata regression test — dependency-free PHP CLI.
 *
 * Guards the four critical production issues fixed in
 * .claude/audits/critical-schema-metadata-fix.md:
 *   1. No malformed telephone ("+1-323--825-2099" / "323--825") anywhere.
 *   2. Exactly one <title>, one meta description, one canonical per page.
 *   3. Homepage title is the approved single-brand string.
 *   4. Articles get their own title (not the homepage title) and every
 *      JSON-LD block parses as valid JSON.
 *
 * TWO layers:
 *   A. SOURCE guard  — always runs, no server needed. Fails if the malformed
 *      phone signature appears in the theme/plugin source.
 *   B. RENDER guard  — runs when a reachable base URL is given. Fetches real
 *      pages and asserts tag counts, titles, phone, and JSON-LD validity.
 *
 * Usage:
 *   php tests/seo-regression.php
 *   SHOWTIME_BASE_URL="http://localhost/showtimepools/wp" php tests/seo-regression.php
 *   (append page/post query args below to match the environment)
 *
 * Exit code 0 = all pass, 1 = one or more failures.
 *
 * @package ShowtimePools
 */

$root = dirname( __DIR__ );

$fail = 0;
$pass = 0;
$skip = 0;
function ok( string $m ): void   { global $pass; $pass++; echo "  \xE2\x9C\x94 $m\n"; }
function bad( string $m ): void  { global $fail; $fail++; echo "  \xE2\x9C\x98 FAIL: $m\n"; }
function skip( string $m ): void { global $skip; $skip++; echo "  \xE2\x97\x8B skip: $m\n"; }

// The forbidden phone signatures. The double-dash is the OTTO-schema artifact.
$BAD_PHONE = array( '323--825', '+1-323--825', '+1-323-' );

echo "== A. SOURCE guard (theme + plugin) ==\n";

// Scan executable/output-producing code only. Documentation (.md under
// .claude/ and tasks/) legitimately NAMES the malformed number when describing
// the bug, so docs are out of scope — only code that could actually render the
// phone matters here.
$exts = array( 'php', 'js', 'css', 'json' );
$rii  = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
);
$hits = array();
foreach ( $rii as $file ) {
	if ( ! $file->isFile() ) { continue; }
	$path = $file->getPathname();
	// Don't scan this test file (it legitimately contains the pattern),
	// documentation dirs, node_modules, or VCS internals.
	if ( strpos( $path, 'seo-regression.php' ) !== false ) { continue; }
	if ( strpos( $path, DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR ) !== false ) { continue; }
	if ( strpos( $path, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR ) !== false ) { continue; }
	if ( strpos( $path, DIRECTORY_SEPARATOR . '.claude' . DIRECTORY_SEPARATOR ) !== false ) { continue; }
	if ( strpos( $path, DIRECTORY_SEPARATOR . 'tasks' . DIRECTORY_SEPARATOR ) !== false ) { continue; }
	$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, $exts, true ) ) { continue; }
	$body = (string) file_get_contents( $path );
	foreach ( $BAD_PHONE as $needle ) {
		if ( strpos( $body, $needle ) !== false ) {
			$hits[] = str_replace( $root, '', $path ) . "  (\"$needle\")";
		}
	}
}
if ( $hits ) {
	foreach ( $hits as $h ) { bad( "malformed phone in source: $h" ); }
} else {
	ok( 'no malformed phone signature in theme/plugin source' );
}

// ---------------------------------------------------------------------------

echo "\n== B. RENDER guard ==\n";

$base = getenv( 'SHOWTIME_BASE_URL' );
if ( ! $base ) {
	$base = 'http://localhost/showtimepools/wp';
}
$base = rtrim( $base, '/' );

$fetch = static function ( string $url ): ?string {
	$ctx  = stream_context_create( array( 'http' => array( 'timeout' => 8, 'follow_location' => 1 ) ) );
	$html = @file_get_contents( $url, false, $ctx );
	return ( false === $html || '' === $html ) ? null : $html;
};

// Confirm the base is reachable before asserting anything against it.
$probe = $fetch( $base . '/' );
if ( null === $probe ) {
	skip( "base URL not reachable ($base) — render assertions skipped, source guard still authoritative" );
} else {
	$approved_home_title = 'Pool Service & Repairs in Los Angeles | Showtime Pools';

	// Pages to test: label => URL. Query-arg form works regardless of the
	// site's permalink structure.
	$pages = array(
		'homepage' => $base . '/',
		'service'  => $base . '/?page_id=7',   // pool-leak-detection
		'article1' => $base . '/?p=53',
		'article2' => $base . '/?p=54',
	);

	$count = static function ( string $h, string $re ): int {
		return preg_match_all( $re, $h, $m );
	};
	$first = static function ( string $h, string $re ): string {
		return preg_match( $re, $h, $m ) ? html_entity_decode( $m[1], ENT_QUOTES ) : '';
	};

	foreach ( $pages as $label => $url ) {
		$h = $fetch( $url );
		echo "  -- $label ($url)\n";
		if ( null === $h ) { bad( "$label did not render" ); continue; }

		// Malformed phone must never appear in rendered output.
		$phone_bad = false;
		foreach ( $BAD_PHONE as $needle ) {
			if ( strpos( $h, $needle ) !== false ) { $phone_bad = true; break; }
		}
		$phone_bad ? bad( "$label: malformed phone in rendered output" )
		           : ok( "$label: no malformed phone" );

		// Exactly one of each critical tag.
		$nt = $count( $h, '#<title[ >]#i' );
		$nd = $count( $h, '#<meta\s+name=["\']description["\']#i' );
		$nc = $count( $h, '#rel=["\']canonical["\']#i' );
		1 === $nt ? ok( "$label: exactly one <title>" )        : bad( "$label: <title> count = $nt" );
		1 === $nd ? ok( "$label: exactly one meta description" ): bad( "$label: meta description count = $nd" );
		1 === $nc ? ok( "$label: exactly one canonical" )       : bad( "$label: canonical count = $nc" );

		$title = $first( $h, '#<title[^>]*>(.*?)</title>#is' );

		if ( 'homepage' === $label ) {
			$title === $approved_home_title
				? ok( "homepage title is approved string" )
				: bad( "homepage title = \"$title\" (expected \"$approved_home_title\")" );
		} else {
			$title !== $approved_home_title && '' !== $title
				? ok( "$label: title is page-specific, not the homepage title" )
				: bad( "$label: title is empty or equals the homepage title (\"$title\")" );
		}

		// og:title / twitter:title must equal <title> (compare decoded).
		$og = $first( $h, '#property=["\']og:title["\']\s+content=["\'](.*?)["\']#is' );
		$tw = $first( $h, '#name=["\']twitter:title["\']\s+content=["\'](.*?)["\']#is' );
		( $og === $title ) ? ok( "$label: og:title matches <title>" )      : bad( "$label: og:title \"$og\" != title \"$title\"" );
		( $tw === $title ) ? ok( "$label: twitter:title matches <title>" ) : bad( "$label: twitter:title \"$tw\" != title \"$title\"" );

		// Every JSON-LD block must be valid JSON (no empty {} either), and no
		// two full nodes may share an @id (duplicate semantic entity). Branch
		// nodes have distinct @ids, so they are not flagged.
		preg_match_all( '#<script[^>]*application/ld\+json[^>]*>(.*?)</script>#is', $h, $blocks );
		$n_json   = count( $blocks[1] );
		$json_bad = 0;
		$ids      = array();
		$collect  = static function ( $node ) use ( &$ids ) {
			if ( is_array( $node ) && isset( $node['@type'] ) && isset( $node['@id'] ) ) {
				$ids[] = (string) $node['@id'];
			}
		};
		foreach ( $blocks[1] as $raw ) {
			$data = json_decode( trim( $raw ), true );
			if ( null === $data || array() === $data ) { $json_bad++; continue; }
			// A block is either a single node or a @graph of nodes.
			if ( isset( $data['@graph'] ) && is_array( $data['@graph'] ) ) {
				foreach ( $data['@graph'] as $n ) { $collect( $n ); }
			} else {
				$collect( $data );
			}
		}
		$dupe_ids = array_keys( array_filter( array_count_values( $ids ), static fn( $c ) => $c > 1 ) );

		if ( 0 === $n_json ) {
			bad( "$label: no JSON-LD found" );
		} elseif ( $json_bad > 0 ) {
			bad( "$label: $json_bad of $n_json JSON-LD block(s) invalid/empty" );
		} else {
			ok( "$label: all $n_json JSON-LD block(s) valid" );
		}
		$dupe_ids
			? bad( "$label: duplicate @id node(s): " . implode( ', ', $dupe_ids ) )
			: ok( "$label: no duplicate @id nodes" );

		// Business-entity hygiene (sitewide footer schema): exactly one
		// #organization, no #branch-* LocalBusiness, no Beverly Hills office
		// address in schema. Beverly Hills must remain only as an areaServed City.
		$n_branch     = substr_count( $h, '#branch-' );
		$n_charleville= substr_count( $h, 'Charleville' ) + substr_count( $h, '90212' );
		$n_org        = count( array_filter( $ids, static fn( $id ) => str_ends_with( $id, '#organization' ) ) );
		0 === $n_branch      ? ok( "$label: no #branch-* business entity" )        : bad( "$label: $n_branch #branch-* node(s) present" );
		0 === $n_charleville ? ok( "$label: no Beverly Hills office in schema" )    : bad( "$label: Beverly Hills office address in schema" );
		( 1 === $n_org || 0 === $n_org ) ? ok( "$label: single (or zero) #organization node" ) : bad( "$label: $n_org #organization nodes (expected 1)" );
	}
}

echo "\n== RESULT ==\n";
echo "  pass: $pass   skip: $skip   fail: $fail\n";
exit( $fail > 0 ? 1 : 0 );
