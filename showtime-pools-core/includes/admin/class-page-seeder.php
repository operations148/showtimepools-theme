<?php
/**
 * Page seeder. Creates the standard page structure on demand.
 *
 * Idempotent: existing pages by slug are skipped, never duplicated.
 * Runs on plugin activation via run_all_idempotent() (see the activation
 * hook in showtime-pools-core.php) and can also be re-run manually from
 * Showtime Pools → Tools. Either path is safe — existing pages are skipped.
 *
 * Phase 1F seeds: parent /services/ + 8 service children
 * Phase 1H seeds: /contact/, /quote/, /book/
 * Future phases register their own seed groups via the same UI.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Admin;

use Showtime\Services;
use Showtime\Areas;
use Showtime\Inspections;
use Showtime\Projects;

defined( 'ABSPATH' ) || exit;

final class PageSeeder {

	private const PAGE_SLUG       = 'showtime-tools';
	private const NONCE_SERVICES  = 'showtime_seed_services';
	private const NONCE_STATIC    = 'showtime_seed_static';
	private const SVC_PARENT      = 'services';
	private const DEFAULT_STATUS  = 'publish';

	/**
	 * Static pages registry (contact, quote, book). Each entry tells the
	 * seeder what slug, title, template, and meta to use. Adding a new
	 * structural page = one entry here, no template changes.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function static_pages(): array {
		return array(
			array( 'slug' => 'contact', 'title' => __( 'Contact', 'showtime-pools-core' ),            'template' => 'page-contact.php',     'meta' => array( '_showtime_section' => 'contact' ) ),
			array( 'slug' => 'quote',   'title' => __( 'Get a Quote', 'showtime-pools-core' ),       'template' => 'page-iframe.php',      'meta' => array( '_showtime_section' => 'iframe', '_showtime_iframe_kind' => 'quote' ) ),
			array( 'slug' => 'book',    'title' => __( 'Book an Inspection', 'showtime-pools-core' ),'template' => 'page-iframe.php',      'meta' => array( '_showtime_section' => 'iframe', '_showtime_iframe_kind' => 'book' ) ),
			array( 'slug' => 'about',       'title' => __( 'About', 'showtime-pools-core' ),         'template' => 'page-about.php',       'meta' => array( '_showtime_section' => 'about' ) ),
			array( 'slug' => 'the-founder', 'title' => __( 'The Founder', 'showtime-pools-core' ),  'template' => 'page-founder.php',     'meta' => array( '_showtime_section' => 'founder' ) ),
			array( 'slug' => 'projects',    'title' => __( 'Projects', 'showtime-pools-core' ),     'template' => 'page-projects.php',    'meta' => array( '_showtime_section' => 'projects' ) ),
			array( 'slug' => 'reviews',     'title' => __( 'Reviews', 'showtime-pools-core' ),      'template' => 'page-reviews.php',     'meta' => array( '_showtime_section' => 'reviews' ) ),
			array( 'slug' => 'blog',        'title' => __( 'Blog', 'showtime-pools-core' ),         'template' => 'page-blog.php',        'meta' => array( '_showtime_section' => 'blog-hub' ) ),
			array( 'slug' => 'service-areas', 'title' => __( 'Service Areas', 'showtime-pools-core' ), 'template' => 'page-areas.php',     'meta' => array( '_showtime_section' => 'areas-hub' ) ),
			array( 'slug' => 'pool-inspections', 'title' => __( 'Pool Inspections', 'showtime-pools-core' ), 'template' => 'page-inspections.php', 'meta' => array( '_showtime_section' => 'inspections-hub' ) ),
			array( 'slug' => 'affiliate', 'title' => __( 'Affiliate Program', 'showtime-pools-core' ), 'template' => 'page-affiliate.php', 'meta' => array( '_showtime_section' => 'affiliate' ) ),
			array( 'slug' => 'privacy-policy', 'title' => __( 'Privacy Policy', 'showtime-pools-core' ), 'template' => 'page-legal.php', 'meta' => array( '_showtime_section' => 'legal' ) ),
			array( 'slug' => 'terms',          'title' => __( 'Terms', 'showtime-pools-core' ),          'template' => 'page-legal.php', 'meta' => array( '_showtime_section' => 'legal' ) ),
		);
	}

	/**
	 * Area children registered under /service-areas/.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function area_pages(): array {
		if ( ! class_exists( '\\Showtime\\Areas' ) ) {
			return array();
		}
		return array_map(
			static fn( $area ) => array(
				'slug'     => (string) ( $area['slug'] ?? '' ),
				'title'    => (string) ( $area['name'] ?? '' ),
				'template' => 'page-area.php',
				'meta'     => array( '_showtime_area_slug' => (string) ( $area['slug'] ?? '' ), '_showtime_section' => 'area' ),
				'excerpt'  => (string) ( $area['lead'] ?? '' ),
			),
			Areas::all()
		);
	}

	/**
	 * Inspection children registered under /pool-inspections/.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function inspection_pages(): array {
		if ( ! class_exists( '\\Showtime\\Inspections' ) ) {
			return array();
		}
		return array_map(
			static fn( $i ) => array(
				'slug'     => (string) ( $i['slug'] ?? '' ),
				'title'    => (string) ( $i['name'] ?? '' ),
				'template' => 'page-inspection.php',
				'meta'     => array( '_showtime_inspection_slug' => (string) ( $i['slug'] ?? '' ), '_showtime_section' => 'inspection' ),
				'excerpt'  => (string) ( $i['lead'] ?? '' ),
			),
			Inspections::all()
		);
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 11 );
		add_action( 'admin_post_showtime_seed_services', array( $this, 'handle_seed_services' ) );
		add_action( 'admin_post_showtime_seed_static',   array( $this, 'handle_seed_static' ) );
	}

	/**
	 * Idempotent first-run seed. Called from the plugin activation hook so
	 * a clean live deploy renders the same site the developer sees locally,
	 * with zero admin clicks. Skips silently if pages exist (idempotent).
	 *
	 * Auth checks are intentionally NOT performed here — activation hooks
	 * run in a privileged WP-internal context, not a user-facing request.
	 *
	 * @return array{created:int,skipped:int}
	 */
	public function run_all_idempotent(): array {
		$created = 0;
		$skipped = 0;

		// 1) Services parent + service child pages
		$ok = $this->ensure_page(
			self::SVC_PARENT,
			__( 'Services', 'showtime-pools-core' ),
			0,
			array(
				'page_template' => 'page-services-hub.php',
				'meta_input'    => array( '_showtime_section' => 'services-hub' ),
			)
		);
		$ok ? $created++ : $skipped++;

		$svc_parent_id = $this->page_id( self::SVC_PARENT );
		if ( class_exists( '\\Showtime\\Services' ) ) {
			foreach ( Services::all() as $svc ) {
				if ( empty( $svc['slug'] ) ) {
					continue;
				}
				$ok = $this->ensure_page(
					(string) $svc['slug'],
					(string) ( $svc['title'] ?? $svc['slug'] ),
					(int) $svc_parent_id,
					array(
						'page_template' => 'page-service.php',
						'meta_input'    => array(
							'_showtime_service_slug' => (string) $svc['slug'],
							'_showtime_section'      => 'service',
						),
						'post_excerpt'  => (string) ( $svc['summary'] ?? '' ),
					)
				);
				$ok ? $created++ : $skipped++;
			}
		}

		// 2) Static pages (contact, quote, book, about, projects, reviews, areas-hub, inspections-hub, privacy, terms)
		foreach ( $this->static_pages() as $page ) {
			$ok = $this->ensure_page(
				(string) $page['slug'],
				(string) $page['title'],
				0,
				array(
					'page_template' => $page['template'],
					'meta_input'    => $page['meta'],
				)
			);
			$ok ? $created++ : $skipped++;
		}

		// 3) Area children under /service-areas/
		$areas_parent_id = $this->page_id( 'service-areas' );
		foreach ( $this->area_pages() as $page ) {
			if ( '' === (string) ( $page['slug'] ?? '' ) ) {
				continue;
			}
			$ok = $this->ensure_page(
				(string) $page['slug'],
				(string) $page['title'],
				(int) $areas_parent_id,
				array(
					'page_template' => $page['template'],
					'meta_input'    => $page['meta'],
					'post_excerpt'  => (string) ( $page['excerpt'] ?? '' ),
				)
			);
			$ok ? $created++ : $skipped++;
		}

		// 4) Inspection children under /pool-inspections/
		$insp_parent_id = $this->page_id( 'pool-inspections' );
		foreach ( $this->inspection_pages() as $page ) {
			if ( '' === (string) ( $page['slug'] ?? '' ) ) {
				continue;
			}
			$ok = $this->ensure_page(
				(string) $page['slug'],
				(string) $page['title'],
				(int) $insp_parent_id,
				array(
					'page_template' => $page['template'],
					'meta_input'    => $page['meta'],
					'post_excerpt'  => (string) ( $page['excerpt'] ?? '' ),
				)
			);
			$ok ? $created++ : $skipped++;
		}

		// 5) Project CPT — seed 8 demo projects matched to bundled photos.
		$proj_result = $this->seed_projects();
		$created += $proj_result['created'];
		$skipped += $proj_result['skipped'];

		// 6) Blog — seed 3 categories + 6 demo posts.
		$blog_result = $this->seed_blog();
		$created += $blog_result['created'];
		$skipped += $blog_result['skipped'];

		// 7) Fix page templates that may be wrong on existing installs.
		$this->fix_page_templates();

		// 8) Pre-fill founder page with default bio content if empty.
		$this->fill_founder_content();

		// 9) Nav menu: intentionally NOT auto-seeded. The theme header
		// (template-parts/header/primary-nav.php) renders a canonical
		// hardcoded nav with mega-menus whenever no WP menu exists, and
		// prefers any assigned menu otherwise. Auto-creating a menu here
		// shadowed that canonical nav with WP's default (unstyled) markup,
		// making local diverge from live. Leaving menus unseeded keeps a
		// fresh install identical to the live site.

		flush_rewrite_rules();

		return array(
			'created' => $created,
			'skipped' => $skipped,
		);
	}

	/**
	 * Seed the `project` CPT from the bundled registry. Idempotent — a
	 * project with the same slug is skipped on subsequent runs.
	 *
	 * @return array{created:int,skipped:int}
	 */
	public function seed_projects(): array {
		$created = 0;
		$skipped = 0;

		if ( ! class_exists( '\\Showtime\\Projects' ) ) {
			return array( 'created' => 0, 'skipped' => 0 );
		}

		$projects = Projects::all();
		$n = 0;
		foreach ( $projects as $p ) {
			$n++;
			$slug = (string) ( $p['slug'] ?? '' );
			if ( '' === $slug ) { continue; }

			$existing = get_page_by_path( $slug, OBJECT, 'project' );
			if ( $existing instanceof \WP_Post ) {
				$skipped++;
				continue;
			}

			$post_id = wp_insert_post(
				array(
					'post_title'   => (string) ( $p['title']   ?? $slug ),
					'post_name'    => $slug,
					'post_type'    => 'project',
					'post_status'  => 'publish',
					'post_excerpt' => (string) ( $p['excerpt'] ?? '' ),
					'menu_order'   => $n, // drives bundled photo slot (project_1..8)
				),
				true
			);
			if ( is_wp_error( $post_id ) || ! $post_id ) {
				$skipped++;
				continue;
			}

			// Copy registry fields into post meta. ACF reads these via field name.
			foreach ( array( 'neighborhood', 'completion_date', 'finish', 'scope', 'value_label', 'duration_label', 'client_quote', 'client_name' ) as $key ) {
				if ( isset( $p[ $key ] ) && '' !== $p[ $key ] ) {
					update_post_meta( $post_id, $key, (string) $p[ $key ] );
				}
			}

			$created++;
		}

		return array( 'created' => $created, 'skipped' => $skipped );
	}

	/**
	 * Seed the blog: 3 categories + 6 demo posts. Idempotent — categories
	 * are looked up by slug, posts by slug. Existing items are skipped.
	 *
	 * @return array{created:int,skipped:int}
	 */
	public function seed_blog(): array {
		$created = 0;
		$skipped = 0;

		$seed_path = SHOWTIME_CORE_DIR . 'includes/data/blog-seed.php';
		if ( ! file_exists( $seed_path ) ) {
			return array( 'created' => 0, 'skipped' => 0 );
		}
		$seed = (array) include $seed_path;
		$cats = (array) ( $seed['categories'] ?? array() );
		$posts = (array) ( $seed['posts'] ?? array() );

		$cat_ids = array(); // slug → term_id

		foreach ( $cats as $c ) {
			$slug = (string) ( $c['slug'] ?? '' );
			if ( '' === $slug ) { continue; }
			$existing = get_term_by( 'slug', $slug, 'category' );
			if ( $existing instanceof \WP_Term ) {
				$cat_ids[ $slug ] = (int) $existing->term_id;
				$skipped++;
				continue;
			}
			$res = wp_insert_term(
				(string) ( $c['name'] ?? $slug ),
				'category',
				array(
					'slug'        => $slug,
					'description' => (string) ( $c['description'] ?? '' ),
				)
			);
			if ( ! is_wp_error( $res ) ) {
				$cat_ids[ $slug ] = (int) $res['term_id'];
				$created++;
			}
		}

		foreach ( $posts as $p ) {
			$slug = (string) ( $p['slug'] ?? '' );
			if ( '' === $slug ) { continue; }

			$existing = get_page_by_path( $slug, OBJECT, 'post' );
			if ( $existing instanceof \WP_Post ) {
				$skipped++;
				continue;
			}

			$cat_slug = (string) ( $p['category'] ?? '' );
			$cat_id   = $cat_ids[ $cat_slug ] ?? 0;

			$post_id = wp_insert_post(
				array(
					'post_title'    => (string) ( $p['title']   ?? $slug ),
					'post_name'     => $slug,
					'post_type'     => 'post',
					'post_status'   => 'publish',
					'post_excerpt'  => (string) ( $p['excerpt'] ?? '' ),
					'post_content'  => (string) ( $p['content'] ?? '' ),
					'post_category' => $cat_id ? array( $cat_id ) : array(),
				),
				true
			);
			if ( is_wp_error( $post_id ) || ! $post_id ) {
				$skipped++;
				continue;
			}

			// Store bundled image slot for templates: post-meta `_showtime_image_slot`
			$slot = (string) ( $p['image_slot'] ?? '' );
			if ( '' !== $slot ) {
				update_post_meta( $post_id, '_showtime_image_slot', $slot );
			}

			$created++;
		}

		return array( 'created' => $created, 'skipped' => $skipped );
	}

	// ─────────────────────────────────────────────────────────────────────────
	// FIX PAGE TEMPLATES
	// ─────────────────────────────────────────────────────────────────────────

	/**
	 * Correct `_wp_page_template` on pages that exist but may have been seeded
	 * without the correct template (e.g. blog page set to wrong template or to
	 * WordPress's "Posts page" setting which overrides the page template).
	 *
	 * Also ensures the Blog page is NOT the WordPress Posts page — that setting
	 * bypasses `page-blog.php` and sends the request through `index.php` instead.
	 */
	private function fix_page_templates(): void {
		$template_map = array(
			'blog'             => 'page-blog.php',
			'the-founder'      => 'page-founder.php',
			'about'            => 'page-about.php',
			'projects'         => 'page-projects.php',
			'service-areas'    => 'page-areas.php',
			'pool-inspections' => 'page-inspections.php',
			'contact'          => 'page-contact.php',
			'affiliate'        => 'page-affiliate.php',
		);

		foreach ( $template_map as $slug => $tpl ) {
			$page = get_page_by_path( $slug, OBJECT, 'page' );
			if ( ! $page instanceof \WP_Post ) {
				continue;
			}
			$current = (string) get_post_meta( $page->ID, '_wp_page_template', true );
			if ( $current !== $tpl ) {
				update_post_meta( $page->ID, '_wp_page_template', $tpl );
			}
		}

		// Clear "page_for_posts" if it's pointing to our blog hub page — that
		// setting makes WordPress bypass the page template entirely.
		$blog_page = get_page_by_path( 'blog', OBJECT, 'page' );
		if ( $blog_page instanceof \WP_Post ) {
			if ( (int) get_option( 'page_for_posts' ) === (int) $blog_page->ID ) {
				update_option( 'page_for_posts', 0 );
			}
		}
	}

	// ─────────────────────────────────────────────────────────────────────────
	// FOUNDER CONTENT PRE-FILL
	// ─────────────────────────────────────────────────────────────────────────

	/**
	 * If the /the-founder/ page has empty post_content, write the default bio
	 * text so the Gutenberg editor is pre-filled and the native WP edit path
	 * works out of the box.
	 */
	private function fill_founder_content(): void {
		$page = get_page_by_path( 'the-founder', OBJECT, 'page' );
		if ( ! $page instanceof \WP_Post ) {
			return;
		}
		if ( '' !== trim( $page->post_content ) ) {
			return; // Already has content — don't overwrite.
		}
		wp_update_post( array(
			'ID'           => $page->ID,
			'post_content' => $this->founder_default_content(),
		) );
	}

	/**
	 * Default bio copy for the Founder page. Stored in post_content so the
	 * user can edit it directly in WP Admin → Pages → The Founder.
	 */
	private function founder_default_content(): string {
		return '<p>Steve started Showtime Pools with one truck and a handful of weekly customers in Sherman Oaks. The first decade was just service: drive the route, balance the chemistry, fix what breaks, send a photo report before leaving the driveway. Customers asked when we would start doing remodels. Steve said no for years.</p>

<p>When we finally added construction, it was because the same handful of customers kept asking. We built a pool for one. Then a remodel for another. Word got around. Today the construction line and the service line are both staffed by W-2 crew, both supervised by Steve, both working off the same standards. Same shop on Ventura Boulevard. Same trucks. Same number.</p>

<p>Quotes are written and itemized. Permits are pulled in person. The person who walks your site is on the job when the work happens. When the inspection says walk away from a deal, that is what the inspection says, even when it costs us a six-figure construction quote. Independence is the whole point.</p>';
	}

	public function register_menu(): void {
		add_submenu_page(
			'showtime-settings',
			__( 'Tools', 'showtime-pools-core' ),
			__( 'Tools', 'showtime-pools-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render' )
		);
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$notice = '';
		if ( isset( $_GET['seeded'] ) ) {
			$created = (int) ( $_GET['created'] ?? 0 );
			$skipped = (int) ( $_GET['skipped'] ?? 0 );
			$notice  = sprintf(
				/* translators: 1: created count, 2: skipped count */
				__( 'Seed complete. %1$d created, %2$d already existed.', 'showtime-pools-core' ),
				$created,
				$skipped
			);
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Showtime Pools — Tools', 'showtime-pools-core' ); ?></h1>

			<?php if ( $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
			<?php endif; ?>

			<div class="card" style="max-width:760px">
				<h2><?php esc_html_e( 'Seed service pages', 'showtime-pools-core' ); ?></h2>
				<p>
					<?php esc_html_e( 'Creates /services/ + 8 service child pages wired to the Service template. Existing pages by slug are skipped.', 'showtime-pools-core' ); ?>
				</p>

				<?php $this->render_table( $this->services_status() ); ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="showtime_seed_services">
					<?php wp_nonce_field( self::NONCE_SERVICES ); ?>
					<?php submit_button( __( 'Seed missing service pages', 'showtime-pools-core' ) ); ?>
				</form>
			</div>

			<div class="card" style="max-width:760px;margin-top:1.5em">
				<h2><?php esc_html_e( 'Seed contact, quote, and booking pages', 'showtime-pools-core' ); ?></h2>
				<p>
					<?php esc_html_e( 'Creates /contact/ (native form → GHL), /quote/ (GHL iframe), and /book/ (GHL iframe). Set the iframe URLs in Showtime Pools → Settings.', 'showtime-pools-core' ); ?>
				</p>

				<?php $this->render_table( $this->static_pages_status() ); ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="showtime_seed_static">
					<?php wp_nonce_field( self::NONCE_STATIC ); ?>
					<?php submit_button( __( 'Seed missing static pages', 'showtime-pools-core' ) ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * @param array<int, array{slug:string,title:string,exists:bool,post_status:string}> $rows
	 */
	private function render_table( array $rows ): void {
		?>
		<table class="widefat striped" style="margin:1em 0">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Slug', 'showtime-pools-core' ); ?></th>
					<th><?php esc_html_e( 'Title', 'showtime-pools-core' ); ?></th>
					<th><?php esc_html_e( 'Status', 'showtime-pools-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><code><?php echo esc_html( $row['slug'] ); ?></code></td>
						<td><?php echo esc_html( $row['title'] ); ?></td>
						<td>
							<?php if ( $row['exists'] ) : ?>
								<span style="color:#15803D">✓ <?php echo esc_html( $row['post_status'] ); ?></span>
							<?php else : ?>
								<span style="color:#92400E">— not yet</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	public function handle_seed_services(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'showtime-pools-core' ), 403 );
		}
		check_admin_referer( self::NONCE_SERVICES );

		$created = 0;
		$skipped = 0;

		$parent_ok = $this->ensure_page(
			self::SVC_PARENT,
			__( 'Services', 'showtime-pools-core' ),
			0,
			array(
				'meta_input' => array(
					'_showtime_section' => 'services-hub',
				),
			)
		);
		$parent_ok ? $created++ : $skipped++;

		$parent_id = $this->page_id( self::SVC_PARENT );

		foreach ( Services::all() as $svc ) {
			if ( empty( $svc['slug'] ) ) {
				continue;
			}
			$ok = $this->ensure_page(
				$svc['slug'],
				(string) ( $svc['title'] ?? $svc['slug'] ),
				(int) $parent_id,
				array(
					'page_template' => 'page-service.php',
					'meta_input'    => array(
						'_showtime_service_slug' => $svc['slug'],
						'_showtime_section'      => 'service',
					),
					'post_excerpt'  => (string) ( $svc['summary'] ?? '' ),
				)
			);
			$ok ? $created++ : $skipped++;
		}

		$this->finish( $created, $skipped );
	}

	public function handle_seed_static(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'showtime-pools-core' ), 403 );
		}
		check_admin_referer( self::NONCE_STATIC );

		$created = 0;
		$skipped = 0;

		foreach ( $this->static_pages() as $page ) {
			$ok = $this->ensure_page(
				$page['slug'],
				$page['title'],
				0,
				array(
					'page_template' => $page['template'],
					'meta_input'    => $page['meta'],
				)
			);
			$ok ? $created++ : $skipped++;
		}

		// Areas: parent /service-areas/ + 6 children
		$areas_parent_id = $this->page_id( 'service-areas' );
		foreach ( $this->area_pages() as $page ) {
			if ( '' === $page['slug'] ) { continue; }
			$ok = $this->ensure_page(
				$page['slug'],
				$page['title'],
				(int) $areas_parent_id,
				array(
					'page_template' => $page['template'],
					'meta_input'    => $page['meta'],
					'post_excerpt'  => $page['excerpt'] ?? '',
				)
			);
			$ok ? $created++ : $skipped++;
		}

		// Inspections: parent /pool-inspections/ + 3 children
		$insp_parent_id = $this->page_id( 'pool-inspections' );
		foreach ( $this->inspection_pages() as $page ) {
			if ( '' === $page['slug'] ) { continue; }
			$ok = $this->ensure_page(
				$page['slug'],
				$page['title'],
				(int) $insp_parent_id,
				array(
					'page_template' => $page['template'],
					'meta_input'    => $page['meta'],
					'post_excerpt'  => $page['excerpt'] ?? '',
				)
			);
			$ok ? $created++ : $skipped++;
		}

		$this->finish( $created, $skipped );
	}

	private function finish( int $created, int $skipped ): void {
		flush_rewrite_rules();
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => self::PAGE_SLUG,
					'seeded'  => 1,
					'created' => $created,
					'skipped' => $skipped,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Create a page if missing. Returns true on create, false on skip.
	 *
	 * @param array<string, mixed> $extras page_template, meta_input, post_excerpt, post_status
	 */
	private function ensure_page( string $slug, string $title, int $parent_id, array $extras = array() ): bool {
		$existing = get_page_by_path( $parent_id ? $this->path_for( $slug, $parent_id ) : $slug, OBJECT, 'page' );
		if ( $existing instanceof \WP_Post ) {
			return false;
		}

		$post_args = array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_type'    => 'page',
			'post_status'  => $extras['post_status'] ?? self::DEFAULT_STATUS,
			'post_parent'  => $parent_id,
			'post_content' => '',
			'post_excerpt' => $extras['post_excerpt'] ?? '',
			'meta_input'   => $extras['meta_input'] ?? array(),
		);

		$id = wp_insert_post( $post_args, true );
		if ( is_wp_error( $id ) || ! $id ) {
			return false;
		}

		if ( ! empty( $extras['page_template'] ) ) {
			update_post_meta( $id, '_wp_page_template', (string) $extras['page_template'] );
		}

		return true;
	}

	private function path_for( string $slug, int $parent_id ): string {
		$parent = get_post( $parent_id );
		if ( ! $parent ) {
			return $slug;
		}
		return $parent->post_name . '/' . $slug;
	}

	private function page_id( string $slug ): int {
		$p = get_page_by_path( $slug, OBJECT, 'page' );
		return $p instanceof \WP_Post ? (int) $p->ID : 0;
	}

	/**
	 * @return array<int, array{slug:string,title:string,exists:bool,post_status:string}>
	 */
	private function services_status(): array {
		$rows = array();

		$p = get_page_by_path( self::SVC_PARENT, OBJECT, 'page' );
		$rows[] = array(
			'slug'        => self::SVC_PARENT,
			'title'       => __( 'Services (parent hub)', 'showtime-pools-core' ),
			'exists'      => (bool) $p,
			'post_status' => $p ? (string) $p->post_status : '',
		);

		$parent_id = $p ? (int) $p->ID : 0;

		foreach ( Services::all() as $svc ) {
			$slug = (string) ( $svc['slug'] ?? '' );
			if ( '' === $slug ) {
				continue;
			}
			$child = $parent_id ? get_page_by_path( self::SVC_PARENT . '/' . $slug, OBJECT, 'page' ) : null;
			$rows[] = array(
				'slug'        => $slug,
				'title'       => (string) ( $svc['title'] ?? $slug ),
				'exists'      => (bool) $child,
				'post_status' => $child ? (string) $child->post_status : '',
			);
		}

		return $rows;
	}

	/**
	 * @return array<int, array{slug:string,title:string,exists:bool,post_status:string}>
	 */
	private function static_pages_status(): array {
		$rows = array();
		foreach ( $this->static_pages() as $page ) {
			$p = get_page_by_path( $page['slug'], OBJECT, 'page' );
			$rows[] = array(
				'slug'        => $page['slug'],
				'title'       => (string) $page['title'],
				'exists'      => (bool) $p,
				'post_status' => $p ? (string) $p->post_status : '',
			);
		}
		return $rows;
	}
}
