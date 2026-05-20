<?php
/**
 * Project CPT — Showtime Pools portfolio.
 *
 * Slug   : `project`
 * Archive: false (a /projects/ page-projects.php hub handles the archive
 *          UI; this CPT only registers the singular). Singles default to
 *          /projects/<slug>/ because rewrite is parented to 'projects'.
 *
 * Mapbox / REST integration (Phase 2A) reads the same post type via the
 * REST endpoint exposed by `show_in_rest`.
 *
 * Project meta lives in ACF (group_project_meta.json):
 *   - neighborhood, completion_date, finish, scope
 *   - value_label, duration_label, client_name, client_quote
 *   - before_image, after_image, gallery
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Cpt;

defined( 'ABSPATH' ) || exit;

final class Project {

	public const POST_TYPE = 'project';
	public const TAXONOMY  = 'project_service';

	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ), 5 );
		add_action( 'init', array( $this, 'register_taxonomy' ), 5 );
		add_filter( 'showtime/image/slot_for_project', array( $this, 'slot_for_project' ), 10, 2 );
	}

	public function register_post_type(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'               => __( 'Projects', 'showtime-pools-core' ),
					'singular_name'      => __( 'Project', 'showtime-pools-core' ),
					'menu_name'          => __( 'Projects', 'showtime-pools-core' ),
					'add_new'            => __( 'Add Project', 'showtime-pools-core' ),
					'add_new_item'       => __( 'Add New Project', 'showtime-pools-core' ),
					'edit_item'          => __( 'Edit Project', 'showtime-pools-core' ),
					'view_item'          => __( 'View Project', 'showtime-pools-core' ),
					'search_items'       => __( 'Search Projects', 'showtime-pools-core' ),
					'not_found'          => __( 'No projects found.', 'showtime-pools-core' ),
				),
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => true,
				'show_in_rest'        => true,
				'rest_base'           => 'projects',
				'menu_icon'           => 'dashicons-format-image',
				'menu_position'       => 24,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'has_archive'         => false, // /projects/ page-projects.php is the archive hub
				'hierarchical'        => false,
				'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'rewrite'             => array(
					'slug'       => 'projects',
					'with_front' => false,
				),
				'taxonomies'          => array( self::TAXONOMY ),
			)
		);
	}

	public function register_taxonomy(): void {
		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'Service categories', 'showtime-pools-core' ),
					'singular_name' => __( 'Service category', 'showtime-pools-core' ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'rewrite'           => array(
					'slug'       => 'projects/service',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Resolve a stable photo slot for a project (`project_1`, `project_2`, ...)
	 * based on its menu_order. Templates can use:
	 *     showtime_image( apply_filters( 'showtime/image/slot_for_project', 'project_1', $post_id ) );
	 *
	 * Lets the seeded demo projects align deterministically with bundled photos.
	 */
	public function slot_for_project( string $default, int $post_id ): string {
		$order = (int) get_post_field( 'menu_order', $post_id );
		if ( $order < 1 ) { $order = 1; }
		if ( $order > 8 ) { $order = ( ( $order - 1 ) % 8 ) + 1; }
		return 'project_' . $order;
	}
}
