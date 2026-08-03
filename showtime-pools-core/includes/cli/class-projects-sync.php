<?php
/**
 * WP-CLI: reconcile WordPress routing shells with the code project registry.
 *
 *   wp showtime projects sync --dry-run   # default — reports, writes nothing
 *   wp showtime projects sync --apply     # creates missing routing posts
 *
 * The registry is authoritative for CONTENT; this command only ensures a
 * published `project` post exists so the permalink, sitemap entry and
 * WP_Query behaviour work. It never deletes a post, never changes an existing
 * slug, and never overwrites unrelated content. Idempotent: a second run with
 * no registry changes reports zero actions.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Cli;

use Showtime\Projects;

defined( 'ABSPATH' ) || exit;

final class ProjectsSync {

	/** Validation problems found in the registry itself. */
	private array $errors = array();

	public function register(): void {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'showtime projects sync', array( $this, 'sync' ) );
		}
	}

	/**
	 * Validate the registry, then report or apply routing-shell changes.
	 *
	 * ## OPTIONS
	 *
	 * [--apply]
	 * : Write changes. Without this flag the command is a dry run.
	 *
	 * [--dry-run]
	 * : Explicit no-op default. Accepted for symmetry.
	 */
	public function sync( $args, $assoc ): void {
		$apply = ! empty( $assoc['apply'] );

		$managed = $this->managed_entries();
		if ( empty( $managed ) ) {
			\WP_CLI::error( 'No managed projects found in the registry.' );
		}

		$this->validate( $managed );
		if ( ! empty( $this->errors ) ) {
			foreach ( $this->errors as $e ) {
				\WP_CLI::log( '  ✘ ' . $e );
			}
			\WP_CLI::error( sprintf( 'Registry validation failed with %d problem(s). Nothing was written.', count( $this->errors ) ) );
		}
		\WP_CLI::log( sprintf( '✔ Registry valid — %d managed project(s).', count( $managed ) ) );

		$planned = 0;
		foreach ( $managed as $entry ) {
			$slug     = (string) $entry['slug'];
			$existing = get_page_by_path( $slug, OBJECT, 'project' );

			if ( $existing instanceof \WP_Post ) {
				// Existing shell: never rename, never rewrite content. Only report
				// a non-published status, which would hide the project.
				if ( 'publish' !== $existing->post_status ) {
					++$planned;
					\WP_CLI::log( sprintf( '  ~ %s: status is "%s" (expected publish)', $slug, $existing->post_status ) );
					if ( $apply ) {
						wp_update_post( array( 'ID' => $existing->ID, 'post_status' => 'publish' ) );
						\WP_CLI::log( '    → published' );
					}
				} else {
					\WP_CLI::log( sprintf( '  = %s: routing post OK (ID %d)', $slug, $existing->ID ) );
				}
				continue;
			}

			++$planned;
			\WP_CLI::log( sprintf( '  + %s: routing post MISSING — would create', $slug ) );
			if ( $apply ) {
				$new_id = wp_insert_post(
					array(
						'post_type'   => 'project',
						'post_name'   => $slug,
						'post_title'  => (string) ( $entry['title'] ?? $slug ),
						'post_status' => 'publish',
						'post_content'=> '',
					),
					true
				);
				if ( is_wp_error( $new_id ) ) {
					\WP_CLI::error( sprintf( 'Failed creating %s: %s', $slug, $new_id->get_error_message() ) );
				}
				\WP_CLI::log( sprintf( '    → created (ID %d)', $new_id ) );
			}
		}

		if ( 0 === $planned ) {
			\WP_CLI::success( 'Everything already in sync — no changes needed.' );
			return;
		}
		if ( $apply ) {
			\WP_CLI::success( sprintf( 'Applied %d change(s).', $planned ) );
		} else {
			\WP_CLI::success( sprintf( 'Dry run: %d change(s) would be applied. Re-run with --apply to write.', $planned ) );
		}
	}

	/** @return array<int,array<string,mixed>> */
	private function managed_entries(): array {
		$out = array();
		foreach ( Projects::all() as $e ) {
			if ( ! empty( $e['managed'] ) ) {
				$out[] = $e;
			}
		}
		return $out;
	}

	/**
	 * Registry validation. Every failure is collected so one run reports all
	 * problems rather than stopping at the first.
	 */
	private function validate( array $managed ): void {
		$slugs  = array();
		$titles = array();
		$images = array();
		$dir    = defined( 'SHOWTIME_CHILD_DIR' )
			? SHOWTIME_CHILD_DIR . '/assets/img/projects/comparisons/'
			: '';

		foreach ( $managed as $e ) {
			$slug = (string) ( $e['slug'] ?? '' );
			$tag  = '' !== $slug ? $slug : '(missing slug)';

			if ( '' === $slug ) {
				$this->errors[] = 'An entry has no slug.';
				continue;
			}
			if ( isset( $slugs[ $slug ] ) ) {
				$this->errors[] = "$tag: duplicate slug.";
			}
			$slugs[ $slug ] = true;

			$title = trim( (string) ( $e['title'] ?? '' ) );
			if ( '' === $title ) {
				$this->errors[] = "$tag: title is required.";
			} elseif ( isset( $titles[ $title ] ) ) {
				$this->errors[] = "$tag: duplicate title \"$title\".";
			}
			$titles[ $title ] = true;

			// Internal link targets must resolve in the registries.
			$svc = trim( (string) preg_replace( '#^/services/#', '', (string) ( $e['service_url'] ?? '' ) ), '/' );
			if ( '' === $svc || ! class_exists( 'Showtime\Services' ) || null === \Showtime\Services::get( $svc ) ) {
				$this->errors[] = "$tag: service_url does not resolve to a registered service.";
			}
			$area = trim( (string) preg_replace( '#^/service-areas/#', '', (string) ( $e['area_url'] ?? '' ) ), '/' );
			if ( '' === $area || ! class_exists( 'Showtime\Areas' ) || null === \Showtime\Areas::get( $area ) ) {
				$this->errors[] = "$tag: area_url does not resolve to a registered service area.";
			}

			// Images: present, readable, landscape, distinct, never reused.
			$pair = array();
			foreach ( array( 'before_image', 'after_image', 'hero_image', 'og_image' ) as $key ) {
				$file = (string) ( $e[ $key ] ?? '' );
				if ( '' === $file ) {
					$this->errors[] = "$tag: $key is required.";
					continue;
				}
				$path = $dir . basename( $file );
				if ( '' === $dir || ! is_readable( $path ) ) {
					$this->errors[] = "$tag: $key file not found ($file).";
					continue;
				}
				$size = @getimagesize( $path );
				if ( ! is_array( $size ) ) {
					$this->errors[] = "$tag: $key is not a readable image.";
					continue;
				}
				if ( $size[0] <= $size[1] ) {
					$this->errors[] = "$tag: $key is not landscape ({$size[0]}x{$size[1]}).";
				}
				if ( in_array( $key, array( 'before_image', 'after_image' ), true ) ) {
					$pair[ $key ] = hash_file( 'sha256', $path );
					if ( isset( $images[ $pair[ $key ] ] ) && $images[ $pair[ $key ] ] !== $slug ) {
						$this->errors[] = "$tag: $key reuses an image from {$images[ $pair[ $key ] ]}.";
					}
					$images[ $pair[ $key ] ] = $slug;
				}
			}
			if ( isset( $pair['before_image'], $pair['after_image'] ) && $pair['before_image'] === $pair['after_image'] ) {
				$this->errors[] = "$tag: before and after are the same file.";
			}

			// Alt text on every rendered image.
			foreach ( array( 'before_alt', 'after_alt', 'hero_alt' ) as $key ) {
				if ( '' === trim( (string) ( $e[ $key ] ?? '' ) ) ) {
					$this->errors[] = "$tag: $key must not be empty.";
				}
			}

			// Optional completion date must be a real YYYY-MM when supplied.
			$cd = trim( (string) ( $e['completion_date'] ?? '' ) );
			if ( '' !== $cd && ! preg_match( '/^\d{4}-(0[1-9]|1[0-2])$/', $cd ) ) {
				$this->errors[] = "$tag: completion_date must be YYYY-MM or blank.";
			}

			// Investment must read as a RANGE, never a single contract figure.
			$inv = trim( (string) ( $e['investment'] ?? '' ) );
			if ( '' !== $inv ) {
				if ( ! preg_match( '/^\$[\d,]+\s*[–-]\s*\$[\d,]+$/u', $inv ) ) {
					$this->errors[] = "$tag: investment must be a range like \$10,000–\$25,000 (got \"$inv\").";
				}
			}

			// SEO fields required for a managed project.
			foreach ( array( 'seo_title', 'meta_description', 'excerpt', 'comparison_heading', 'comparison_summary' ) as $key ) {
				if ( '' === trim( (string) ( $e[ $key ] ?? '' ) ) ) {
					$this->errors[] = "$tag: $key must not be empty.";
				}
			}

			// No review/rating data may live in the registry.
			$blob = strtolower( wp_json_encode( $e ) );
			foreach ( array( 'aggregaterating', 'ratingvalue', 'reviewcount', '"review"' ) as $needle ) {
				if ( false !== strpos( $blob, $needle ) ) {
					$this->errors[] = "$tag: contains review/rating data ($needle).";
				}
			}
		}
	}
}
