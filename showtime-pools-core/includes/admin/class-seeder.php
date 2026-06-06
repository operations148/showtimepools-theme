<?php
/**
 * One-time content + image seeder.
 *
 * Writes the inline PHP fallback defaults declared in each page template into
 * the matching post_meta keys, and imports bundled /assets/img/ files into the
 * Media Library for empty Site Images slots. Idempotent: every write is gated
 * by an "already set" check, so re-runs are zero-cost.
 *
 * Three-layer image idempotency:
 *   1. wp_options[showtime_img_<slot>] non-empty → skip slot entirely.
 *   2. attachment meta _showtime_seed_hash matches source SHA-256 → reuse existing ID.
 *   3. fresh sideload → stamp provenance meta (hash, slot, source path).
 *
 * Entry points:
 *   - Admin: Showtime Pools → Tools (Dry run / Run seeder buttons).
 *   - WP-CLI: `wp showtime seed-all [--write] [--section=text|images]`.
 *
 * Does NOT touch: post type 'post' (blog), post type 'project' (CPT entries),
 * the site logo, or any attachment that lacks the seeder provenance meta.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Admin;

defined( 'ABSPATH' ) || exit;

final class Seeder {

	public const ADMIN_ACTION  = 'showtime_seed_run';
	public const ATTACH_HASH   = '_showtime_seed_hash';
	public const ATTACH_SLOT   = '_showtime_seed_slot';
	public const ATTACH_SOURCE = '_showtime_seed_source';

	/** @var array<string, mixed>|null */
	private static ?array $defaults_cache = null;

	/** @var array<string, string>|null */
	private static ?array $slots_cache = null;

	public function register(): void {
		add_action( 'admin_post_' . self::ADMIN_ACTION, array( $this, 'handle_admin_post' ) );

		// Register the WP-CLI command only when the CLI runtime is loaded.
		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			\WP_CLI::add_command( 'showtime seed-all', array( $this, 'cli_seed_all' ) );
		}
	}

	// ─── DATA LOADERS ──────────────────────────────────────────────────────────

	/** @return array<string, mixed> */
	private static function defaults(): array {
		if ( null !== self::$defaults_cache ) {
			return self::$defaults_cache;
		}
		$path                  = SHOWTIME_CORE_DIR . 'includes/admin/data/page-defaults.php';
		self::$defaults_cache  = file_exists( $path ) ? (array) require $path : array();
		return self::$defaults_cache;
	}

	/** @return array<string, string> */
	private static function slots(): array {
		if ( null !== self::$slots_cache ) {
			return self::$slots_cache;
		}
		$path              = SHOWTIME_CORE_DIR . 'includes/admin/data/image-slots.php';
		self::$slots_cache = file_exists( $path ) ? (array) require $path : array();
		return self::$slots_cache;
	}

	// ─── PUBLIC API ────────────────────────────────────────────────────────────

	/**
	 * Run both seeders.
	 *
	 * @param bool $write True = actually writes to DB. False = dry-run report only.
	 * @return array{text: array, images: array}
	 */
	public function seed_all( bool $write = false ): array {
		return array(
			'text'   => $this->seed_text( $write ),
			'images' => $this->seed_images( $write ),
		);
	}

	/**
	 * Seed text content (per-page post_meta + sitewide wp_options).
	 *
	 * @return array{written:int, skipped:int, by_page:array<string,array{written:int,skipped:int}>, options_written:int, options_skipped:int}
	 */
	public function seed_text( bool $write = false ): array {
		$defaults = self::defaults();
		$written  = 0;
		$skipped  = 0;
		$by_page  = array();

		// Single-instance templates.
		foreach ( (array) ( $defaults['templates'] ?? array() ) as $template => $fields ) {
			$pages = $this->find_pages_by_template( $template );
			foreach ( $pages as $page_id ) {
				$page_result = $this->write_page_meta( $page_id, (array) $fields, $write );
				$written    += $page_result['written'];
				$skipped    += $page_result['skipped'];
				$by_page[ $template . ' (#' . $page_id . ')' ] = $page_result;
			}
		}

		// Multi-instance: area pages (resolve defaults from registry).
		foreach ( $this->find_pages_by_template( 'page-area.php' ) as $page_id ) {
			$slug = $this->resolve_area_slug( $page_id );
			if ( '' === $slug ) {
				continue;
			}
			$area = class_exists( '\\Showtime\\Areas' ) ? \Showtime\Areas::get( $slug ) : null;
			if ( ! $area ) {
				continue;
			}
			$name   = (string) ( $area['name'] ?? '' );
			$fields = array(
				'area_h1'          => (string) ( $area['seo_h1']    ?? '' ) ?: ( '' !== $name ? sprintf( 'Pool service in %s.', $name ) : '' ),
				'area_lead'        => (string) ( $area['seo_intro'] ?? '' ) ?: (string) ( $area['lead'] ?? '' ),
				'area_what_common' => '' !== $name ? sprintf( 'What %s pools have in common.', $name ) : 'What these pools have in common.',
				'area_what_do'     => 'What we do here most.',
			);
			$fields      = array_filter( $fields, static fn( $v ) => '' !== $v );
			$page_result = $this->write_page_meta( $page_id, $fields, $write );
			$written    += $page_result['written'];
			$skipped    += $page_result['skipped'];
			$by_page[ 'page-area.php — ' . $slug . ' (#' . $page_id . ')' ] = $page_result;
		}

		// Multi-instance: inspection pages.
		foreach ( $this->find_pages_by_template( 'page-inspection.php' ) as $page_id ) {
			$slug = $this->resolve_inspection_slug( $page_id );
			if ( '' === $slug ) {
				continue;
			}
			$insp = class_exists( '\\Showtime\\Inspections' ) ? \Showtime\Inspections::get( $slug ) : null;
			if ( ! $insp ) {
				continue;
			}
			$fields      = array(
				'insp_h1'         => (string) ( $insp['name'] ?? '' ),
				'insp_lead'       => (string) ( $insp['lead'] ?? '' ),
				'insp_who_h2'     => 'Right inspection, right moment.',
				'insp_what_h2'    => 'Every inspection includes the following.',
				'insp_process_h2' => 'Four steps from booking to written report.',
				'insp_faq_h2'     => 'Common questions about this inspection.',
			);
			$fields      = array_filter( $fields, static fn( $v ) => '' !== $v );
			$page_result = $this->write_page_meta( $page_id, $fields, $write );
			$written    += $page_result['written'];
			$skipped    += $page_result['skipped'];
			$by_page[ 'page-inspection.php — ' . $slug . ' (#' . $page_id . ')' ] = $page_result;
		}

		// Multi-instance: iframe pages (quote / book).
		$iframe_kinds = (array) ( $defaults['iframe_kinds'] ?? array() );
		foreach ( $this->find_pages_by_template( 'page-iframe.php' ) as $page_id ) {
			$kind = (string) get_post_meta( $page_id, '_showtime_iframe_kind', true );
			if ( '' === $kind ) {
				$kind = (string) get_post_field( 'post_name', $page_id );
			}
			$fields = (array) ( $iframe_kinds[ $kind ] ?? array() );
			if ( empty( $fields ) ) {
				continue;
			}
			$page_result = $this->write_page_meta( $page_id, $fields, $write );
			$written    += $page_result['written'];
			$skipped    += $page_result['skipped'];
			$by_page[ 'page-iframe.php — ' . $kind . ' (#' . $page_id . ')' ] = $page_result;
		}

		// Multi-instance: legal pages (privacy-policy / terms), keyed by slug.
		$legal_pages = (array) ( $defaults['legal_pages'] ?? array() );
		foreach ( $this->find_pages_by_template( 'page-legal.php' ) as $page_id ) {
			$slug   = (string) get_post_field( 'post_name', $page_id );
			$fields = (array) ( $legal_pages[ $slug ] ?? array() );
			if ( empty( $fields ) ) {
				continue;
			}
			$page_result = $this->write_page_meta( $page_id, $fields, $write );
			$written    += $page_result['written'];
			$skipped    += $page_result['skipped'];
			$by_page[ 'page-legal.php - ' . $slug . ' (#' . $page_id . ')' ] = $page_result;
		}

		// Sitewide wp_options (Site Content → Team + Certifications tabs).
		$options_written = 0;
		$options_skipped = 0;
		$options         = (array) ( $defaults['options'] ?? array() );

		foreach ( (array) ( $options['team'] ?? array() ) as $n => $member ) {
			foreach ( array( 'name', 'role', 'initials', 'note', 'href' ) as $field ) {
				$key = ContentPage::PREFIX . "team_{$n}_{$field}";
				$res = $this->maybe_write_option( $key, (string) ( $member[ $field ] ?? '' ), $write );
				$options_written += $res['written'];
				$options_skipped += $res['skipped'];
			}
		}

		foreach ( (array) ( $options['creds'] ?? array() ) as $n => $cred ) {
			foreach ( array( 'title', 'body' ) as $field ) {
				$key = ContentPage::PREFIX . "cred_{$n}_{$field}";
				$res = $this->maybe_write_option( $key, (string) ( $cred[ $field ] ?? '' ), $write );
				$options_written += $res['written'];
				$options_skipped += $res['skipped'];
			}
		}

		return array(
			'written'         => $written,
			'skipped'         => $skipped,
			'by_page'         => $by_page,
			'options_written' => $options_written,
			'options_skipped' => $options_skipped,
		);
	}

	/**
	 * Import bundled images into the Media Library and assign attachment IDs to
	 * empty Site Images slots.
	 *
	 * @return array{imported:int, reused:int, skipped:int, missing:int, errors:list<string>, per_slot:array<string,string>}
	 */
	public function seed_images( bool $write = false ): array {
		$slots    = self::slots();
		$imported = 0;
		$reused   = 0;
		$skipped  = 0;
		$missing  = 0;
		$errors   = array();
		$per_slot = array();

		// Resolve the child theme img dir. If the constant is missing the theme
		// is not active and we cannot find the bundled files.
		$child_dir = defined( 'SHOWTIME_CHILD_DIR' ) ? SHOWTIME_CHILD_DIR : '';
		if ( '' === $child_dir ) {
			$theme     = function_exists( 'wp_get_theme' ) ? wp_get_theme() : null;
			$child_dir = $theme ? trailingslashit( get_stylesheet_directory() ) : '';
		}
		if ( '' === $child_dir ) {
			return array(
				'imported' => 0, 'reused' => 0, 'skipped' => 0, 'missing' => count( $slots ),
				'errors'   => array( 'Child theme directory not resolvable; nothing to seed.' ),
				'per_slot' => array(),
			);
		}

		foreach ( $slots as $slot => $rel_path ) {
			$opt_key = SettingsPage::slot_to_option( $slot );

			// LAYER 1: option already set → skip.
			$current = (string) get_option( $opt_key, '' );
			if ( '' !== $current ) {
				$skipped++;
				$per_slot[ $slot ] = 'skipped (option set)';
				continue;
			}

			$source_path = trailingslashit( $child_dir ) . $rel_path;
			if ( ! file_exists( $source_path ) ) {
				$missing++;
				$per_slot[ $slot ] = 'missing source';
				continue;
			}

			$hash = (string) hash_file( 'sha256', $source_path );

			// LAYER 2: existing attachment with same hash → reuse.
			$existing_id = $this->find_attachment_by_hash( $hash );
			if ( $existing_id > 0 ) {
				if ( $write ) {
					update_option( $opt_key, $existing_id, false );
					update_post_meta( $existing_id, self::ATTACH_SLOT, $slot );
				}
				$reused++;
				$per_slot[ $slot ] = 'reused attachment #' . $existing_id;
				continue;
			}

			// LAYER 3: fresh sideload.
			if ( ! $write ) {
				$imported++;
				$per_slot[ $slot ] = 'would import (' . $rel_path . ')';
				continue;
			}

			$id = $this->sideload_local_file( $source_path, $slot, $hash, $rel_path );
			if ( $id instanceof \WP_Error || $id <= 0 ) {
				$errors[] = $slot . ': ' . ( $id instanceof \WP_Error ? $id->get_error_message() : 'unknown sideload failure' );
				$per_slot[ $slot ] = 'error';
				continue;
			}
			update_option( $opt_key, $id, false );
			$imported++;
			$per_slot[ $slot ] = 'imported attachment #' . $id;
		}

		return array(
			'imported' => $imported,
			'reused'   => $reused,
			'skipped'  => $skipped,
			'missing'  => $missing,
			'errors'   => $errors,
			'per_slot' => $per_slot,
		);
	}

	// ─── INTERNAL: TEXT WRITERS ────────────────────────────────────────────────

	/**
	 * @param array<string,string|int|float|null> $fields
	 * @return array{written:int, skipped:int}
	 */
	private function write_page_meta( int $page_id, array $fields, bool $write ): array {
		$w = 0;
		$s = 0;
		foreach ( $fields as $key => $value ) {
			$value = (string) $value;
			if ( '' === $value ) {
				continue;
			}
			$current = (string) get_post_meta( $page_id, $key, true );
			if ( '' !== $current ) {
				$s++;
				continue;
			}
			if ( $write ) {
				update_post_meta( $page_id, $key, $value );
			}
			$w++;
		}
		return array( 'written' => $w, 'skipped' => $s );
	}

	/**
	 * @return array{written:int, skipped:int}
	 */
	private function maybe_write_option( string $key, string $value, bool $write ): array {
		if ( '' === $value ) {
			return array( 'written' => 0, 'skipped' => 0 );
		}
		$current = (string) get_option( $key, '' );
		if ( '' !== $current ) {
			return array( 'written' => 0, 'skipped' => 1 );
		}
		if ( $write ) {
			update_option( $key, $value, false );
		}
		return array( 'written' => 1, 'skipped' => 0 );
	}

	// ─── INTERNAL: PAGE RESOLUTION ─────────────────────────────────────────────

	/** @return list<int> */
	private function find_pages_by_template( string $template ): array {
		$q = new \WP_Query(
			array(
				'post_type'      => 'page',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'   => '_wp_page_template',
						'value' => $template,
					),
				),
			)
		);
		return array_map( 'intval', (array) $q->posts );
	}

	private function resolve_area_slug( int $page_id ): string {
		$slug = (string) get_post_meta( $page_id, '_showtime_area_slug', true );
		return '' !== $slug ? $slug : (string) get_post_field( 'post_name', $page_id );
	}

	private function resolve_inspection_slug( int $page_id ): string {
		$slug = (string) get_post_meta( $page_id, '_showtime_inspection_slug', true );
		return '' !== $slug ? $slug : (string) get_post_field( 'post_name', $page_id );
	}

	// ─── INTERNAL: IMAGE IMPORT ────────────────────────────────────────────────

	private function find_attachment_by_hash( string $hash ): int {
		$q = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'   => self::ATTACH_HASH,
						'value' => $hash,
					),
				),
			)
		);
		$ids = (array) $q->posts;
		return isset( $ids[0] ) ? (int) $ids[0] : 0;
	}

	/**
	 * @return int|\WP_Error attachment ID on success, WP_Error on failure.
	 */
	private function sideload_local_file( string $source_path, string $slot, string $hash, string $rel_path ) {
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$basename = wp_basename( $source_path );
		$tmp      = wp_tempnam( $basename );
		if ( ! $tmp ) {
			return new \WP_Error( 'tempnam', 'Could not create temp file.' );
		}
		if ( ! copy( $source_path, $tmp ) ) {
			@unlink( $tmp );
			return new \WP_Error( 'copy', 'Could not copy source into temp.' );
		}

		$file_array = array(
			'name'     => 'seed_' . $basename,
			'tmp_name' => $tmp,
		);

		$attach_id = media_handle_sideload( $file_array, 0, null, array(
			'post_title' => 'Showtime Pools — ' . $slot,
		) );

		if ( $attach_id instanceof \WP_Error ) {
			@unlink( $tmp );
			return $attach_id;
		}

		update_post_meta( $attach_id, self::ATTACH_HASH,   $hash );
		update_post_meta( $attach_id, self::ATTACH_SLOT,   $slot );
		update_post_meta( $attach_id, self::ATTACH_SOURCE, $rel_path );

		return (int) $attach_id;
	}

	// ─── ENTRY: ADMIN-POST HANDLER ─────────────────────────────────────────────

	public function handle_admin_post(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'showtime-pools-core' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::ADMIN_ACTION );

		$write   = isset( $_POST['write'] ) && '1' === $_POST['write'];
		$results = $this->seed_all( $write );

		set_transient(
			'showtime_seed_result_' . get_current_user_id(),
			array( 'write' => $write, 'results' => $results, 'at' => time() ),
			60
		);

		$redirect = admin_url( 'admin.php?page=showtime-tools&seeded=1' );
		wp_safe_redirect( $redirect );
		exit;
	}

	// ─── ENTRY: WP-CLI ─────────────────────────────────────────────────────────

	/**
	 * `wp showtime seed-all [--write] [--section=text|images]`
	 *
	 * @param array<int,string>      $args
	 * @param array<string,string>   $assoc
	 */
	public function cli_seed_all( array $args, array $assoc ): void {
		$write   = isset( $assoc['write'] );
		$section = isset( $assoc['section'] ) ? (string) $assoc['section'] : 'all';

		\WP_CLI::log( $write ? '⚡ Writing to DB.' : '🔍 Dry run (use --write to commit).' );

		if ( 'images' !== $section ) {
			\WP_CLI::log( '— Text seeder —' );
			$text = $this->seed_text( $write );
			\WP_CLI::log( sprintf(
				'  Page meta:    %d %s, %d skipped (already set)',
				$text['written'],
				$write ? 'written' : 'would write',
				$text['skipped']
			) );
			\WP_CLI::log( sprintf(
				'  Sitewide:     %d %s, %d skipped (already set)',
				$text['options_written'],
				$write ? 'written' : 'would write',
				$text['options_skipped']
			) );
			foreach ( $text['by_page'] as $label => $r ) {
				\WP_CLI::log( sprintf( '    · %s — %d / %d skipped', $label, $r['written'], $r['skipped'] ) );
			}
		}

		if ( 'text' !== $section ) {
			\WP_CLI::log( '— Image seeder —' );
			$img = $this->seed_images( $write );
			\WP_CLI::log( sprintf(
				'  Imports:      %d %s, %d reused, %d skipped (slot set), %d missing source',
				$img['imported'],
				$write ? 'imported' : 'would import',
				$img['reused'],
				$img['skipped'],
				$img['missing']
			) );
			if ( ! empty( $img['errors'] ) ) {
				foreach ( $img['errors'] as $e ) {
					\WP_CLI::warning( $e );
				}
			}
		}

		\WP_CLI::success( $write ? 'Seeding complete.' : 'Dry run complete.' );
	}
}
