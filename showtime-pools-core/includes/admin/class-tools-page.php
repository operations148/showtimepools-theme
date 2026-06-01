<?php
/**
 * "Showtime Pools → Tools" admin submenu.
 *
 * Single page exposing the Seeder runner with two buttons (Dry run / Run).
 * Results are picked up from a 60-second transient set by the admin-post
 * handler so the user lands on a clean redirect instead of POST-refresh.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Admin;

defined( 'ABSPATH' ) || exit;

final class ToolsPage {

	private const PARENT_SLUG = 'showtime-settings';
	private const PAGE_SLUG   = 'showtime-tools';

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 30 );
	}

	public function register_menu(): void {
		add_submenu_page(
			self::PARENT_SLUG,
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

		$result = get_transient( 'showtime_seed_result_' . get_current_user_id() );
		if ( $result ) {
			delete_transient( 'showtime_seed_result_' . get_current_user_id() );
		}

		$action_url = admin_url( 'admin-post.php' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Tools', 'showtime-pools-core' ); ?></h1>

			<?php if ( $result && is_array( $result ) ) : ?>
				<?php $this->render_result_banner( $result ); ?>
			<?php endif; ?>

			<div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:24px;margin-top:1.5rem;max-width:760px;">
				<h2 style="margin-top:0;"><?php esc_html_e( 'Pre-fill content + images', 'showtime-pools-core' ); ?></h2>
				<p style="color:#555;">
					<?php esc_html_e( 'One-time setup. Writes the default text into empty page fields and imports bundled photos into the Media Library for empty image slots. Will never overwrite content or images you have already edited. Safe to run twice.', 'showtime-pools-core' ); ?>
				</p>
				<p style="color:#555;">
					<strong><?php esc_html_e( 'What gets seeded:', 'showtime-pools-core' ); ?></strong>
					<br><?php esc_html_e( 'Page text (About, Founder, Contact, Service Areas, Inspections, Blog, Reviews, Projects, Services Hub, Quote/Book, each Area and Inspection sub-page) — Team and Certifications under Site Content — and 39 bundled photos into Site Images.', 'showtime-pools-core' ); ?>
				</p>
				<p style="color:#555;">
					<strong><?php esc_html_e( 'What is NOT touched:', 'showtime-pools-core' ); ?></strong>
					<?php esc_html_e( 'Blog posts, the Projects CPT, the site logo, and any field/slot that already has a value.', 'showtime-pools-core' ); ?>
				</p>

				<form method="post" action="<?php echo esc_url( $action_url ); ?>" style="display:flex;gap:12px;flex-wrap:wrap;margin-top:1rem;">
					<?php wp_nonce_field( Seeder::ADMIN_ACTION ); ?>
					<input type="hidden" name="action" value="<?php echo esc_attr( Seeder::ADMIN_ACTION ); ?>">

					<button type="submit" name="write" value="0" class="button button-secondary button-large">
						<?php esc_html_e( 'Dry run (preview)', 'showtime-pools-core' ); ?>
					</button>
					<button type="submit"
							name="write"
							value="1"
							class="button button-primary button-large"
							onclick="return confirm('<?php echo esc_js( __( 'This writes default content into empty fields and imports bundled photos. Already-edited fields are NEVER overwritten. Proceed?', 'showtime-pools-core' ) ); ?>');">
						<?php esc_html_e( 'Run seeder', 'showtime-pools-core' ); ?>
					</button>
				</form>
			</div>

			<details style="margin-top:2rem;max-width:760px;">
				<summary style="cursor:pointer;font-weight:600;color:#666;">
					<?php esc_html_e( 'WP-CLI alternative', 'showtime-pools-core' ); ?>
				</summary>
				<pre style="background:#f6f7f7;padding:14px;border-radius:6px;font-size:12px;line-height:1.5;margin-top:0.5rem;">
wp showtime seed-all                       # dry-run (no writes)
wp showtime seed-all --write               # run for real
wp showtime seed-all --write --section=text
wp showtime seed-all --write --section=images</pre>
			</details>
		</div>
		<?php
	}

	/**
	 * @param array{write:bool, results:array, at:int} $result
	 */
	private function render_result_banner( array $result ): void {
		$write = (bool) ( $result['write'] ?? false );
		$r     = (array) ( $result['results'] ?? array() );
		$text  = (array) ( $r['text'] ?? array() );
		$img   = (array) ( $r['images'] ?? array() );

		$class = $write ? 'notice notice-success' : 'notice notice-info';
		$head  = $write ? __( '✓ Seeding complete.', 'showtime-pools-core' ) : __( '🔍 Dry-run preview.', 'showtime-pools-core' );
		?>
		<div class="<?php echo esc_attr( $class ); ?>" style="padding:14px 18px;">
			<h2 style="margin:0 0 0.75rem;font-size:16px;"><?php echo esc_html( $head ); ?></h2>

			<p style="margin:0.25rem 0;">
				<strong><?php esc_html_e( 'Text fields:', 'showtime-pools-core' ); ?></strong>
				<?php
				printf(
					/* translators: 1: written, 2: written verb, 3: skipped */
					esc_html__( '%1$d %2$s, %3$d skipped (already set).', 'showtime-pools-core' ),
					(int) ( $text['written'] ?? 0 ),
					esc_html( $write ? __( 'written', 'showtime-pools-core' ) : __( 'would write', 'showtime-pools-core' ) ),
					(int) ( $text['skipped'] ?? 0 )
				);
				?>
			</p>

			<p style="margin:0.25rem 0;">
				<strong><?php esc_html_e( 'Sitewide options (team + certifications):', 'showtime-pools-core' ); ?></strong>
				<?php
				printf(
					/* translators: 1: written, 2: written verb, 3: skipped */
					esc_html__( '%1$d %2$s, %3$d skipped (already set).', 'showtime-pools-core' ),
					(int) ( $text['options_written'] ?? 0 ),
					esc_html( $write ? __( 'written', 'showtime-pools-core' ) : __( 'would write', 'showtime-pools-core' ) ),
					(int) ( $text['options_skipped'] ?? 0 )
				);
				?>
			</p>

			<p style="margin:0.25rem 0;">
				<strong><?php esc_html_e( 'Images:', 'showtime-pools-core' ); ?></strong>
				<?php
				printf(
					/* translators: 1: imported count, 2: verb, 3: reused, 4: skipped, 5: missing */
					esc_html__( '%1$d %2$s, %3$d reused from Media Library, %4$d skipped (slot already set), %5$d missing source file.', 'showtime-pools-core' ),
					(int) ( $img['imported'] ?? 0 ),
					esc_html( $write ? __( 'imported', 'showtime-pools-core' ) : __( 'would import', 'showtime-pools-core' ) ),
					(int) ( $img['reused'] ?? 0 ),
					(int) ( $img['skipped'] ?? 0 ),
					(int) ( $img['missing'] ?? 0 )
				);
				?>
			</p>

			<?php if ( ! empty( $img['errors'] ) ) : ?>
				<p style="margin:0.5rem 0 0;color:#b00;">
					<strong><?php esc_html_e( 'Errors:', 'showtime-pools-core' ); ?></strong>
				</p>
				<ul style="margin:0.25rem 0 0 1.25rem;color:#b00;font-size:13px;">
					<?php foreach ( (array) $img['errors'] as $e ) : ?>
						<li><?php echo esc_html( (string) $e ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( ! empty( $text['by_page'] ) ) : ?>
				<details style="margin-top:0.75rem;">
					<summary style="cursor:pointer;color:#666;font-size:13px;">
						<?php esc_html_e( 'Per-page breakdown', 'showtime-pools-core' ); ?>
					</summary>
					<ul style="margin:0.5rem 0 0 1.25rem;color:#555;font-size:13px;">
						<?php foreach ( (array) $text['by_page'] as $label => $r ) : ?>
							<li>
								<?php echo esc_html( (string) $label ); ?> —
								<?php
								printf(
									/* translators: 1: written, 2: skipped */
									esc_html__( '%1$d %2$s, %3$d skipped', 'showtime-pools-core' ),
									(int) ( $r['written'] ?? 0 ),
									esc_html( $write ? __( 'written', 'showtime-pools-core' ) : __( 'would write', 'showtime-pools-core' ) ),
									(int) ( $r['skipped'] ?? 0 )
								);
								?>
							</li>
						<?php endforeach; ?>
					</ul>
				</details>
			<?php endif; ?>
		</div>
		<?php
	}
}
