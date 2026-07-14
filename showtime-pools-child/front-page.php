<?php
/**
 * Homepage orchestrator. Loads the 11-section sequence per the build spec.
 *
 * Each section is a separate template-part so we can reorder, A/B test, or
 * rip out individual sections without touching the orchestrator. Sections
 * that depend on CPTs not yet populated render a graceful fallback.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$sections = apply_filters(
	'showtime/home_sections',
	array(
		'01-hero',
		'about-split',
		'interactive-pool',
		'03-services',
		'05-featured-projects',
		'why-us',
		'06-process',
		'08-reviews',
		'09-service-areas',
		'10-faq',
		'map',
	)
);

?>
<main id="primary" class="site-main home">
	<?php
	foreach ( $sections as $slug ) {
		get_template_part( 'template-parts/home/section', $slug );
	}
	?>
</main>
<?php
get_footer();
