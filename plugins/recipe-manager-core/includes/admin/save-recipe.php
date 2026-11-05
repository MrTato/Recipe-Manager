<?php
/**
 * Save structured recipe editor data.
 */

namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate the complete collection before changing any stored ingredients.
 *
 * @return array|\WP_Error Ordered, sanitized rows or a validation failure.
 */
function sanitize_ingredient_rows( $rows ) {
	if ( ! is_array( $rows ) ) {
		return new \WP_Error( 'invalid_ingredients' );
	}
	$clean = array();
	$units = get_units();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			return new \WP_Error( 'invalid_ingredients' );
		}
		$values = array();
		foreach ( array( 'name', 'mass_quantity', 'mass_unit', 'volume_quantity', 'volume_unit', 'note', 'group' ) as $field ) {
			$value = $row[ $field ] ?? '';
			if ( ! is_string( $value ) ) {
				return new \WP_Error( 'invalid_ingredients' );
			}
			$values[ $field ] = trim( $value );
		}
		if ( ! array_filter( $values, static function ( $value ) { return '' !== $value; } ) ) {
			continue;
		}
		$name = sanitize_text_field( $values['name'] );
		if ( '' === $name ) {
			return new \WP_Error( 'invalid_ingredients' );
		}
		$has_measurement = false;
		foreach ( array( 'mass', 'volume' ) as $type ) {
			$quantity = $values[ $type . '_quantity' ];
			$unit = $values[ $type . '_unit' ];
			if ( '' === $quantity && '' === $unit ) {
				continue;
			}
			if ( ! isset( $units[ $unit ] ) || $units[ $unit ]['type'] !== $type
				|| ! preg_match( '/\A(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)\z/', $quantity ) || ! is_finite( (float) $quantity ) || (float) $quantity <= 0 ) {
				return new \WP_Error( 'invalid_ingredients' );
			}
			$has_measurement = true;
		}
		if ( ! $has_measurement ) {
			return new \WP_Error( 'invalid_ingredients' );
		}
		$clean[] = array(
			'name'     => $name,
			'mass_quantity' => '' === $values['mass_quantity'] ? null : $values['mass_quantity'],
			'mass_unit' => $values['mass_unit'],
			'volume_quantity' => '' === $values['volume_quantity'] ? null : $values['volume_quantity'],
			'volume_unit' => $values['volume_unit'],
			'note'     => sanitize_text_field( $values['note'] ),
			'group'    => sanitize_text_field( $values['group'] ),
		);
	}
	return $clean;
}

function save_recipe_ingredients( $post_id ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id )
		|| ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['rm_ingredients_nonce'] ) || ! is_string( $_POST['rm_ingredients_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rm_ingredients_nonce'] ) ), 'rm_save_ingredients' ) ) {
		return;
	}
	// An absent row collection with a valid editor nonce means all rows were removed.
	$rows = sanitize_ingredient_rows( wp_unslash( $_POST['rm_ingredients'] ?? array() ) );
	if ( is_wp_error( $rows ) ) {
		add_filter( 'redirect_post_location', __NAMESPACE__ . '\\ingredient_error_redirect' );
		return;
	}
	// Metadata APIs serialize the array. Slash strings to preserve literal backslashes.
	update_post_meta( $post_id, '_rm_ingredients', wp_slash( $rows ) );
}
add_action( 'save_post_rm_recipe', __NAMESPACE__ . '\\save_recipe_ingredients' );

function ingredient_error_redirect( $location ) {
	return add_query_arg( 'rm_ingredient_error', '1', $location );
}

function show_ingredient_save_error() {
	$screen = get_current_screen();
	if ( ! $screen || 'rm_recipe' !== $screen->post_type || ! isset( $_GET['rm_ingredient_error'] ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>';
	esc_html_e( 'Ingredients were not saved. Previous ingredient data was kept. Each nonempty row needs a name and at least one complete mass or volume measurement. Each supplied measurement needs a supported unit and a positive decimal quantity. Use 0.5 rather than 1/2. Re-enter your ingredient changes and save again.', 'recipe-manager-core' );
	echo '</p></div>';
}
add_action( 'admin_notices', __NAMESPACE__ . '\\show_ingredient_save_error' );

/**
 * Validate all steps before replacing the stored collection.
 *
 * @return array|\WP_Error Ordered steps containing text, attachment IDs, and optional group labels.
 */
function sanitize_instruction_rows( $rows ) {
	if ( ! is_array( $rows ) ) {
		return new \WP_Error( 'invalid_steps' );
	}
	$clean = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) || ! is_string( $row['text'] ?? '' ) || ! is_string( $row['group'] ?? '' ) ) {
			return new \WP_Error( 'invalid_steps' );
		}
		$text = sanitize_textarea_field( $row['text'] ?? '' );
		$group = sanitize_text_field( $row['group'] ?? '' );
		$raw_id = $row['image_id'] ?? '0';
		if ( ( ! is_string( $raw_id ) && ! is_int( $raw_id ) ) || ! preg_match( '/\A[0-9]+\z/', (string) $raw_id ) ) {
			return new \WP_Error( 'invalid_steps' );
		}
		$image_id = (int) $raw_id;
		if ( $image_id && ( 'attachment' !== get_post_type( $image_id ) || ! wp_attachment_is_image( $image_id )
			|| ! current_user_can( 'read_post', $image_id ) ) ) {
			return new \WP_Error( 'invalid_steps' );
		}
		if ( '' === trim( $text ) ) {
			if ( $image_id || '' !== $group ) {
				return new \WP_Error( 'invalid_steps' );
			}
			continue;
		}
		$clean[] = array( 'text' => $text, 'image_id' => $image_id, 'group' => $group );
	}
	return $clean;
}

function save_recipe_steps( $post_id ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id )
		|| ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['rm_steps_nonce'] ) || ! is_string( $_POST['rm_steps_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rm_steps_nonce'] ) ), 'rm_save_steps' ) ) {
		return;
	}
	// A valid editor nonce with no rows means the author removed every step.
	$steps = sanitize_instruction_rows( wp_unslash( $_POST['rm_steps'] ?? array() ) );
	if ( is_wp_error( $steps ) ) {
		add_filter( 'redirect_post_location', __NAMESPACE__ . '\\instruction_error_redirect' );
		return;
	}
	update_post_meta( $post_id, '_rm_steps', wp_slash( $steps ) );
}
add_action( 'save_post_rm_recipe', __NAMESPACE__ . '\\save_recipe_steps' );

function instruction_error_redirect( $location ) {
	return add_query_arg( 'rm_step_error', '1', $location );
}

function show_instruction_save_error() {
	$screen = get_current_screen();
	if ( ! $screen || 'rm_recipe' !== $screen->post_type || ! isset( $_GET['rm_step_error'] ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>';
	esc_html_e( 'Instructions were not saved. Previous instruction data was kept. Each step needs instruction text and, optionally, a valid image selected from the media library. Re-enter your instruction changes and save again.', 'recipe-manager-core' );
	echo '</p></div>';
}
add_action( 'admin_notices', __NAMESPACE__ . '\\show_instruction_save_error' );


/**
 * Shared summary for client-side validation and rejected server submissions.
 */
function show_recipe_validation_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'rm_recipe' !== $screen->post_type || 'post' !== $screen->base ) {
		return;
	}
	$has_error = isset( $_GET['rm_ingredient_error'] ) || isset( $_GET['rm_step_error'] );
	?>
	<div id="rm-recipe-validation-notice" class="notice notice-error" role="alert" tabindex="-1" <?php if ( ! $has_error ) : ?>hidden<?php endif; ?>>
		<p><?php esc_html_e( 'There are errors. Please fix them', 'recipe-manager-core' ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', __NAMESPACE__ . '\\show_recipe_validation_notice', 5 );
