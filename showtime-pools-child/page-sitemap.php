<?php
/**
 * Template Name: HTML Sitemap
 *
 * Human + crawler HTML sitemap at /sitemap/. Renders a grouped, dynamically
 * queried list of canonical, published, indexable URLs — Main Pages, Pool
 * Services, Service Areas, Projects, and Pool Guides & Articles.
 *
 * This is a VISIBLE HTML index only. It does NOT emit XML and never competes
 * with the native /wp-sitemap.xml. Excluded from the list: noindex utility
 * templates (book/quote/shop/affiliate), legacy-duplicate slugs (terms-2),
 * drafts, attachment pages, and this sitemap page itself — driven off the same
 * noindex helpers as the robots meta and XML sitemap so the three agree.
 *
 * Assign to a page with slug "sitemap" (see the deployment report for the
 * exact WP-CLI command). Uses the existing interior-page design system.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$noindex_templates = function_exists( 'showtime_noindex_page_templates' ) ? showtime_noindex_page_templates() : array();
$noindex_slugs     = function_exists( 'showtime_noindex_page_slugs' ) ? showtime_noindex_page_slugs() : array();
$this_id           = get_the_ID();

/**
 * Render one group of links. $links is an array of ['title'=>, 'url'=>].
 */
$render_group = static function ( string $heading, array $links ): void {
	$links = array_values( array_filter( $links, static fn( $l ) => ! empty( $l['url'] ) && ! empty( $l['title'] ) ) );
	if ( empty( $links ) ) {
		return;
	}
	?>
	<section class="sitemap-group">
		<h2 class="sitemap-group__title"><?php echo esc_html( $heading ); ?></h2>
		<ul class="sitemap-group__list">
			<?php foreach ( $links as $l ) : ?>
				<li><a href="<?php echo esc_url( $l['url'] ); ?>"><?php echo esc_html( $l['title'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
};

// ── Main Pages: published top-level pages, minus noindex + this page ────────
$main_pages = array( array( 'title' => __( 'Home', 'showtime-pools' ), 'url' => home_url( '/' ) ) );
$pages = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'post_parent'    => 0,
		'posts_per_page' => -1,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	)
);
foreach ( $pages as $p ) {
	if ( (int) $p->ID === (int) $this_id ) {
		continue; // don't list the sitemap page itself
	}
	if ( in_array( $p->post_name, (array) $noindex_slugs, true ) ) {
		continue; // legacy duplicate / redirected
	}
	$tpl = (string) get_post_meta( $p->ID, '_wp_page_template', true );
	if ( $tpl && in_array( $tpl, (array) $noindex_templates, true ) ) {
		continue; // noindex utility template
	}
	$main_pages[] = array( 'title' => get_the_title( $p->ID ), 'url' => get_permalink( $p->ID ) );
}

// ── Pool Services + Service Areas: from the registries (single source) ──────
$service_links = array();
if ( class_exists( '\\Showtime\\Services' ) ) {
	foreach ( \Showtime\Services::all() as $svc ) {
		$slug = (string) ( $svc['slug'] ?? '' );
		if ( '' === $slug ) { continue; }
		$service_links[] = array( 'title' => (string) ( $svc['title'] ?? $slug ), 'url' => home_url( '/services/' . $slug . '/' ) );
	}
}
$area_links = array();
if ( class_exists( '\\Showtime\\Areas' ) ) {
	foreach ( \Showtime\Areas::all() as $area ) {
		$slug = (string) ( $area['slug'] ?? '' );
		if ( '' === $slug ) { continue; }
		$area_links[] = array( 'title' => (string) ( $area['name'] ?? $slug ), 'url' => home_url( '/service-areas/' . $slug . '/' ) );
	}
}

// ── Projects (CPT) + Articles (posts): published only ───────────────────────
$project_links = array();
if ( post_type_exists( 'project' ) ) {
	// Legacy seed rows and "Coming soon" placeholders are excluded for the same
	// reason as the XML sitemap: neither carries verified project content.
	$unmanaged_project_ids = function_exists( 'showtime_project_ids_hidden_from_discovery' ) ? showtime_project_ids_hidden_from_discovery() : array();
	foreach ( get_posts( array( 'post_type' => 'project', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'menu_order date', 'order' => 'ASC', 'no_found_rows' => true, 'post__not_in' => $unmanaged_project_ids ) ) as $pr ) {
		$project_links[] = array( 'title' => get_the_title( $pr->ID ), 'url' => get_permalink( $pr->ID ) );
	}
}
$article_links = array();
foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) ) as $post_item ) {
	$article_links[] = array( 'title' => get_the_title( $post_item->ID ), 'url' => get_permalink( $post_item->ID ) );
}
?>
<main id="primary" class="site-main interior-page">
	<section class="int-hero int-hero--brand" data-reveal>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'Sitemap', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php esc_html_e( 'Find anything', 'showtime-pools' ); ?></span>
				<h1 class="int-hero__title balance"><?php esc_html_e( 'Sitemap', 'showtime-pools' ); ?></h1>
				<p class="int-hero__lead"><?php esc_html_e( 'Every public page on the Showtime Pools site, grouped for people and search engines.', 'showtime-pools' ); ?></p>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container sitemap">
			<?php
			$render_group( __( 'Main Pages', 'showtime-pools' ), $main_pages );
			$render_group( __( 'Pool Services', 'showtime-pools' ), $service_links );
			$render_group( __( 'Service Areas', 'showtime-pools' ), $area_links );
			$render_group( __( 'Projects', 'showtime-pools' ), $project_links );
			$render_group( __( 'Pool Guides & Articles', 'showtime-pools' ), $article_links );
			?>
		</div>
	</section>
</main>
<?php
get_footer();
