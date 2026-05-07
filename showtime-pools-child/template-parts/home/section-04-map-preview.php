<?php
/**
 * Map preview — stylized SF Valley silhouette with neighborhood pins.
 * Replaced by the full Mapbox map in Phase 2A; this version is rendered
 * with inline SVG so it works without the Mapbox token and ships clean
 * CLS/LCP numbers.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="map-preview section section--cream" data-reveal>
	<div class="container">
		<div class="map-preview__grid">

			<div class="map-preview__copy stack stack--md">
				<span class="eyebrow"><?php esc_html_e( '02 / The route', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php esc_html_e( 'We work a tight LA route on purpose.', 'showtime-pools' ); ?></h2>
				<p>
					<?php esc_html_e( 'Six neighborhoods on regular weekly routes. The rest of LA on construction, remodel, and inspection-only basis. Tight geography is how we keep the same tech on your pool every visit.', 'showtime-pools' ); ?>
				</p>
				<ul class="map-preview__chips" role="list">
					<li><span class="map-preview__pin map-preview__pin--blue"></span><?php esc_html_e( 'Active jobs', 'showtime-pools' ); ?></li>
					<li><span class="map-preview__pin map-preview__pin--green"></span><?php esc_html_e( 'Completed projects', 'showtime-pools' ); ?></li>
					<li><span class="map-preview__pin map-preview__pin--orange"></span><?php esc_html_e( 'Office', 'showtime-pools' ); ?></li>
				</ul>
				<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/projects/' ) ); ?>">
					<?php esc_html_e( 'Open the full map', 'showtime-pools' ); ?>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
				</a>
			</div>

			<div class="map-preview__visual" aria-hidden="true">
				<svg viewBox="0 0 600 420" fill="none" xmlns="http://www.w3.org/2000/svg" class="map-preview__svg">
					<defs>
						<pattern id="mapGrid" width="20" height="20" patternUnits="userSpaceOnUse">
							<path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(15,23,36,0.06)" stroke-width="1"/>
						</pattern>
					</defs>
					<rect width="600" height="420" fill="#FBF8F1"/>
					<rect width="600" height="420" fill="url(#mapGrid)"/>

					<path d="M40 240 Q120 180 200 220 Q280 250 360 200 Q440 160 520 200 Q560 220 560 280 Q540 320 460 320 Q360 340 260 320 Q160 300 80 320 Q40 320 40 280 Z"
						fill="#3F6072" fill-opacity="0.06" stroke="#3F6072" stroke-width="1.5" stroke-opacity="0.4"/>

					<path d="M120 80 Q150 200 180 320 Q200 380 240 410" stroke="#3F6072" stroke-width="1.5" stroke-opacity="0.25" stroke-dasharray="4 6" fill="none"/>
					<path d="M40 200 L560 220" stroke="#3F6072" stroke-width="1.5" stroke-opacity="0.25" stroke-dasharray="4 6" fill="none"/>

					<g font-family="Inter, system-ui" font-size="11" font-weight="600" fill="#3F4B58">
						<text x="180" y="220" text-anchor="middle">Sherman Oaks</text>
						<text x="280" y="200" text-anchor="middle">Studio City</text>
						<text x="100" y="260" text-anchor="middle">Encino</text>
						<text x="380" y="280" text-anchor="middle">Beverly Hills</text>
						<text x="460" y="240" text-anchor="middle">Tarzana</text>
						<text x="500" y="180" text-anchor="middle">Woodland Hills</text>
					</g>

					<g fill="#3F6072">
						<circle cx="180" cy="240" r="8"/><circle cx="180" cy="240" r="3" fill="#fff"/>
						<circle cx="280" cy="220" r="8"/><circle cx="280" cy="220" r="3" fill="#fff"/>
						<circle cx="100" cy="280" r="8"/><circle cx="100" cy="280" r="3" fill="#fff"/>
						<circle cx="160" cy="265" r="6"/><circle cx="160" cy="265" r="2.5" fill="#fff"/>
						<circle cx="200" cy="255" r="6"/><circle cx="200" cy="255" r="2.5" fill="#fff"/>
					</g>
					<g fill="#5C8A9E">
						<circle cx="380" cy="300" r="7"/><circle cx="380" cy="300" r="2.5" fill="#fff"/>
						<circle cx="460" cy="260" r="7"/><circle cx="460" cy="260" r="2.5" fill="#fff"/>
						<circle cx="500" cy="200" r="7"/><circle cx="500" cy="200" r="2.5" fill="#fff"/>
						<circle cx="220" cy="285" r="6"/><circle cx="220" cy="285" r="2.5" fill="#fff"/>
						<circle cx="320" cy="240" r="6"/><circle cx="320" cy="240" r="2.5" fill="#fff"/>
						<circle cx="350" cy="265" r="6"/><circle cx="350" cy="265" r="2.5" fill="#fff"/>
					</g>
					<g>
						<circle cx="190" cy="232" r="11" fill="#C77A0E"/>
						<circle cx="190" cy="232" r="4" fill="#fff"/>
					</g>
				</svg>

				<div class="map-preview__overlay">
					<strong>1,824</strong>
					<span><?php esc_html_e( 'Pools serviced since 2003', 'showtime-pools' ); ?></span>
				</div>
			</div>

		</div>
	</div>
</section>
