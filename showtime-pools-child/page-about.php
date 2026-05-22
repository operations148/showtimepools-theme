<?php
/**
 * Template Name: About
 *
 * /about/ — Showtime Pools company story, real team, credentials.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$about_hero  = function_exists( 'showtime_image' ) ? showtime_image( 'about_hero', 1600 ) : '';
$about_split = function_exists( 'showtime_image' ) ? showtime_image( 'lifestyle_main', 1200 ) : '';

$person_schema = array(
	'@context'    => 'https://schema.org',
	'@type'       => 'Organization',
	'@id'         => home_url( '/about/#org' ),
	'name'        => 'Showtime Pools',
	'url'         => home_url( '/' ),
	'foundingDate'=> '2003',
	'foundingLocation' => array(
		'@type' => 'Place',
		'name'  => 'Sherman Oaks, Los Angeles, California',
	),
	'founder'     => array(
		'@type'    => 'Person',
		'@id'      => home_url( '/the-founder/#person' ),
		'name'     => 'Steve Adams',
		'jobTitle' => 'Founder & CEO',
	),
	'employee'    => array(
		array( '@type' => 'Person', 'name' => 'Steve Adams',  'jobTitle' => 'Founder & CEO' ),
		array( '@type' => 'Person', 'name' => 'Viktor O',     'jobTitle' => 'Repair Manager' ),
		array( '@type' => 'Person', 'name' => 'Felipe A',     'jobTitle' => 'Pool Service Technician' ),
		array( '@type' => 'Person', 'name' => 'George C',     'jobTitle' => 'Senior Cleaner' ),
	),
	'parentOrganization' => array( '@id' => home_url( '/#localbusiness' ) ),
);
?>
<main id="primary" class="site-main interior-page">

	<section class="int-hero int-hero--brand int-hero--photo" data-reveal>
		<?php if ( $about_hero ) : ?>
			<img class="int-hero__photo" src="<?php echo esc_url( $about_hero ); ?>" alt="" loading="eager" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="int-hero__pattern" aria-hidden="true"></div>
		<div class="container">
			<nav class="breadcrumbs int-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'showtime-pools' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'showtime-pools' ); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span aria-current="page"><?php esc_html_e( 'About', 'showtime-pools' ); ?></span>
			</nav>
			<div class="int-hero__inner">
				<span class="eyebrow eyebrow--invert"><?php esc_html_e( 'About Showtime Pools', 'showtime-pools' ); ?></span>
				<h1 class="int-hero__title balance"><?php echo esc_html( $about_h1 ); ?></h1>
				<p class="int-hero__lead"><?php echo esc_html( $about_hero_lead ); ?></p>
			</div>
		</div>
	</section>

	<?php
	// Section 2 ("Who we are") + Section 3 ("What we believe") read from
	// Site Content → Page Copy → About page tab. PHP defaults below preserve
	// editorial voice if ACF is deactivated or fields are blank.
	$opt = function_exists( 'get_field' ) ? 'option' : false;

	// Priority chain: native wp_options (Showtime Pools → Site Content) → ACF → PHP fallback.
	$_ct = class_exists( '\\Showtime\\Admin\\ContentPage' ) ? '\\Showtime\\Admin\\ContentPage' : null;

	$about_h1        = $_ct ? $_ct::get( 'about_h1' ) : '';
	$about_h1        = '' !== $about_h1 ? $about_h1 : __( 'Complete pool care, start to finish.', 'showtime-pools' );

	$about_hero_lead = $_ct ? $_ct::get( 'about_lead' ) : '';
	$about_hero_lead = '' !== $about_hero_lead ? $about_hero_lead : __( 'Showtime Pools designs, builds, and transforms pools and outdoor spaces that elevate the way you live. Based in Los Angeles, we are the trusted name for homeowners, property managers, and businesses across Sherman Oaks, Encino, Beverly Hills, Studio City, Tarzana, and Woodland Hills.', 'showtime-pools' );

	$about_eyebrow   = $opt ? (string) get_field( 'about_who_we_are_eyebrow', $opt ) : '';
	$about_eyebrow   = '' !== $about_eyebrow ? $about_eyebrow : __( 'Who we are', 'showtime-pools' );

	$about_title     = $_ct ? $_ct::get( 'about_wwa_title' ) : '';
	if ( '' === $about_title ) { $about_title = $opt ? (string) get_field( 'about_who_we_are_title', $opt ) : ''; }
	$about_title     = '' !== $about_title ? $about_title : __( 'Years of hands-on experience. Built on quality, transparency, and reliability.', 'showtime-pools' );

	$about_body      = $_ct ? $_ct::get( 'about_wwa_body' ) : '';
	if ( '' === $about_body ) { $about_body = $opt ? (string) get_field( 'about_who_we_are_body', $opt ) : ''; }

	$values_title    = $_ct ? $_ct::get( 'about_values_title' ) : '';
	if ( '' === $values_title ) { $values_title = $opt ? (string) get_field( 'about_values_intro_title', $opt ) : ''; }
	$values_title    = '' !== $values_title ? $values_title : __( 'Five commitments. Every project, every visit.', 'showtime-pools' );

	$values_intro    = $opt ? (string) get_field( 'about_values_intro_body', $opt ) : '';
	?>
	<section class="int-section" data-reveal>
		<div class="container">
			<div class="about-story">
				<aside class="about-story__photo">
					<div class="about-story__photo-frame">
						<?php if ( $about_split ) : ?>
							<img src="<?php echo esc_url( $about_split ); ?>" alt="" loading="lazy" decoding="async">
						<?php endif; ?>
					</div>
					<figcaption><?php esc_html_e( 'Sherman Oaks shop · Ventura Boulevard', 'showtime-pools' ); ?></figcaption>
				</aside>

				<div class="about-story__copy">
					<span class="eyebrow"><?php echo esc_html( $about_eyebrow ); ?></span>
					<h2><?php echo esc_html( $about_title ); ?></h2>
					<?php if ( '' !== $about_body ) : ?>
						<?php echo wp_kses_post( wpautop( $about_body ) ); ?>
					<?php else : ?>
						<p><?php esc_html_e( 'Showtime Pools has become a trusted name for homeowners, property managers, and businesses seeking long-lasting, high-performance pool systems. Repairs, weekly service, remodels, equipment, inspections, and outdoor living are all handled by one in-house team.', 'showtime-pools' ); ?></p>
						<p><?php esc_html_e( 'We do not believe in shortcuts, only results that stand the test of time. Every project is treated like it is our own backyard. That means engineered structure, honest communication, premium materials, and standing behind our work with integrity.', 'showtime-pools' ); ?></p>
						<p><?php esc_html_e( 'When something breaks, we identify the root cause first so you do not waste money on temporary patches. When you are remodeling, we coordinate every trade so you are not chasing five contractors. When you are buying a house, we inspect the pool independently and tell you the truth.', 'showtime-pools' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="int-section int-section--cream" data-reveal>
		<div class="container">
			<header class="int-section__head">
				<span class="eyebrow"><?php esc_html_e( 'What we believe', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php echo esc_html( $values_title ); ?></h2>
				<?php if ( '' !== $values_intro ) : ?>
					<p class="int-section__lead"><?php echo esc_html( $values_intro ); ?></p>
				<?php endif; ?>
			</header>
			<div class="values-grid">
				<?php
				$values_default = array(
					array( 'icon' => 'gem',     'title' => __( 'Durable, safe, visually stunning pools', 'showtime-pools' ),     'body' => __( 'Pools that last decades and look better at year five than they did at year one. Engineered structure, premium finish, modern equipment.', 'showtime-pools' ) ),
					array( 'icon' => 'sparkle', 'title' => __( 'Proven methods, modern technology', 'showtime-pools' ),         'body' => __( 'We blend tested construction methods with current automation, salt systems, and energy-efficient equipment. No experiments on your dime.', 'showtime-pools' ) ),
					array( 'icon' => 'check',   'title' => __( 'Honest communication start to finish', 'showtime-pools' ),     'body' => __( 'Itemized written quotes. Daily updates during construction. Final walk-through with a punch list. Nothing important happens verbally.', 'showtime-pools' ) ),
					array( 'icon' => 'shield',  'title' => __( 'Standing behind our work with integrity', 'showtime-pools' ),  'body' => __( 'Two-year workmanship warranty on construction. Five-year warranty on PebbleTec finishes. Manufacturer pass-through on every piece of equipment.', 'showtime-pools' ) ),
					array( 'icon' => 'heart',   'title' => __( 'Dedicated to everything we do', 'showtime-pools' ),            'body' => __( 'Same crew start to finish. No subcontractors. No rotating techs. The person who quotes the job is on-site when the work happens.', 'showtime-pools' ) ),
					array( 'icon' => 'star',    'title' => __( 'No shortcuts, only lasting results', 'showtime-pools' ),       'body' => __( 'We say no to bad ideas, including our own. If a finish, a layout, or a piece of equipment will not last, we tell you up front.', 'showtime-pools' ) ),
				);
				$values = function_exists( 'showtime_acf_rows' )
					? showtime_acf_rows( 'about_value_cards', $values_default )
					: $values_default;
				$n = 0;
				foreach ( $values as $v ) :
					$n++;
					$num   = str_pad( (string) $n, 2, '0', STR_PAD_LEFT );
					$title = (string) ( $v['title'] ?? '' );
					$body  = (string) ( $v['body'] ?? '' );
					if ( '' === $title && '' === $body ) { continue; }
				?>
					<article class="value-card">
						<span class="value-card__num"><?php echo esc_html( $num ); ?></span>
						<h3><?php echo esc_html( $title ); ?></h3>
						<p><?php echo esc_html( $body ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="int-section" data-reveal>
		<div class="container">
			<header class="int-section__head">
				<span class="eyebrow"><?php esc_html_e( 'The team', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php esc_html_e( 'Who actually shows up to your house.', 'showtime-pools' ); ?></h2>
				<p class="int-section__lead"><?php esc_html_e( 'Four people you will meet. The same four who own your project from the first call to the final walk-through.', 'showtime-pools' ); ?></p>
			</header>
			<div class="team-grid">
				<?php
				$team_default = array(
					array( 'name' => 'Steve Adams', 'role' => __( 'Founder & CEO', 'showtime-pools' ), 'note' => __( 'On every quote, walks every site, pulls every permit personally. The phone you call rings on his desk.', 'showtime-pools' ), 'initials' => 'SA', 'href' => home_url( '/the-founder/' ) ),
					array( 'name' => 'Viktor O',     'role' => __( 'Repair Manager', 'showtime-pools' ),         'note' => __( 'Runs the repair line. Diagnoses the failure before he quotes the fix. Pentair- and Jandy-certified for warranty pass-through.', 'showtime-pools' ), 'initials' => 'VO', 'href' => '' ),
					array( 'name' => 'Felipe A',    'role' => __( 'Pool Service Technician', 'showtime-pools' ), 'note' => __( 'Senior route tech. Same customers every week. Photo report after every visit before he leaves the driveway.', 'showtime-pools' ), 'initials' => 'FA', 'href' => '' ),
					array( 'name' => 'George C',    'role' => __( 'Senior Cleaner', 'showtime-pools' ),          'note' => __( 'Owns the chemistry-and-detail side of weekly maintenance. Tile-line wipe-down, full chemistry balance, equipment runtime check.', 'showtime-pools' ), 'initials' => 'GC', 'href' => '' ),
				);
				// Priority: native wp_options (Showtime Pools → Site Content → Team) → ACF → PHP fallback.
				if ( $_ct ) {
					$team = $_ct::get_all_team();
				} else {
					$team = showtime_acf_rows( 'team', $team_default );
				}
				foreach ( $team as $t ) :
					$t_name     = (string) ( $t['name'] ?? '' );
					$t_role     = (string) ( $t['role'] ?? '' );
					$t_note     = (string) ( $t['note'] ?? '' );
					$t_href     = (string) ( $t['href'] ?? '' );
					$t_initials = (string) ( $t['initials'] ?? '' );
					if ( '' === $t_initials && '' !== $t_name ) {
						$parts = preg_split( '/\s+/', $t_name );
						$t_initials = strtoupper( substr( $parts[0] ?? '', 0, 1 ) . substr( $parts[1] ?? '', 0, 1 ) );
					}
					// Photo field — ACF returns an array with url/sizes; plain string also accepted.
					$t_photo_raw = $t['photo'] ?? '';
					$t_photo_url = '';
					if ( is_array( $t_photo_raw ) ) {
						$t_photo_url = (string) ( $t_photo_raw['sizes']['medium_large'] ?? $t_photo_raw['sizes']['large'] ?? $t_photo_raw['url'] ?? '' );
					} elseif ( is_string( $t_photo_raw ) ) {
						$t_photo_url = $t_photo_raw;
					}
				?>
					<article class="team-card<?php echo $t_photo_url ? ' team-card--has-photo' : ''; ?>">
						<?php if ( $t_photo_url ) : ?>
							<div class="team-card__photo">
								<img src="<?php echo esc_url( $t_photo_url ); ?>"
									 alt="<?php echo esc_attr( $t_name ); ?>"
									 loading="lazy" decoding="async">
							</div>
						<?php else : ?>
							<div class="team-card__avatar" aria-hidden="true"><?php echo esc_html( $t_initials ); ?></div>
						<?php endif; ?>
						<div class="team-card__body">
							<h3 class="team-card__name"><?php echo esc_html( $t_name ); ?></h3>
							<p class="team-card__role"><?php echo esc_html( $t_role ); ?></p>
							<?php if ( '' !== $t_note ) : ?>
								<p class="team-card__note"><?php echo esc_html( $t_note ); ?></p>
							<?php endif; ?>
							<?php if ( '' !== $t_href ) : ?>
								<a class="team-card__link" href="<?php echo esc_url( $t_href ); ?>"><?php esc_html_e( 'Full bio →', 'showtime-pools' ); ?></a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="int-section int-section--cream" data-reveal>
		<div class="container">
			<header class="int-section__head">
				<span class="eyebrow"><?php esc_html_e( 'Certifications & partnerships', 'showtime-pools' ); ?></span>
				<h2 class="balance"><?php esc_html_e( 'Manufacturer-certified. Trade-trained. Accountable.', 'showtime-pools' ); ?></h2>
			</header>
			<div class="creds-grid">
				<?php
				$creds_default = array(
					array( 'h' => __( 'Pentair Authorized Service', 'showtime-pools' ),   'b' => __( 'Manufacturer warranty pass-through on IntelliFlo, IntelliCenter, MasterTemp, and IC40 salt cells.', 'showtime-pools' ) ),
					array( 'h' => __( 'Jandy Authorized Service', 'showtime-pools' ),     'b' => __( 'AquaLink, AquaPure, JXi heater, and Stealth pump warranty pass-through.', 'showtime-pools' ) ),
					array( 'h' => __( 'PebbleTec Certified Applicator', 'showtime-pools' ),'b' => __( 'Five-year written finish warranty backed by PebbleTec. Annual applicator training.', 'showtime-pools' ) ),
					array( 'h' => __( 'California Code Compliance', 'showtime-pools' ),   'b' => __( 'Every permit, bonding inspection, and electrical sign-off pulled through LA County and city counters in-house.', 'showtime-pools' ) ),
				);
				// Priority: native wp_options → ACF → PHP fallback.
				$creds = $_ct ? $_ct::get_all_creds() : showtime_acf_rows( 'credentials', $creds_default );
				foreach ( $creds as $c ) :
				?>
					<article class="creds-card">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
						<div>
							<h3><?php echo esc_html( $c['h'] ); ?></h3>
							<p><?php echo esc_html( $c['b'] ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>


</main>
<script type="application/ld+json"><?php echo wp_json_encode( $person_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php get_footer();
