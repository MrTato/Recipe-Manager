<?php
/**
 * Repeatable ingredient editor.
 */

namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function register_ingredient_box() {
	add_meta_box( 'rm-ingredients', __( 'Ingredients', 'recipe-manager-core' ), __NAMESPACE__ . '\\render_ingredient_box', 'rm_recipe', 'normal', 'default' );
}
add_action( 'add_meta_boxes_rm_recipe', __NAMESPACE__ . '\\register_ingredient_box' );

/**
 * Render one row, also used for the client-side template.
 */
function render_ingredient_row( $index, $row = array() ) {
	$units = get_units();
	// Display measurements saved by the original single-measurement editor.
	$legacy_type = $units[ $row['unit'] ?? '' ]['type'] ?? '';
	if ( in_array( $legacy_type, array( 'mass', 'volume' ), true ) && ! isset( $row[ $legacy_type . '_unit' ] ) ) {
		$row[ $legacy_type . '_quantity' ] = $row['quantity'] ?? '';
		$row[ $legacy_type . '_unit' ] = $row['unit'];
	}
	$row = wp_parse_args( $row, array( 'name' => '', 'mass_quantity' => '', 'mass_unit' => '', 'volume_quantity' => '', 'volume_unit' => '', 'note' => '', 'group' => '' ) );
	$labels = array(
		'name'     => __( 'Ingredient name', 'recipe-manager-core' ),
		'mass_quantity' => __( 'Mass quantity', 'recipe-manager-core' ),
		'mass_unit' => __( 'Mass unit', 'recipe-manager-core' ),
		'volume_quantity' => __( 'Volume quantity', 'recipe-manager-core' ),
		'volume_unit' => __( 'Volume unit', 'recipe-manager-core' ),
		'note'     => __( 'Preparation note', 'recipe-manager-core' ),
		'group'    => __( 'Group (optional)', 'recipe-manager-core' ),
	);
	?>
	<div class="rm-ingredient-row">
		<h4 class="rm-ingredient-group-heading" data-group-heading hidden></h4>
		<div class="rm-ingredient-fields">
			<?php foreach ( $labels as $field => $label ) : ?>
				<label>
					<span><?php echo esc_html( $label ); ?></span>
					<?php if ( in_array( $field, array( 'mass_unit', 'volume_unit' ), true ) ) : ?>
						<select name="rm_ingredients[<?php echo esc_attr( $index ); ?>][<?php echo esc_attr( $field ); ?>]" data-field="<?php echo esc_attr( $field ); ?>">
							<option value=""><?php esc_html_e( 'No unit', 'recipe-manager-core' ); ?></option>
							<?php foreach ( $units as $code => $unit ) :
								if ( $unit['type'] !== ( 'mass_unit' === $field ? 'mass' : 'volume' ) ) { continue; } ?>
								<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $row[ $field ], $code ); ?>><?php echo esc_html( $unit['label'] ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<input type="<?php echo in_array( $field, array( 'mass_quantity', 'volume_quantity' ), true ) ? 'number' : 'text'; ?>"
							name="rm_ingredients[<?php echo esc_attr( $index ); ?>][<?php echo esc_attr( $field ); ?>]"
							data-field="<?php echo esc_attr( $field ); ?>"
							<?php if ( 'group' === $field ) : ?>list="rm-ingredient-groups" aria-describedby="rm-ingredient-group-help"<?php endif; ?>
							value="<?php echo esc_attr( (string) $row[ $field ] ); ?>"
							<?php if ( in_array( $field, array( 'mass_quantity', 'volume_quantity' ), true ) ) : ?>min="0" step="any" inputmode="decimal"<?php endif; ?> />
					<?php endif; ?>
					<span class="rm-measurement-error" data-field-error="<?php echo esc_attr( $field ); ?>" role="alert" hidden></span>
				</label>
			<?php endforeach; ?>
		</div>
		<div class="rm-ingredient-actions">
			<button type="button" class="button" data-ingredient-action="up"><?php esc_html_e( 'Move up', 'recipe-manager-core' ); ?></button>
			<button type="button" class="button" data-ingredient-action="down"><?php esc_html_e( 'Move down', 'recipe-manager-core' ); ?></button>
			<button type="button" class="button" data-ingredient-action="remove"><?php esc_html_e( 'Remove ingredient', 'recipe-manager-core' ); ?></button>
		</div>
	</div>
	<?php
}

function render_ingredient_box( $post ) {
	$rows = get_post_meta( $post->ID, '_rm_ingredients', true );
	$rows = is_array( $rows ) ? array_values( $rows ) : array();
	wp_nonce_field( 'rm_save_ingredients', 'rm_ingredients_nonce' );
	?>
	<div id="rm-ingredient-editor">
		<p><?php esc_html_e( 'Each ingredient needs a name and at least one complete measurement: mass or volume. You may provide both as equivalent amounts. Use decimals such as 0.5 or 0.25. Spoon and cup measures: teaspoon 5 ml, tablespoon 15 ml, cup 250 ml.', 'recipe-manager-core' ); ?></p>
		<p id="rm-ingredient-group-help"><?php esc_html_e( 'Grouping is optional. Enter any group name, such as Chicken ingredients or Sauce ingredients, and use the same name for related ingredients. Leave it blank for ungrouped ingredients. Move related rows together to show them under one heading.', 'recipe-manager-core' ); ?></p>
		<datalist id="rm-ingredient-groups">
			<?php
			$groups = array_unique( array_filter( array_column( $rows, 'group' ), static function ( $group ) { return '' !== trim( (string) $group ); } ) );
			foreach ( $groups as $group ) : ?>
				<option value="<?php echo esc_attr( $group ); ?>"></option>
			<?php endforeach; ?>
		</datalist>
		<noscript><p><?php esc_html_e( 'Enable JavaScript to add, remove, or reorder ingredients. Existing rows can still be edited and saved.', 'recipe-manager-core' ); ?></p></noscript>
		<div data-ingredient-rows>
			<?php foreach ( $rows as $index => $row ) { render_ingredient_row( $index, $row ); } ?>
		</div>
		<button type="button" class="button" data-ingredient-action="add"><?php esc_html_e( 'Add ingredient', 'recipe-manager-core' ); ?></button>
		<template id="rm-ingredient-template"><?php render_ingredient_row( '__INDEX__' ); ?></template>
		<p class="screen-reader-text" aria-live="polite" data-ingredient-status></p>
		<span hidden data-measurement-message><?php esc_html_e( 'Provide at least one complete mass or volume measurement.', 'recipe-manager-core' ); ?></span>
		<span hidden data-ungrouped-label><?php esc_html_e( 'Ungrouped ingredients', 'recipe-manager-core' ); ?></span>
		<span hidden data-order-message><?php esc_html_e( 'Ingredient order updated.', 'recipe-manager-core' ); ?></span>
	</div>
	<?php
}

function enqueue_ingredient_assets( $hook ) {
	$screen = get_current_screen();
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! $screen || 'rm_recipe' !== $screen->post_type ) {
		return;
	}
	$plugin_file = dirname( __DIR__, 2 ) . '/recipe-manager-core.php';
	foreach ( array( 'js', 'css' ) as $extension ) {
		$path = dirname( __DIR__, 2 ) . '/assets/admin.' . $extension;
		$url  = plugins_url( 'assets/admin.' . $extension, $plugin_file );
		if ( 'js' === $extension ) {
			wp_enqueue_script( 'rm-recipe-admin', $url, array(), (string) filemtime( $path ), true );
		} else {
			wp_enqueue_style( 'rm-recipe-admin', $url, array(), (string) filemtime( $path ) );
		}
	}
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_ingredient_assets' );
