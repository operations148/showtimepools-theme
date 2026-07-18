<?php
/**
 * Template Name: Service
 *
 * Single template for all 8 service pages. Resolves the service slug from
 * post meta (`_showtime_service_slug`) or post slug, hydrates a context
 * array from the registry merged over ACF overrides, then renders the
 * section sequence. Sections are filterable so we can A/B test or reorder
 * without touching this file.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$service_slug = (string) get_post_meta( get_the_ID(), '_showtime_service_slug', true );
if ( '' === $service_slug ) {
	$service_slug = get_post_field( 'post_name', get_the_ID() );
}

$registry = class_exists( '\\Showtime\\Services' )
	? \Showtime\Services::get( $service_slug )
	: null;

$acf = function_exists( 'get_field' );

$ctx = array(
	'slug'       => $service_slug,
	'title'      => get_the_title(),
	'seo_h1'    => '',
	'seo_intro' => '',
	'summary'    => '',
	'icon'       => $registry['icon']         ?? 'equipment',
	'price'      => '',
	'price_was'  => '',
	'price_badge'=> '',
	'price_items'=> array(),
	'turnaround' => '',
	'disclaimer' => '',
	'includes'   => array(),
	'faqs'       => array(),
	'related'    => class_exists( '\\Showtime\\Services' ) ? \Showtime\Services::related( $service_slug, 3 ) : array(),
	'related_areas' => array(),
	// AEO/GEO section data (registry-driven; each section hides itself if empty).
	'glance'          => array(),
	'cost'            => array(),
	'decision'        => array(),
	'problems'        => array(),
	'problems_heading'=> '',
	'real_project'    => array(),
	'area_anchor'     => '',
	'areas_relevant'  => array(),
);

// Layer registry defaults first.
if ( $registry ) {
	$ctx['seo_h1']     = (string) ( $registry['seo_h1'] ?? '' );
	$ctx['seo_intro']  = (string) ( $registry['seo_intro'] ?? '' );
	$ctx['summary']    = (string) ( $registry['summary'] ?? '' );
	$ctx['price']       = (string) ( $registry['default_price'] ?? '' );
	$ctx['price_was']   = (string) ( $registry['default_price_was'] ?? '' );
	$ctx['price_badge'] = (string) ( $registry['default_price_badge'] ?? '' );
	$ctx['price_items'] = (array)  ( $registry['default_price_items'] ?? array() );
	$ctx['turnaround']  = (string) ( $registry['default_turnaround'] ?? '' );
	$ctx['includes']   = (array)  ( $registry['default_includes'] ?? array() );
	$ctx['faqs']       = (array)  ( $registry['default_faqs'] ?? array() );

	// AEO/GEO registry fields.
	$ctx['glance']           = (array)  ( $registry['aeo_glance'] ?? array() );
	$ctx['cost']             = (array)  ( $registry['aeo_cost'] ?? array() );
	$ctx['decision']         = (array)  ( $registry['aeo_decision'] ?? array() );
	$ctx['problems']         = (array)  ( $registry['aeo_problems'] ?? array() );
	$ctx['problems_heading'] = (string) ( $registry['aeo_problems_heading'] ?? '' );
	$ctx['real_project']     = (array)  ( $registry['aeo_real_project'] ?? array() );
	$ctx['area_anchor']      = (string) ( $registry['aeo_area_anchor'] ?? '' );

	// Resolve the curated relevant-area slugs to [slug,name] pairs.
	$aeo_area_slugs = (array) ( $registry['aeo_areas'] ?? array() );
	if ( $aeo_area_slugs && class_exists( '\\Showtime\\Areas' ) ) {
		foreach ( $aeo_area_slugs as $aslug ) {
			$area = \Showtime\Areas::get( (string) $aslug );
			if ( $area && ! empty( $area['name'] ) ) {
				$ctx['areas_relevant'][] = array( 'slug' => (string) $aslug, 'name' => (string) $area['name'] );
			}
		}
	}
}

// Layer ACF overrides on top (only when populated).
if ( $acf ) {
	$summary    = (string) get_field( 'hero_summary' );
	$price      = (string) get_field( 'price_starting_at' );
	$turnaround = (string) get_field( 'turnaround' );
	$disclaimer = (string) get_field( 'pricing_disclaimer' );
	$includes   = (array)  ( get_field( 'includes' ) ?: array() );
	$faqs_acf   = (array)  ( get_field( 'faqs' ) ?: array() );
	$rel_areas  = (array)  ( get_field( 'related_areas' ) ?: array() );

	if ( $summary    !== '' ) { $ctx['summary']    = $summary; }
	if ( $price      !== '' ) { $ctx['price']      = $price; }
	if ( $turnaround !== '' ) { $ctx['turnaround'] = $turnaround; }
	if ( $disclaimer !== '' ) { $ctx['disclaimer'] = $disclaimer; }

	if ( $includes ) {
		$ctx['includes'] = array_values(
			array_filter(
				array_map(
					static fn( $row ) => is_array( $row ) ? (string) ( $row['text'] ?? '' ) : (string) $row,
					$includes
				)
			)
		);
	}

	if ( $faqs_acf ) {
		$ctx['faqs'] = array_values(
			array_filter(
				array_map(
					static fn( $row ) => is_array( $row ) && ! empty( $row['q'] )
						? array( 'q' => (string) $row['q'], 'a' => (string) ( $row['a'] ?? '' ) )
						: null,
					$faqs_acf
				)
			)
		);
	}

	if ( $rel_areas ) {
		$ctx['related_areas'] = array_values( array_filter( $rel_areas ) );
	}
}

// Make the context available to template-parts via a global. set_query_var
// doesn't survive get_template_part on every WP version reliably for arrays,
// so we use a request-scoped global guarded by a unique name.
$GLOBALS['showtime_service_ctx'] = $ctx;

// 'cta' intentionally NOT in this list. The sitewide footer CTA tier
// (template-parts/footer/footer-cta.php) is the page closer for every
// template — adding 'cta' here would render two black CTA blocks back
// to back. Re-add via the showtime/service_sections filter only if a
// service variant truly needs a service-specific closer.
$sections = apply_filters(
	'showtime/service_sections',
	array(
		'hero',
		'includes',
		'process',
		// AEO/GEO block — placed after the main service explanation and before
		// the pricing/FAQ/CTA close. Each part self-hides when its registry
		// data is empty, so partially-filled services show no empty sections.
		'glance',
		'cost',
		'decision',
		'problems',
		'projects',       // real before/after (replaces the old 'before-after')
		'service-areas',
		// 'pricing' (the "Investment" card) retired: the starting price now
		// shows as a fact in the glance block, the itemized breakdown lives in
		// the cost guide, and the quote CTA moved into the glance section.
		'faq',
		'related',
	),
	$ctx
);
?>
<main id="primary" class="site-main service-page">
	<?php
	foreach ( $sections as $slug ) {
		get_template_part( 'template-parts/service/section', $slug );
	}
	get_template_part( 'template-parts/service/schema' );
	?>
</main>
<?php
unset( $GLOBALS['showtime_service_ctx'] );
get_footer();
