<?php
/**
 * Intro video — founder-led brand-authority section directly under the
 * hero. Video on the left (native controls, poster frame), the single-team
 * / twelve-services promise on the right. Ties every service line together
 * under one accountable crew before the page gets into individual sections.
 *
 * All copy is ACF-editable from Site Content → Page Copy → Intro Video
 * (option scope), same pattern as every other homepage section.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

$opt = function_exists( 'get_field' ) ? 'option' : false;

$eyebrow    = $opt ? (string) get_field( 'intro_video_eyebrow', $opt )    : '';
$headline   = $opt ? (string) get_field( 'intro_video_headline', $opt )   : '';
$sub1_title = $opt ? (string) get_field( 'intro_video_sub1_title', $opt ) : '';
$sub1_body  = $opt ? (string) get_field( 'intro_video_sub1_body', $opt )  : '';
$sub2_title = $opt ? (string) get_field( 'intro_video_sub2_title', $opt ) : '';
$sub2_body  = $opt ? (string) get_field( 'intro_video_sub2_body', $opt )  : '';

$eyebrow    = '' !== $eyebrow    ? $eyebrow    : __( 'Meet Showtime Pools', 'showtime-pools' );
$headline   = '' !== $headline   ? $headline   : __( 'One team for every service, start to finish.', 'showtime-pools' );
$sub1_title = '' !== $sub1_title ? $sub1_title : __( 'Founder-Led, Every Job', 'showtime-pools' );
$sub1_body  = '' !== $sub1_body  ? $sub1_body  : __( 'Steve Adams built Showtime Pools on one idea: the person who quotes your job is accountable for it, start to finish. That is still how the crew works today: no subcontractors passing the blame, no call center between you and the technician standing in your yard.', 'showtime-pools' );
$sub2_title = '' !== $sub2_title ? $sub2_title : __( 'Twelve Services. One Accountable Crew.', 'showtime-pools' );
$sub2_body  = '' !== $sub2_body  ? $sub2_body  : __( 'Weekly maintenance, repairs, remodeling, equipment, inspections, automation, new construction, spas, tile and decking, outdoor living, kitchens, and fire and water features: all handled in-house by the same team, supervised by Steve from first fill to last remodel.', 'showtime-pools' );

$services_covered = array(
	__( 'Weekly Maintenance', 'showtime-pools' ),
	__( 'Repairs & Plumbing', 'showtime-pools' ),
	__( 'Remodeling & Resurfacing', 'showtime-pools' ),
	__( 'Equipment', 'showtime-pools' ),
	__( 'Inspections', 'showtime-pools' ),
	__( 'Automation', 'showtime-pools' ),
	__( 'Custom Design & New Construction', 'showtime-pools' ),
	__( 'Spas', 'showtime-pools' ),
	__( 'Tile, Coping, Plaster & Decking', 'showtime-pools' ),
	__( 'Outdoor Living & Hardscape', 'showtime-pools' ),
	__( 'Outdoor Kitchens', 'showtime-pools' ),
	__( 'Fire & Water Features', 'showtime-pools' ),
);

$video_rel = 'assets/img/showtime-intro.mp4';
$has_video = file_exists( SHOWTIME_CHILD_DIR . '/' . $video_rel );
$video_url = SHOWTIME_CHILD_URI . '/' . $video_rel;
$poster    = function_exists( 'showtime_image' ) ? showtime_image( 'showtime-intro-poster', 1280 ) : '';
?>
<section class="intro-video" data-reveal>
	<div class="container">

		<header class="intro-video__header">
			<span class="eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<h2 class="intro-video__title balance"><?php echo esc_html( $headline ); ?></h2>
		</header>

		<div class="intro-video__grid">

			<div class="intro-video__media">
				<?php if ( $has_video ) : ?>
					<video class="intro-video__player" controls preload="metadata" <?php if ( $poster ) : ?>poster="<?php echo esc_url( $poster ); ?>"<?php endif; ?>>
						<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
					</video>
				<?php elseif ( $poster ) : ?>
					<img class="intro-video__fallback" src="<?php echo esc_url( $poster ); ?>" alt="<?php esc_attr_e( 'Showtime Pools', 'showtime-pools' ); ?>" loading="lazy" decoding="async">
				<?php endif; ?>
			</div>

			<div class="intro-video__copy">
				<h3><?php echo esc_html( $sub1_title ); ?></h3>
				<p><?php echo esc_html( $sub1_body ); ?></p>

				<h3><?php echo esc_html( $sub2_title ); ?></h3>
				<p><?php echo esc_html( $sub2_body ); ?></p>

				<ul class="intro-video__services" role="list">
					<?php foreach ( $services_covered as $svc ) : ?>
						<li><?php echo esc_html( $svc ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>

		</div>

	</div>
</section>
