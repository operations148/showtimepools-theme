<?php
/**
 * Imagery — named slot → CDN URL map for stock photography.
 *
 * Centralizes every external image URL on the site so swapping stock for
 * Steve's real photography (once the shoot lands) is a one-file change.
 *
 * Defaults pull from Unsplash's public CDN with auto-format + fit=crop +
 * width-on-demand parameters. Each slot is filterable via
 * `showtime/image/{slot}` so the user can override per-page in code or
 * by uploading to the WP Media Library and pointing the filter to the
 * attachment URL.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build an Unsplash CDN URL from a photo ID with width + quality.
 */
function showtime_unsplash_url( string $photo_id, int $w = 1600, int $h = 0, int $q = 78 ): string {
	$base = "https://images.unsplash.com/photo-{$photo_id}";
	$params = array(
		'ixlib' => 'rb-4.0.3',
		'auto'  => 'format',
		'fit'   => 'crop',
		'w'     => $w,
		'q'     => $q,
	);
	if ( $h > 0 ) {
		$params['h']    = $h;
		$params['fit']  = 'crop';
	}
	return $base . '?' . http_build_query( $params );
}

/**
 * Build a Picsum fallback URL with deterministic seed.
 */
function showtime_picsum_url( string $seed, int $w = 1600, int $h = 900 ): string {
	return "https://picsum.photos/seed/{$seed}/{$w}/{$h}";
}

/**
 * Resolve an image URL by slot.
 *
 * @param string $slot Logical name (hero, project_1, area_sherman_oaks, ...)
 * @param int    $w    Width in CSS pixels.
 * @param int    $h    Height (0 = let Unsplash decide proportionally).
 */
function showtime_image( string $slot, int $w = 1600, int $h = 0 ): string {

	// ── Priority 0: Native WP option (no ACF Pro required) ───────────────────
	// WP Admin → Showtime Pools → Site Images → upload any photo.
	// Saves as attachment ID (int) or URL (string) in wp_options.
	// Always available, even without ACF Pro on the server.
	$opt_key = 'showtime_img_' . str_replace( '-', '_', $slot );
	$native  = get_option( $opt_key, '' );
	if ( '' !== (string) $native ) {
		$native_url = is_numeric( $native )
			? wp_get_attachment_url( (int) $native )
			: (string) $native;
		if ( $native_url ) {
			return (string) apply_filters( 'showtime/image/' . $slot, $native_url, $slot, $w, $h );
		}
	}

	// ── Priority 1: ACF Options override ─────────────────────────────────────
	// WP Admin → Site Content → Images → upload any image to override the
	// bundled/Unsplash fallback. Field name = "img_" + slot with hyphens → underscores.
	// This is the single entry-point for all dynamic image management.
	if ( function_exists( 'get_field' ) ) {
		$field_key = 'img_' . str_replace( '-', '_', $slot );
		$acf_img   = get_field( $field_key, 'option' );
		if ( ! empty( $acf_img ) ) {
			$acf_url = is_array( $acf_img )
				? (string) ( $acf_img['sizes']['large'] ?? $acf_img['sizes']['medium_large'] ?? $acf_img['url'] ?? '' )
				: (string) $acf_img;
			if ( '' !== $acf_url ) {
				return (string) apply_filters( 'showtime/image/' . $slot, $acf_url, $slot, $w, $h );
			}
		}
	}

	// ── Priority 2: Curated Unsplash IDs (original fallback chain) ───────────
	// Curated Unsplash IDs. These are popular pool/water/luxury-home photos.
	// If any 404, the CSS background-color fallback keeps the page readable.
	$photos = array(

		// Hero + lifestyle
		'hero'             => '1572331165267-854da2b10ccc', // modern infinity pool aerial
		'lifestyle_main'   => '1582610116397-edb318620f90', // luxury backyard pool
		'lifestyle_1'      => '1571902943202-507ec2618e8f', // pool tile + water
		'lifestyle_2'      => '1576013551627-0cc20b96c2a7', // pool surface
		'lifestyle_3'      => '1564013799919-ab600027ffc6', // palm + sunset by pool
		'lifestyle_4'      => '1540541338287-41700207dee6', // water light play

		// Featured projects on homepage (3)
		'project_1'        => '1499856871958-5b9627545d1a', // modern home pool
		'project_2'        => '1567113463300-102a7eb3cb26', // pool corner
		'project_3'        => '1568605114967-8130f3a36994', // modern pool patio

		// Projects gallery page (additional 6)
		'project_4'        => '1601933470928-c4bdc18d0d34', // pool tile pattern
		'project_5'        => '1571939228382-b2f2b585ce15', // pool ladder
		'project_6'        => '1597348989645-46b190ce4918', // pool detail
		'project_7'        => '1604975701397-6365d3aff5a8', // pool side
		'project_8'        => '1583608205776-bfb35f0d9f83', // pool with deck
		'project_9'        => '1573521193826-58c7dc2e13e3', // pool from above

		// Service area cards — pool-themed photos that align with each
		// neighborhood's character. Each is also overridable via a local
		// file at assets/img/area-{slug}.{jpg,webp,png} (see resolver below).
		'area_sherman-oaks'   => '1499856871958-5b9627545d1a', // modern California home with pool
		'area_encino'         => '1582610116397-edb318620f90', // luxury backyard pool
		'area_beverly-hills'  => '1568605114967-8130f3a36994', // modern pool patio (high-end)
		'area_studio-city'    => '1564013799919-ab600027ffc6', // palm + sunset by pool (hillside feel)
		'area_tarzana'        => '1564013799919-ab600027ffc6', // house + pool (verified). Bundled area_tarzana.jpg overrides.
		'area_woodland-hills' => '1576013551627-0cc20b96c2a7', // pool surface + light
		'area_west-hollywood' => '1572331165267-854da2b10ccc', // compact courtyard pool at dusk
		'area_bel-air'        => '1613490493576-7fde63acd811', // estate villa pool, hillside

		// Backdrops + accents
		'inspections_bg'   => '1565609890717-4b39e8b7cf9c',    // equipment
		'about_hero'       => '1581094271901-8022df4466f9',    // tools / craftsmanship
		'process_bg'       => '1576013551627-0cc20b96c2a7',    // pool surface

		// Founder portrait — defaults to a craftsman-on-jobsite shot. Drop
		// a real Steve Adams photo at /assets/img/founder.jpg (or .webp /
		// .png) and the local file resolver below will pick it up.
		'founder'          => '1622253692010-333f2da6031d',     // tradesman portrait
	);

	// Local-file override: if the theme ships a real photo at
	// /assets/img/{slot}.{ext}, prefer it over the Unsplash CDN. Lets the
	// user drop authentic shots (founder portrait, neighborhood hero,
	// project photos) without touching WP admin OR PHP. Drop the file,
	// hard-refresh, done.
	$local_slots = array( 'founder', 'about_hero', 'hero', 'lifestyle_main', 'lifestyle_1', 'lifestyle_2', 'lifestyle_3', 'lifestyle_4', 'inspections_bg' );
	// Allow every area_*, project_*, service_*, blog_*, team_* slot too.
	if ( str_starts_with( $slot, 'area_' )
		|| str_starts_with( $slot, 'project_' )
		|| str_starts_with( $slot, 'service_' )
		|| str_starts_with( $slot, 'blog_' )
		|| str_starts_with( $slot, 'team_' ) ) {
		$local_slots[] = $slot;
	}
	if ( in_array( $slot, $local_slots, true ) ) {
		// Prefer modern formats first; .webp wins where the browser supports it.
		foreach ( array( 'webp', 'avif', 'jpg', 'jpeg', 'png' ) as $ext ) {
			$rel = "assets/img/{$slot}.{$ext}";
			if ( file_exists( SHOWTIME_CHILD_DIR . '/' . $rel ) ) {
				return apply_filters( 'showtime/image/' . $slot, SHOWTIME_CHILD_URI . '/' . $rel, $slot, $w, $h );
			}
		}
	}

	$photo_id = $photos[ $slot ] ?? '';

	if ( '' !== $photo_id ) {
		$url = showtime_unsplash_url( $photo_id, $w, $h );
	} else {
		$url = showtime_picsum_url( 'showtime-' . preg_replace( '/[^a-z0-9-]/', '-', strtolower( $slot ) ), $w, $h ?: round( $w * 0.5625 ) );
	}

	/**
	 * Filter image URL by slot. Use to swap stock for Steve's real
	 * photography once uploaded.
	 *
	 * @param string $url
	 * @param string $slot
	 * @param int    $w
	 * @param int    $h
	 */
	return (string) apply_filters( 'showtime/image/' . $slot, $url, $slot, $w, $h );
}

/**
 * Front-page hero URLs, desktop + mobile pair. Single source of truth shared
 * by the hero template (template-parts/home/section-01-hero.php) and the LCP
 * preload hook (inc/performance.php) so the preloaded URL always matches the
 * rendered one.
 *
 * @return array{desktop:string,mobile:string}
 */
function showtime_front_hero_urls(): array {
	$opt    = function_exists( 'get_field' ) ? 'option' : false;
	$pc_img = $opt ? get_field( 'hero_image', $opt ) : null;

	if ( is_array( $pc_img ) && ! empty( $pc_img['url'] ) ) {
		$desktop = (string) ( $pc_img['sizes']['large'] ?? $pc_img['url'] );
		$mobile  = (string) ( $pc_img['sizes']['medium_large'] ?? $desktop );
	} else {
		$desktop = showtime_image( 'hero', 1920 );
		$mobile  = showtime_image( 'hero', 1200 );
	}

	return array(
		'desktop' => $desktop,
		'mobile'  => $mobile,
	);
}

/**
 * Resolve the image slot for a post: explicit `_showtime_image_slot` meta,
 * else the primary category's bundled blog photo, else the generic default.
 */
function showtime_post_hero_slot( int $pid ): string {
	$slot = (string) get_post_meta( $pid, '_showtime_image_slot', true );
	if ( '' !== $slot ) {
		return $slot;
	}
	$category_slot_map = array(
		'pool-trends'      => 'blog_trends',
		'maintenance-tips' => 'blog_tips',
		'equipment-guides' => 'blog_equipment',
	);
	$cats = get_the_category( $pid );
	return isset( $cats[0], $category_slot_map[ $cats[0]->slug ] ) ? $category_slot_map[ $cats[0]->slug ] : 'blog_default';
}

/**
 * Post hero URL: featured image when set, slot-based stock photo otherwise.
 * Shared by single.php and the LCP preload hook in inc/performance.php.
 *
 * @param int    $pid        Post ID.
 * @param int    $w          Slot-image width when no thumbnail exists.
 * @param string $thumb_size Thumbnail size when one exists.
 */
function showtime_post_hero_url( int $pid, int $w = 1920, string $thumb_size = 'full' ): string {
	if ( has_post_thumbnail( $pid ) ) {
		return (string) get_the_post_thumbnail_url( $pid, $thumb_size );
	}
	return showtime_image( showtime_post_hero_slot( $pid ), $w );
}

/**
 * Convenience: render an `<img>` tag with sensible defaults.
 */
function showtime_image_tag( string $slot, array $attrs = array() ): string {
	$defaults = array(
		'src'      => showtime_image( $slot, (int) ( $attrs['w'] ?? 1600 ) ),
		'alt'      => '',
		'loading'  => 'lazy',
		'decoding' => 'async',
	);
	unset( $attrs['w'], $attrs['h'] );
	$attrs = array_merge( $defaults, $attrs );

	$html = '<img';
	foreach ( $attrs as $k => $v ) {
		if ( '' === $v && 'alt' !== $k ) { continue; }
		$html .= ' ' . esc_attr( $k ) . '="' . esc_attr( (string) $v ) . '"';
	}
	$html .= '>';
	return $html;
}
