<?php
/**
 * Template Name: Style Guide
 *
 * Living showcase of every design token and component in the design system.
 * Assign to a page (suggested slug: /style-guide/) and review at CHECKPOINT 1.
 * If anything looks off, swap values in assets/css/tokens.css and the entire
 * site updates. This page is the canonical reference.
 *
 * Not indexed (noindex meta below) and not linked from the public nav.
 *
 * @package ShowtimePools
 */

defined( 'ABSPATH' ) || exit;

get_header();

$brand_scale  = array( 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950 );
$accent_scale = array( 50, 100, 200, 300, 400, 500, 600, 700, 800, 900 );
$ink_scale    = array( 50, 100, 200, 300, 400, 500, 600, 700, 800, 900 );
$semantic     = array(
	'success' => __( 'Success', 'showtime-pools' ),
	'warning' => __( 'Warning', 'showtime-pools' ),
	'danger'  => __( 'Danger', 'showtime-pools' ),
	'info'    => __( 'Info', 'showtime-pools' ),
);

?>
<meta name="robots" content="noindex,nofollow">

<main id="primary" class="site-main style-guide">

	<!-- Header -->
	<section class="section section--surface">
		<div class="container stack stack--md">
			<span class="eyebrow"><?php esc_html_e( 'Design System v0.1', 'showtime-pools' ); ?></span>
			<h1><?php esc_html_e( 'Showtime Pools — Style Guide', 'showtime-pools' ); ?></h1>
			<p class="lead"><?php esc_html_e( 'Every color, type ramp, and component used on showtimepools.com. Approve here at CHECKPOINT 1, and the rest of the build inherits this system. Edit values in assets/css/tokens.css to retune the entire site in one place.', 'showtime-pools' ); ?></p>
		</div>
	</section>

	<!-- TOC -->
	<section class="section section--tight">
		<div class="container">
			<nav aria-label="<?php esc_attr_e( 'Style guide sections', 'showtime-pools' ); ?>" class="breadcrumbs">
				<a href="#colors"><?php esc_html_e( 'Colors', 'showtime-pools' ); ?></a><span class="breadcrumbs__sep">/</span>
				<a href="#typography"><?php esc_html_e( 'Typography', 'showtime-pools' ); ?></a><span class="breadcrumbs__sep">/</span>
				<a href="#spacing"><?php esc_html_e( 'Spacing', 'showtime-pools' ); ?></a><span class="breadcrumbs__sep">/</span>
				<a href="#radius-shadow"><?php esc_html_e( 'Radius &amp; Shadow', 'showtime-pools' ); ?></a><span class="breadcrumbs__sep">/</span>
				<a href="#buttons"><?php esc_html_e( 'Buttons', 'showtime-pools' ); ?></a><span class="breadcrumbs__sep">/</span>
				<a href="#forms"><?php esc_html_e( 'Forms', 'showtime-pools' ); ?></a><span class="breadcrumbs__sep">/</span>
				<a href="#cards"><?php esc_html_e( 'Cards', 'showtime-pools' ); ?></a><span class="breadcrumbs__sep">/</span>
				<a href="#badges-tags"><?php esc_html_e( 'Badges &amp; Tags', 'showtime-pools' ); ?></a><span class="breadcrumbs__sep">/</span>
				<a href="#alerts"><?php esc_html_e( 'Alerts', 'showtime-pools' ); ?></a>
			</nav>
		</div>
	</section>

	<!-- Colors -->
	<section id="colors" class="section">
		<div class="container stack stack--xl">
			<header class="stack stack--sm">
				<span class="eyebrow"><?php esc_html_e( 'Tokens', 'showtime-pools' ); ?></span>
				<h2><?php esc_html_e( 'Colors', 'showtime-pools' ); ?></h2>
				<p class="lead"><?php esc_html_e( 'Brand: deep ocean blue. Accent: aqua, used sparingly for emphasis and hover states. Ink: warm greyscale for text and surfaces.', 'showtime-pools' ); ?></p>
			</header>

			<div>
				<h3 style="font-size:var(--fs-xl)"><?php esc_html_e( 'Brand', 'showtime-pools' ); ?></h3>
				<div class="grid-4" style="margin-top:var(--sp-4)">
					<?php foreach ( $brand_scale as $step ) : ?>
						<div class="swatch">
							<div class="swatch__color" style="background: var(--c-brand-<?php echo (int) $step; ?>)"></div>
							<span class="swatch__name">brand-<?php echo (int) $step; ?></span>
							<span class="swatch__value">var(--c-brand-<?php echo (int) $step; ?>)</span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div>
				<h3 style="font-size:var(--fs-xl)"><?php esc_html_e( 'Accent', 'showtime-pools' ); ?></h3>
				<div class="grid-4" style="margin-top:var(--sp-4)">
					<?php foreach ( $accent_scale as $step ) : ?>
						<div class="swatch">
							<div class="swatch__color" style="background: var(--c-accent-<?php echo (int) $step; ?>)"></div>
							<span class="swatch__name">accent-<?php echo (int) $step; ?></span>
							<span class="swatch__value">var(--c-accent-<?php echo (int) $step; ?>)</span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div>
				<h3 style="font-size:var(--fs-xl)"><?php esc_html_e( 'Ink (text + surface)', 'showtime-pools' ); ?></h3>
				<div class="grid-4" style="margin-top:var(--sp-4)">
					<?php foreach ( $ink_scale as $step ) : ?>
						<div class="swatch">
							<div class="swatch__color" style="background: var(--c-ink-<?php echo (int) $step; ?>)"></div>
							<span class="swatch__name">ink-<?php echo (int) $step; ?></span>
							<span class="swatch__value">var(--c-ink-<?php echo (int) $step; ?>)</span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div>
				<h3 style="font-size:var(--fs-xl)"><?php esc_html_e( 'Semantic', 'showtime-pools' ); ?></h3>
				<div class="grid-4" style="margin-top:var(--sp-4)">
					<?php foreach ( $semantic as $key => $label ) : ?>
						<div class="swatch">
							<div class="swatch__color" style="background: var(--c-<?php echo esc_attr( $key ); ?>)"></div>
							<span class="swatch__name"><?php echo esc_html( $label ); ?></span>
							<span class="swatch__value">var(--c-<?php echo esc_attr( $key ); ?>)</span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>

	<!-- Typography -->
	<section id="typography" class="section section--surface">
		<div class="container stack stack--xl">
			<header class="stack stack--sm">
				<span class="eyebrow"><?php esc_html_e( 'Tokens', 'showtime-pools' ); ?></span>
				<h2><?php esc_html_e( 'Typography', 'showtime-pools' ); ?></h2>
				<p class="lead"><?php esc_html_e( 'Display: Plus Jakarta Sans (700/800). Body: Inter (400/500/600). Both fluid, both Google Fonts. Self-host via WP Rocket post-launch.', 'showtime-pools' ); ?></p>
			</header>

			<div class="stack stack--lg">
				<div><span class="muted swatch__value">--fs-6xl</span><div style="font-family:var(--ff-display);font-size:var(--fs-6xl);font-weight:800;line-height:1.05;letter-spacing:-0.02em">Your dream pool, perfectly maintained.</div></div>
				<div><span class="muted swatch__value">--fs-5xl / h1</span><h1>Premium pool service in Sherman Oaks</h1></div>
				<div><span class="muted swatch__value">--fs-4xl / h2</span><h2>Construction, remodeling, weekly care</h2></div>
				<div><span class="muted swatch__value">--fs-3xl / h3</span><h3>Trusted by homeowners across LA</h3></div>
				<div><span class="muted swatch__value">--fs-2xl / h4</span><h4>Twenty years of clear water</h4></div>
				<div><span class="muted swatch__value">--fs-xl / h5</span><h5>Available seven days a week</h5></div>
				<div><span class="muted swatch__value">--fs-lg / h6 (eyebrow style)</span><h6>Showtime Pools Mechanics</h6></div>
				<div><span class="muted swatch__value">.lead</span><p class="lead">Showtime Pools has serviced LA pools for over two decades. Steve Adams personally inspects every project, every week. No subcontractors, no surprises, no skipped weeks.</p></div>
				<div><span class="muted swatch__value">body</span><p>Default body text uses Inter at 16px with a relaxed 1.5 line-height. Long paragraphs cap at 70ch for comfortable reading. Links inherit the brand color and slide to the accent on hover, with a 1px underline at 0.18em offset.</p></div>
				<div><span class="muted swatch__value">small</span><p><small>Small text for captions, footnotes, and disclaimers. License #ABC123. Insured + bonded.</small></p></div>
				<div><span class="muted swatch__value">.eyebrow</span><span class="eyebrow">Limited time offer</span></div>
			</div>
		</div>
	</section>

	<!-- Spacing -->
	<section id="spacing" class="section">
		<div class="container stack stack--xl">
			<header class="stack stack--sm">
				<span class="eyebrow"><?php esc_html_e( 'Tokens', 'showtime-pools' ); ?></span>
				<h2><?php esc_html_e( 'Spacing scale', 'showtime-pools' ); ?></h2>
				<p class="lead"><?php esc_html_e( '4px base. Most spacing comes from .stack and .cluster gap utilities, not raw margins.', 'showtime-pools' ); ?></p>
			</header>

			<div class="stack stack--sm">
				<?php foreach ( array( '1' => 4, '2' => 8, '3' => 12, '4' => 16, '5' => 20, '6' => 24, '8' => 32, '10' => 40, '12' => 48, '16' => 64, '20' => 80, '24' => 96 ) as $token => $px ) : ?>
					<div class="cluster">
						<span class="swatch__value muted" style="min-width:120px">--sp-<?php echo esc_html( (string) $token ); ?> (<?php echo (int) $px; ?>px)</span>
						<div style="height:14px;background:var(--c-brand-500);width:<?php echo (int) $px; ?>px;border-radius:var(--r-xs)"></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Radius + Shadow -->
	<section id="radius-shadow" class="section section--surface">
		<div class="container stack stack--xl">
			<header class="stack stack--sm">
				<span class="eyebrow"><?php esc_html_e( 'Tokens', 'showtime-pools' ); ?></span>
				<h2><?php esc_html_e( 'Radius &amp; shadow', 'showtime-pools' ); ?></h2>
			</header>

			<div class="grid-4">
				<?php foreach ( array( 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', 'full' ) as $r ) : ?>
					<div class="stack stack--sm">
						<div style="background:var(--c-brand-700);height:80px;border-radius:var(--r-<?php echo esc_attr( $r ); ?>)"></div>
						<span class="swatch__name">--r-<?php echo esc_html( $r ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="grid-4">
				<?php foreach ( array( 'xs', 'sm', 'md', 'lg', 'xl' ) as $s ) : ?>
					<div class="stack stack--sm">
						<div style="background:#fff;height:100px;border-radius:var(--r-md);box-shadow:var(--sh-<?php echo esc_attr( $s ); ?>)"></div>
						<span class="swatch__name">--sh-<?php echo esc_html( $s ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Buttons -->
	<section id="buttons" class="section">
		<div class="container stack stack--xl">
			<header class="stack stack--sm">
				<span class="eyebrow"><?php esc_html_e( 'Components', 'showtime-pools' ); ?></span>
				<h2><?php esc_html_e( 'Buttons', 'showtime-pools' ); ?></h2>
			</header>

			<div class="stack stack--lg">
				<div class="stack stack--sm">
					<span class="swatch__value muted">.btn .btn--primary (default)</span>
					<div class="cluster">
						<a href="#" class="btn"><?php esc_html_e( 'Get a free quote', 'showtime-pools' ); ?></a>
						<a href="#" class="btn btn--lg"><?php esc_html_e( 'Book inspection', 'showtime-pools' ); ?></a>
						<a href="#" class="btn btn--sm"><?php esc_html_e( 'Learn more', 'showtime-pools' ); ?></a>
						<button class="btn" disabled><?php esc_html_e( 'Disabled', 'showtime-pools' ); ?></button>
					</div>
				</div>

				<div class="stack stack--sm">
					<span class="swatch__value muted">.btn--secondary</span>
					<div class="cluster">
						<a href="#" class="btn btn--secondary"><?php esc_html_e( 'View projects', 'showtime-pools' ); ?></a>
						<a href="#" class="btn btn--secondary btn--lg"><?php esc_html_e( 'See the map', 'showtime-pools' ); ?></a>
					</div>
				</div>

				<div class="stack stack--sm">
					<span class="swatch__value muted">.btn--ghost</span>
					<div class="cluster">
						<a href="#" class="btn btn--ghost"><?php esc_html_e( 'Read FAQ', 'showtime-pools' ); ?></a>
						<a href="#" class="btn btn--ghost btn--lg"><?php esc_html_e( 'Service areas', 'showtime-pools' ); ?></a>
					</div>
				</div>

				<div class="stack stack--sm">
					<span class="swatch__value muted">.btn--invert (use on dark backgrounds)</span>
					<div style="background:var(--c-ink-900);padding:var(--sp-6);border-radius:var(--r-md)">
						<div class="cluster">
							<a href="#" class="btn btn--invert"><?php esc_html_e( 'Schedule today', 'showtime-pools' ); ?></a>
							<a href="#" class="btn btn--ghost" style="color:var(--c-text-invert);border-color:rgba(255,255,255,0.3)"><?php esc_html_e( 'Learn more', 'showtime-pools' ); ?></a>
						</div>
					</div>
				</div>

				<div class="stack stack--sm">
					<span class="swatch__value muted">.btn--link</span>
					<a href="#" class="btn btn--link"><?php esc_html_e( 'See all 47 projects →', 'showtime-pools' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<!-- Forms -->
	<section id="forms" class="section section--surface">
		<div class="container stack stack--xl">
			<header class="stack stack--sm">
				<span class="eyebrow"><?php esc_html_e( 'Components', 'showtime-pools' ); ?></span>
				<h2><?php esc_html_e( 'Form fields', 'showtime-pools' ); ?></h2>
				<p class="lead"><?php esc_html_e( 'WP-native forms (FluentForms) inherit these styles. Heavier intake forms live as GHL landing pages.', 'showtime-pools' ); ?></p>
			</header>

			<form class="stack stack--md" style="max-width:34rem" onsubmit="return false">
				<div class="form-field">
					<label class="form-label" for="sg-name"><?php esc_html_e( 'Full name', 'showtime-pools' ); ?> <span class="required">*</span></label>
					<input class="form-input" id="sg-name" type="text" placeholder="Jane Doe">
				</div>
				<div class="form-field">
					<label class="form-label" for="sg-email"><?php esc_html_e( 'Email', 'showtime-pools' ); ?></label>
					<input class="form-input" id="sg-email" type="email" placeholder="jane@example.com">
					<span class="form-hint"><?php esc_html_e( 'We never share your email.', 'showtime-pools' ); ?></span>
				</div>
				<div class="form-field">
					<label class="form-label" for="sg-svc"><?php esc_html_e( 'Service', 'showtime-pools' ); ?></label>
					<select class="form-select" id="sg-svc">
						<option><?php esc_html_e( 'Weekly maintenance', 'showtime-pools' ); ?></option>
						<option><?php esc_html_e( 'Pool remodeling', 'showtime-pools' ); ?></option>
						<option><?php esc_html_e( 'Equipment repair', 'showtime-pools' ); ?></option>
						<option><?php esc_html_e( 'Pre-purchase inspection', 'showtime-pools' ); ?></option>
					</select>
				</div>
				<div class="form-field">
					<label class="form-label" for="sg-msg"><?php esc_html_e( 'Message', 'showtime-pools' ); ?></label>
					<textarea class="form-textarea" id="sg-msg" placeholder="Tell us about your pool"></textarea>
				</div>
				<div class="form-field">
					<label class="form-label" for="sg-err"><?php esc_html_e( 'Field with error', 'showtime-pools' ); ?></label>
					<input class="form-input" id="sg-err" type="text" aria-invalid="true" value="bad input">
					<span class="form-error"><?php esc_html_e( 'Please enter a valid value.', 'showtime-pools' ); ?></span>
				</div>
				<button type="submit" class="btn"><?php esc_html_e( 'Submit', 'showtime-pools' ); ?></button>
			</form>
		</div>
	</section>

	<!-- Cards -->
	<section id="cards" class="section">
		<div class="container stack stack--xl">
			<header class="stack stack--sm">
				<span class="eyebrow"><?php esc_html_e( 'Components', 'showtime-pools' ); ?></span>
				<h2><?php esc_html_e( 'Cards', 'showtime-pools' ); ?></h2>
			</header>

			<div class="grid-3">
				<article class="card card--feature">
					<span class="badge badge--accent"><?php esc_html_e( 'Featured', 'showtime-pools' ); ?></span>
					<h3 class="card__title"><?php esc_html_e( 'Sherman Oaks Backyard', 'showtime-pools' ); ?></h3>
					<p class="card__meta"><?php esc_html_e( 'Pool remodeling • Completed Mar 2026', 'showtime-pools' ); ?></p>
					<p class="card__body"><?php esc_html_e( 'Full replaster, new tile and coping, and equipment upgrade for a 1980s pool that looked brand new again.', 'showtime-pools' ); ?></p>
					<div class="card__footer">
						<span class="rating" data-stars="★★★★★" aria-label="5 out of 5"></span>
						<a href="#" class="btn btn--link"><?php esc_html_e( 'See project →', 'showtime-pools' ); ?></a>
					</div>
				</article>

				<article class="card card--service">
					<div class="card__icon" aria-hidden="true">⚙️</div>
					<h3 class="card__title"><?php esc_html_e( 'Weekly maintenance', 'showtime-pools' ); ?></h3>
					<p class="card__body"><?php esc_html_e( 'Chemical balance, debris removal, equipment check. Same tech every week.', 'showtime-pools' ); ?></p>
					<div class="card__footer">
						<span class="card__meta"><?php esc_html_e( 'From $185/mo', 'showtime-pools' ); ?></span>
						<a href="#" class="btn btn--link"><?php esc_html_e( 'Learn more →', 'showtime-pools' ); ?></a>
					</div>
				</article>

				<article class="card">
					<h3 class="card__title"><?php esc_html_e( 'Marcus K., Studio City', 'showtime-pools' ); ?></h3>
					<span class="rating" data-stars="★★★★★" aria-label="5 out of 5"></span>
					<p class="card__body"><?php esc_html_e( '"Steve has serviced our pool for 8 years. Never missed a week. Equipment runs better than the day it was installed."', 'showtime-pools' ); ?></p>
					<p class="card__meta"><?php esc_html_e( 'Verified Google review • 2 weeks ago', 'showtime-pools' ); ?></p>
				</article>
			</div>
		</div>
	</section>

	<!-- Badges + Tags -->
	<section id="badges-tags" class="section section--surface">
		<div class="container stack stack--lg">
			<header class="stack stack--sm">
				<span class="eyebrow"><?php esc_html_e( 'Components', 'showtime-pools' ); ?></span>
				<h2><?php esc_html_e( 'Badges &amp; tags', 'showtime-pools' ); ?></h2>
			</header>

			<div class="cluster">
				<span class="badge"><?php esc_html_e( 'Default', 'showtime-pools' ); ?></span>
				<span class="badge badge--brand"><?php esc_html_e( 'Brand', 'showtime-pools' ); ?></span>
				<span class="badge badge--accent"><?php esc_html_e( 'Accent', 'showtime-pools' ); ?></span>
				<span class="badge badge--success"><?php esc_html_e( 'Verified', 'showtime-pools' ); ?></span>
				<span class="badge badge--warning"><?php esc_html_e( 'Limited', 'showtime-pools' ); ?></span>
				<span class="badge badge--danger"><?php esc_html_e( 'Urgent', 'showtime-pools' ); ?></span>
			</div>

			<div class="cluster">
				<a href="#" class="tag">#sherman-oaks</a>
				<a href="#" class="tag">#pool-remodeling</a>
				<a href="#" class="tag">#equipment-upgrade</a>
				<a href="#" class="tag">#tile-and-coping</a>
				<a href="#" class="tag">#weekly-care</a>
			</div>
		</div>
	</section>

	<!-- Alerts -->
	<section id="alerts" class="section">
		<div class="container stack stack--md">
			<header class="stack stack--sm">
				<span class="eyebrow"><?php esc_html_e( 'Components', 'showtime-pools' ); ?></span>
				<h2><?php esc_html_e( 'Alerts', 'showtime-pools' ); ?></h2>
			</header>

			<div class="alert alert--info"><?php esc_html_e( 'Heads up: weekly maintenance routes shift to Tuesday/Thursday in summer. Check your service window before the season starts.', 'showtime-pools' ); ?></div>
			<div class="alert alert--success"><?php esc_html_e( 'Quote submitted. Steve will reach out within 1 business day.', 'showtime-pools' ); ?></div>
			<div class="alert alert--warning"><?php esc_html_e( 'Service area limited to LA county. Outside our zone? Email us for a referral.', 'showtime-pools' ); ?></div>
			<div class="alert alert--danger"><?php esc_html_e( 'Something went wrong. Please retry, or call (323) 825-2099.', 'showtime-pools' ); ?></div>
		</div>
	</section>

	<!-- CTA -->
	<section class="section section--brand">
		<div class="container text-center stack stack--md">
			<h2 style="color:var(--c-text-invert)"><?php esc_html_e( 'Approve this design system?', 'showtime-pools' ); ?></h2>
			<p class="lead" style="color:rgba(255,255,255,0.85);margin-inline:auto"><?php esc_html_e( 'CHECKPOINT 1. Sign off here and Phase 1D (header + footer) starts. Want a brand color, type pairing, or radius scale changed? Drop the request and we retune tokens.css before moving on.', 'showtime-pools' ); ?></p>
			<div class="cluster" style="justify-content:center">
				<a href="#" class="btn btn--invert"><?php esc_html_e( 'Approve and continue', 'showtime-pools' ); ?></a>
				<a href="#" class="btn btn--ghost" style="color:var(--c-text-invert);border-color:rgba(255,255,255,0.3)"><?php esc_html_e( 'Request changes', 'showtime-pools' ); ?></a>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
