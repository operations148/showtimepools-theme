<?php
/**
 * FluentForms bridge. When FF is active, every successful submission is
 * forwarded to GHL via the central Ghl integration so we don't end up with
 * two webhook configurations.
 *
 * Hook is no-op when FluentForms isn't installed.
 *
 * @package ShowtimePoolsCore
 */

namespace Showtime\Integrations;

defined( 'ABSPATH' ) || exit;

final class FluentForms {

	public function register(): void {
		// FluentForms 4.x+ uses the slash hook namespace.
		add_action( 'fluentform/submission_inserted', array( $this, 'on_submission' ), 10, 3 );
		// Legacy fallback for older FF installs.
		add_action( 'fluentform_submission_inserted', array( $this, 'on_submission' ), 10, 3 );
	}

	/**
	 * @param int|string $entry_id
	 * @param array<string, mixed> $form_data Sanitized submission values keyed by field name.
	 * @param object|null $form FF form object (id, title, settings).
	 */
	public function on_submission( $entry_id, $form_data, $form = null ): void {
		$form_id    = is_object( $form ) && isset( $form->id ) ? (int) $form->id : 0;
		$form_title = is_object( $form ) && isset( $form->title ) ? (string) $form->title : '';

		// Allow per-form opt-out via a filter — Steve may want some FF forms
		// to bypass GHL (analytics-only forms, internal tools, etc).
		$should_forward = (bool) apply_filters( 'showtime/fluentforms/should_forward', true, $form_id, $form_data );
		if ( ! $should_forward ) {
			return;
		}

		Ghl::forward(
			Ghl::TYPE_FLUENTFORM,
			array(
				'form_id'    => $form_id,
				'form_title' => $form_title,
				'entry_id'   => (int) $entry_id,
				'fields'     => is_array( $form_data ) ? $form_data : array(),
			),
			array(
				'source_form' => 'fluentforms',
			)
		);
	}
}
