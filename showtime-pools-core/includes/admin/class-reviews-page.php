<?php
/**
 * "Showtime Pools → Review Cache" admin submenu.
 *
 * P0-3. Curates the small set of reviews the theme renders as plain
 * server-side HTML, so crawlers that run no JavaScript still see real review
 * text instead of just the section heading.
 *
 * Registered as a submenu of the existing Showtime Pools menu, matching
 * ToolsPage / ContentPage / SettingsPage rather than scattering a page into
 * WP core's Tools menu.
 *
 * Three ways in, all manual and all requiring an explicit confirmation:
 *   1. Load a draft from the already-configured Trustindex shortcode (author +
 *      exact text only; ratings and dates are never guessed).
 *   2. Edit the draft rows — add a verified rating/date if, and only if, the
 *      administrator has checked them in the GBP or Trustindex dashboard.
 *   3. Paste a complete JSON payload when the values were verified elsewhere.
 *
 * There is no cron and no automatic sync: this repository has no authoritative
 * review API, so pretending to refresh weekly would be a lie.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Admin;

use Showtime\Reviews;

defined( 'ABSPATH' ) || exit;

final class ReviewsPage {

	private const PARENT_SLUG = 'showtime-settings';
	private const PAGE_SLUG   = 'showtime-review-cache';
	private const NONCE       = 'showtime_reviews_nonce';
	private const NONCE_ACT   = 'showtime_reviews_save';

	/** Draft rows survive a redirect in a short-lived per-user transient. */
	private const DRAFT_TRANSIENT = 'showtime_reviews_draft_';

	/** Notices survive the post-redirect-get in a short-lived transient. */
	private const NOTICE_TRANSIENT = 'showtime_reviews_notice_';

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 31 );
		add_action( 'admin_post_showtime_reviews_draft', array( $this, 'handle_draft' ) );
		add_action( 'admin_post_showtime_reviews_save', array( $this, 'handle_save' ) );
		add_action( 'admin_post_showtime_reviews_clear', array( $this, 'handle_clear' ) );
	}

	public function register_menu(): void {
		add_submenu_page(
			self::PARENT_SLUG,
			__( 'Review Cache', 'showtime-pools-core' ),
			__( 'Review Cache', 'showtime-pools-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render' )
		);
	}

	private function page_url(): string {
		return admin_url( 'admin.php?page=' . self::PAGE_SLUG );
	}

	private function set_notice( string $type, string $head, array $lines = array() ): void {
		set_transient(
			self::NOTICE_TRANSIENT . get_current_user_id(),
			array(
				'type'  => $type,
				'head'  => $head,
				'lines' => $lines,
			),
			60
		);
	}

	// ─────────────────────────────────────────────────────────────────────
	// Handlers
	// ─────────────────────────────────────────────────────────────────────

	/**
	 * Build a draft from the configured shortcode's local output.
	 *
	 * A parse failure never touches the saved cache — it only reports.
	 */
	public function handle_draft(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'showtime-pools-core' ) );
		}
		check_admin_referer( self::NONCE_ACT, self::NONCE );

		$markup = Reviews::render_widget_markup();
		if ( '' === $markup ) {
			$this->set_notice(
				'error',
				__( 'Could not load the review widget.', 'showtime-pools-core' ),
				array( __( 'The configured shortcode returned nothing — the widget plugin may be inactive. Your saved reviews were not changed.', 'showtime-pools-core' ) )
			);
			wp_safe_redirect( $this->page_url() );
			exit;
		}

		$candidates = Reviews::parse_trustindex_markup( $markup );
		if ( empty( $candidates ) ) {
			$this->set_notice(
				'error',
				__( 'No reviews found in the widget markup.', 'showtime-pools-core' ),
				array( __( 'The widget rendered, but no review blocks could be read from it. Your saved reviews were not changed.', 'showtime-pools-core' ) )
			);
			wp_safe_redirect( $this->page_url() );
			exit;
		}

		set_transient( self::DRAFT_TRANSIENT . get_current_user_id(), $candidates, 30 * MINUTE_IN_SECONDS );
		$this->set_notice(
			'success',
			sprintf(
				/* translators: %d: number of drafts */
				__( 'Loaded %d draft reviews. Ratings and dates are intentionally blank — fill them in only from your GBP or Trustindex dashboard.', 'showtime-pools-core' ),
				count( $candidates )
			)
		);
		wp_safe_redirect( $this->page_url() );
		exit;
	}

	/** Save either the row editor or a pasted JSON payload. */
	public function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'showtime-pools-core' ) );
		}
		check_admin_referer( self::NONCE_ACT, self::NONCE );

		if ( empty( $_POST['showtime_reviews_ack'] ) ) {
			$this->set_notice(
				'error',
				__( 'Nothing was saved.', 'showtime-pools-core' ),
				array( __( 'You must confirm the entries are genuine customer reviews before saving.', 'showtime-pools-core' ) )
			);
			wp_safe_redirect( $this->page_url() );
			exit;
		}

		$mode = isset( $_POST['save_mode'] ) ? sanitize_key( wp_unslash( $_POST['save_mode'] ) ) : 'rows';
		$rows = 'json' === $mode ? $this->rows_from_json() : $this->rows_from_editor();

		if ( is_string( $rows ) ) { // error message
			$this->set_notice( 'error', __( 'Nothing was saved.', 'showtime-pools-core' ), array( $rows ) );
			wp_safe_redirect( $this->page_url() );
			exit;
		}

		$result = Reviews::save( $rows );

		if ( ! $result['ok'] ) {
			$this->set_notice(
				'error',
				__( 'Nothing was saved — the previous cache is untouched.', 'showtime-pools-core' ),
				$result['errors']
			);
			wp_safe_redirect( $this->page_url() );
			exit;
		}

		delete_transient( self::DRAFT_TRANSIENT . get_current_user_id() );
		$this->set_notice(
			'success',
			sprintf( /* translators: %d: saved count */ __( 'Saved %d curated reviews.', 'showtime-pools-core' ), $result['count'] )
		);
		wp_safe_redirect( $this->page_url() );
		exit;
	}

	/** Remove the cache entirely; the theme falls back to Trustindex only. */
	public function handle_clear(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'showtime-pools-core' ) );
		}
		check_admin_referer( self::NONCE_ACT, self::NONCE );

		delete_option( Reviews::OPTION_CACHE );
		delete_option( Reviews::OPTION_SYNCED );
		delete_transient( self::DRAFT_TRANSIENT . get_current_user_id() );

		$this->set_notice(
			'success',
			__( 'Cleared the curated cache. Review sections now show the Trustindex widget only.', 'showtime-pools-core' )
		);
		wp_safe_redirect( $this->page_url() );
		exit;
	}

	/**
	 * Collect rows from the per-review editor.
	 *
	 * @return array<int,array<string,mixed>>|string Rows, or an error message.
	 */
	private function rows_from_editor() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in caller.
		$posted = isset( $_POST['review'] ) && is_array( $_POST['review'] ) ? wp_unslash( $_POST['review'] ) : array();
		if ( empty( $posted ) ) {
			return __( 'No review rows were submitted.', 'showtime-pools-core' );
		}

		$rows = array();
		foreach ( $posted as $r ) {
			if ( ! is_array( $r ) ) {
				continue;
			}
			if ( empty( $r['include'] ) ) {
				continue; // excluded by the administrator
			}
			$rows[] = array(
				'review_id'  => isset( $r['review_id'] ) ? (string) $r['review_id'] : '',
				'author'     => isset( $r['author'] ) ? (string) $r['author'] : '',
				'text'       => isset( $r['text'] ) ? (string) $r['text'] : '',
				'rating'     => isset( $r['rating'] ) && '' !== trim( (string) $r['rating'] ) ? (string) $r['rating'] : null,
				'date'       => isset( $r['date'] ) && '' !== trim( (string) $r['date'] ) ? (string) $r['date'] : null,
				'source'     => isset( $r['source'] ) && '' !== trim( (string) $r['source'] ) ? (string) $r['source'] : Reviews::ALLOWED_SOURCES[0],
				'source_url' => isset( $r['source_url'] ) && '' !== trim( (string) $r['source_url'] ) ? (string) $r['source_url'] : null,
			);
		}

		if ( empty( $rows ) ) {
			return __( 'No reviews were marked for inclusion.', 'showtime-pools-core' );
		}
		return $rows;
	}

	/**
	 * Decode the pasted JSON payload.
	 *
	 * @return array<int,mixed>|string Rows, or an error message.
	 */
	private function rows_from_json() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in caller.
		$raw = isset( $_POST['reviews_json'] ) ? (string) wp_unslash( $_POST['reviews_json'] ) : '';
		$raw = trim( $raw );
		if ( '' === $raw ) {
			return __( 'The JSON field was empty.', 'showtime-pools-core' );
		}
		// Bound the input so a huge paste can't be used to exhaust memory.
		if ( strlen( $raw ) > 200000 ) {
			return __( 'The JSON payload is too large.', 'showtime-pools-core' );
		}
		$decoded = json_decode( $raw, true );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			return sprintf(
				/* translators: %s: JSON parser message */
				__( 'The JSON could not be parsed: %s', 'showtime-pools-core' ),
				json_last_error_msg()
			);
		}
		return is_array( $decoded ) ? $decoded : __( 'The JSON must be an array of reviews.', 'showtime-pools-core' );
	}

	// ─────────────────────────────────────────────────────────────────────
	// View
	// ─────────────────────────────────────────────────────────────────────

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$uid    = get_current_user_id();
		$notice = get_transient( self::NOTICE_TRANSIENT . $uid );
		if ( $notice ) {
			delete_transient( self::NOTICE_TRANSIENT . $uid );
		}

		$cached = Reviews::get_cached();
		$synced = Reviews::synced_at();
		$draft  = get_transient( self::DRAFT_TRANSIENT . $uid );
		$draft  = is_array( $draft ) ? $draft : array();

		// The editor shows the draft when one exists, otherwise the live cache.
		$rows = array();
		if ( ! empty( $draft ) ) {
			foreach ( $draft as $d ) {
				$rows[] = array(
					'review_id'      => '',
					'author'         => (string) ( $d['author'] ?? '' ),
					'text'           => (string) ( $d['text'] ?? '' ),
					'rating'         => '',
					'date'           => '',
					'source'         => Reviews::ALLOWED_SOURCES[0],
					'source_url'     => '',
					'date_candidate' => isset( $d['date_candidate'] ) ? (string) $d['date_candidate'] : '',
					'include'        => false,
				);
			}
		} else {
			foreach ( $cached as $c ) {
				$rows[] = array(
					'review_id'      => (string) ( $c['review_id'] ?? '' ),
					'author'         => (string) ( $c['author'] ?? '' ),
					'text'           => (string) ( $c['text'] ?? '' ),
					'rating'         => isset( $c['rating'] ) && null !== $c['rating'] ? (string) $c['rating'] : '',
					'date'           => isset( $c['date'] ) && null !== $c['date'] ? (string) $c['date'] : '',
					'source'         => (string) ( $c['source'] ?? Reviews::ALLOWED_SOURCES[0] ),
					'source_url'     => isset( $c['source_url'] ) && null !== $c['source_url'] ? (string) $c['source_url'] : '',
					'date_candidate' => '',
					'include'        => true,
				);
			}
		}

		$action_url = admin_url( 'admin-post.php' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Showtime Review Cache', 'showtime-pools-core' ); ?></h1>

			<?php if ( is_array( $notice ) ) : ?>
				<div class="notice notice-<?php echo esc_attr( 'success' === ( $notice['type'] ?? '' ) ? 'success' : 'error' ); ?>" style="padding:12px 16px;">
					<p style="margin:0 0 .25rem;font-weight:600;"><?php echo esc_html( (string) ( $notice['head'] ?? '' ) ); ?></p>
					<?php if ( ! empty( $notice['lines'] ) ) : ?>
						<ul style="margin:.25rem 0 0 1.25rem;">
							<?php foreach ( (array) $notice['lines'] as $line ) : ?>
								<li><?php echo esc_html( (string) $line ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:20px;margin-top:1.25rem;max-width:960px;">
				<h2 style="margin-top:0;font-size:15px;"><?php esc_html_e( 'Status', 'showtime-pools-core' ); ?></h2>
				<p style="margin:.25rem 0;color:#555;">
					<strong><?php esc_html_e( 'Cached reviews:', 'showtime-pools-core' ); ?></strong>
					<?php echo (int) count( $cached ); ?>
					<?php
					printf(
						/* translators: %d: maximum */
						esc_html__( '(maximum %d)', 'showtime-pools-core' ),
						(int) Reviews::MAX_REVIEWS
					);
					?>
				</p>
				<p style="margin:.25rem 0;color:#555;">
					<strong><?php esc_html_e( 'Last saved:', 'showtime-pools-core' ); ?></strong>
					<?php
					echo $synced
						? esc_html( wp_date( 'Y-m-d H:i', $synced ) )
						: esc_html__( 'never', 'showtime-pools-core' );
					?>
				</p>
				<p style="margin:.5rem 0 0;color:#777;font-size:12px;">
					<?php esc_html_e( 'These reviews are saved manually. There is no automatic sync — no review API is connected to this site.', 'showtime-pools-core' ); ?>
				</p>

				<?php if ( ! empty( $cached ) ) : ?>
					<table class="widefat striped" style="margin-top:1rem;">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Author', 'showtime-pools-core' ); ?></th>
								<th style="width:90px;"><?php esc_html_e( 'Rating', 'showtime-pools-core' ); ?></th>
								<th style="width:120px;"><?php esc_html_e( 'Date', 'showtime-pools-core' ); ?></th>
								<th style="width:110px;"><?php esc_html_e( 'Source URL', 'showtime-pools-core' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $cached as $c ) : ?>
								<tr>
									<td><?php echo esc_html( (string) ( $c['author'] ?? '' ) ); ?></td>
									<td><?php echo isset( $c['rating'] ) && null !== $c['rating'] ? esc_html( (string) $c['rating'] ) : '<em style="color:#999;">' . esc_html__( 'omitted', 'showtime-pools-core' ) . '</em>'; ?></td>
									<td><?php echo isset( $c['date'] ) && null !== $c['date'] ? esc_html( (string) $c['date'] ) : '<em style="color:#999;">' . esc_html__( 'omitted', 'showtime-pools-core' ) . '</em>'; ?></td>
									<td><?php echo isset( $c['source_url'] ) && null !== $c['source_url'] ? esc_html__( 'yes', 'showtime-pools-core' ) : '<em style="color:#999;">' . esc_html__( 'omitted', 'showtime-pools-core' ) . '</em>'; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:20px;margin-top:1.25rem;max-width:960px;">
				<h2 style="margin-top:0;font-size:15px;"><?php esc_html_e( 'Load Trustindex draft', 'showtime-pools-core' ); ?></h2>
				<p style="color:#666;font-size:13px;margin:0 0 1rem;">
					<?php esc_html_e( 'Runs the configured review shortcode on this site and reads the author and exact review text out of the markup it returns. It does not contact Google or Trustindex. Ratings and dates are left blank on purpose: the widget markup renders five identical stars for every review and leaves the date empty until JavaScript runs, so neither can be trusted from it. Loading a draft never changes what is already saved.', 'showtime-pools-core' ); ?>
				</p>
				<form method="post" action="<?php echo esc_url( $action_url ); ?>">
					<?php wp_nonce_field( self::NONCE_ACT, self::NONCE ); ?>
					<input type="hidden" name="action" value="showtime_reviews_draft">
					<button type="submit" class="button button-secondary"><?php esc_html_e( 'Load draft from widget', 'showtime-pools-core' ); ?></button>
				</form>
			</div>

			<form method="post" action="<?php echo esc_url( $action_url ); ?>">
				<?php wp_nonce_field( self::NONCE_ACT, self::NONCE ); ?>
				<input type="hidden" name="action" value="showtime_reviews_save">

				<div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:20px;margin-top:1.25rem;max-width:960px;">
					<h2 style="margin-top:0;font-size:15px;"><?php esc_html_e( 'Review before saving', 'showtime-pools-core' ); ?></h2>
					<p style="color:#666;font-size:13px;">
						<?php
						printf(
							/* translators: 1: target count, 2: maximum */
							esc_html__( 'Include roughly %1$d reviews, up to a maximum of %2$d. Leave rating or date blank unless you have confirmed the exact value in the Google Business Profile or Trustindex dashboard — a blank field is simply omitted from the page, which is always better than a guess.', 'showtime-pools-core' ),
							8,
							(int) Reviews::MAX_REVIEWS
						);
						?>
					</p>

					<?php if ( empty( $rows ) ) : ?>
						<p style="color:#888;"><em><?php esc_html_e( 'No draft or saved reviews yet. Load a draft above, or paste JSON below.', 'showtime-pools-core' ); ?></em></p>
					<?php else : ?>
						<?php foreach ( $rows as $i => $r ) : ?>
							<div style="border:1px solid #e5e5e5;border-radius:8px;padding:14px;margin-bottom:12px;">
								<p style="margin:0 0 .5rem;">
									<label>
										<input type="checkbox" name="review[<?php echo (int) $i; ?>][include]" value="1" <?php checked( ! empty( $r['include'] ) ); ?>>
										<strong><?php esc_html_e( 'Include this review', 'showtime-pools-core' ); ?></strong>
									</label>
								</p>
								<input type="hidden" name="review[<?php echo (int) $i; ?>][review_id]" value="<?php echo esc_attr( (string) $r['review_id'] ); ?>">
								<p style="margin:.5rem 0;">
									<label style="display:block;font-weight:600;font-size:12px;"><?php esc_html_e( 'Author', 'showtime-pools-core' ); ?></label>
									<input type="text" class="large-text" name="review[<?php echo (int) $i; ?>][author]" value="<?php echo esc_attr( (string) $r['author'] ); ?>">
								</p>
								<p style="margin:.5rem 0;">
									<label style="display:block;font-weight:600;font-size:12px;"><?php esc_html_e( 'Exact review text', 'showtime-pools-core' ); ?></label>
									<textarea class="large-text" rows="4" name="review[<?php echo (int) $i; ?>][text]"><?php echo esc_textarea( (string) $r['text'] ); ?></textarea>
								</p>
								<p style="margin:.5rem 0;display:flex;gap:16px;flex-wrap:wrap;">
									<span>
										<label style="display:block;font-weight:600;font-size:12px;"><?php esc_html_e( 'Verified rating (1-5, optional)', 'showtime-pools-core' ); ?></label>
										<input type="number" min="1" max="5" step="1" name="review[<?php echo (int) $i; ?>][rating]" value="<?php echo esc_attr( (string) $r['rating'] ); ?>">
									</span>
									<span>
										<label style="display:block;font-weight:600;font-size:12px;"><?php esc_html_e( 'Verified date (YYYY-MM-DD, optional)', 'showtime-pools-core' ); ?></label>
										<input type="text" placeholder="YYYY-MM-DD" name="review[<?php echo (int) $i; ?>][date]" value="<?php echo esc_attr( (string) $r['date'] ); ?>">
										<?php if ( ! empty( $r['date_candidate'] ) ) : ?>
											<span style="display:block;font-size:11px;color:#b26b00;margin-top:2px;">
												<?php
												printf(
													/* translators: %s: unverified date */
													esc_html__( 'Unverified candidate from the widget: %s — confirm before using.', 'showtime-pools-core' ),
													esc_html( (string) $r['date_candidate'] )
												);
												?>
											</span>
										<?php endif; ?>
									</span>
									<span style="flex:1;min-width:240px;">
										<label style="display:block;font-weight:600;font-size:12px;"><?php esc_html_e( 'Source URL (HTTPS, optional)', 'showtime-pools-core' ); ?></label>
										<input type="url" class="large-text" name="review[<?php echo (int) $i; ?>][source_url]" value="<?php echo esc_attr( (string) $r['source_url'] ); ?>">
									</span>
								</p>
								<input type="hidden" name="review[<?php echo (int) $i; ?>][source]" value="<?php echo esc_attr( (string) $r['source'] ); ?>">
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:20px;margin-top:1.25rem;max-width:960px;">
					<h2 style="margin-top:0;font-size:15px;"><?php esc_html_e( 'Or paste a complete JSON payload', 'showtime-pools-core' ); ?></h2>
					<p style="color:#666;font-size:13px;">
						<?php esc_html_e( 'Use this when the rating and date were verified in the dashboard. Choosing this mode ignores the rows above. An invalid payload is rejected whole — the current cache is never partially overwritten.', 'showtime-pools-core' ); ?>
					</p>
					<textarea name="reviews_json" class="large-text code" rows="8" placeholder='[{"review_id":"","author":"","rating":null,"date":null,"text":"","source":"Google Business Profile","source_url":null}]'></textarea>
				</div>

				<div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:20px;margin-top:1.25rem;max-width:960px;">
					<p style="margin:0 0 1rem;">
						<label style="display:flex;gap:8px;align-items:flex-start;">
							<input type="checkbox" name="showtime_reviews_ack" value="1" style="margin-top:3px;">
							<span><?php esc_html_e( 'I confirm the selected entries are genuine customer reviews and are not reviews from an owner, employee, contractor, agency, family member, or another material relationship.', 'showtime-pools-core' ); ?></span>
						</label>
					</p>
					<p style="display:flex;gap:10px;flex-wrap:wrap;margin:0;">
						<button type="submit" name="save_mode" value="rows" class="button button-primary button-large"><?php esc_html_e( 'Save selected rows', 'showtime-pools-core' ); ?></button>
						<button type="submit" name="save_mode" value="json" class="button button-secondary button-large"><?php esc_html_e( 'Save pasted JSON', 'showtime-pools-core' ); ?></button>
					</p>
				</div>
			</form>

			<form method="post" action="<?php echo esc_url( $action_url ); ?>" style="margin-top:1.25rem;max-width:960px;">
				<?php wp_nonce_field( self::NONCE_ACT, self::NONCE ); ?>
				<input type="hidden" name="action" value="showtime_reviews_clear">
				<button type="submit" class="button button-link-delete"
					onclick="return confirm('<?php echo esc_js( __( 'Remove all curated reviews? Review sections will show only the Trustindex widget.', 'showtime-pools-core' ) ); ?>');">
					<?php esc_html_e( 'Clear curated cache', 'showtime-pools-core' ); ?>
				</button>
			</form>
		</div>
		<?php
	}
}
