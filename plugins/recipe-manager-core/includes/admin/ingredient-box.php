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

/** Render a labeled field while keeping existing submission and error hooks. */
function render_ingredient_field( $index, $field, $label, $value, $units ) {
	$is_quantity = in_array( $field, array( 'mass_quantity', 'volume_quantity', 'optional_quantity' ), true );
	?>
	<label class="rm-field-<?php echo esc_attr( $field ); ?>">
		<span class="rm-field-label"><?php echo esc_html( $label ); ?></span>
		<?php if ( in_array( $field, array( 'mass_unit', 'volume_unit' ), true ) ) : ?>
			<select name="rm_ingredients[<?php echo esc_attr( $index ); ?>][<?php echo esc_attr( $field ); ?>]" data-field="<?php echo esc_attr( $field ); ?>">
				<option value=""><?php esc_html_e( 'No unit', 'recipe-manager-core' ); ?></option>
				<?php foreach ( $units as $code => $unit ) :
					if ( $unit['type'] !== ( 'mass_unit' === $field ? 'mass' : 'volume' ) || ! in_array( $code, 'mass_unit' === $field ? array( 'g', 'kg' ) : array( 'ml', 'l' ), true ) ) { continue; } ?>
					<option value="<?php echo esc_attr( $code ); ?>" title="<?php echo esc_attr( $unit['label'] ); ?>" <?php selected( $value, $code ); ?>><?php echo esc_html( $code ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php else : ?>
			<input type="<?php echo $is_quantity ? 'number' : 'text'; ?>"
				name="rm_ingredients[<?php echo esc_attr( $index ); ?>][<?php echo esc_attr( $field ); ?>]"
				data-field="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( (string) $value ); ?>"
				<?php if ( 'group' === $field ) : ?>autocomplete="off" aria-describedby="rm-ingredient-group-help"<?php endif; ?>
				<?php if ( 'optional_unit' === $field ) : ?>list="rm-default-unit-suggestions" autocomplete="off" placeholder="<?php esc_attr_e( 'Unit (optional)', 'recipe-manager-core' ); ?>"<?php endif; ?>
				<?php if ( $is_quantity ) : ?>min="0" step="any" inputmode="decimal"<?php endif; ?> />
		<?php endif; ?>
		<span class="rm-measurement-error" data-field-error="<?php echo esc_attr( $field ); ?>" role="alert" hidden></span>
	</label>
	<?php
}

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
	$row = wp_parse_args( $row, array( 'name' => '', 'mass_quantity' => '', 'mass_unit' => '', 'volume_quantity' => '', 'volume_unit' => '', 'note' => '', 'optional_quantity' => '', 'optional_unit' => '', 'group' => '' ) );
	$segments = array(
		'optional' => array( 'label' => __( 'Default unit', 'recipe-manager-core' ), 'icon' => 'leftright' ),
		'mass' => array( 'label' => __( 'Mass', 'recipe-manager-core' ), 'icon' => 'dashboard' ),
		'volume' => array( 'label' => __( 'Volume', 'recipe-manager-core' ), 'icon' => 'filter' ),
	);
	?>
	<div class="rm-ingredient-row">
		<h4 class="rm-ingredient-group-heading" data-group-heading hidden></h4>
		<?php if ( '' !== $row['note'] ) : ?>
			<input type="hidden" name="rm_ingredients[<?php echo esc_attr( $index ); ?>][note]" data-field="note" value="<?php echo esc_attr( $row['note'] ); ?>" />
		<?php endif; ?>
		<div class="rm-ingredient-fields">
			<div class="rm-ingredient-identity">
				<?php render_ingredient_field( $index, 'name', __( 'Ingredient', 'recipe-manager-core' ), $row['name'], $units ); ?>
				<?php render_ingredient_field( $index, 'group', __( 'Group (optional)', 'recipe-manager-core' ), $row['group'], $units ); ?>
			</div>
			<?php foreach ( $segments as $type => $segment ) : ?>
				<fieldset class="rm-measurement rm-measurement-<?php echo esc_attr( $type ); ?>">
					<legend class="screen-reader-text"><?php echo esc_html( $segment['label'] ); ?></legend>
					<div class="rm-measurement-title" aria-hidden="true"><span class="dashicons dashicons-<?php echo esc_attr( $segment['icon'] ); ?>"></span><?php echo esc_html( $segment['label'] ); ?></div>
					<div class="rm-measurement-inputs">
						<?php
						render_ingredient_field( $index, $type . '_quantity', __( 'Quantity', 'recipe-manager-core' ), $row[ $type . '_quantity' ], $units );
						render_ingredient_field( $index, $type . '_unit', 'optional' === $type ? __( 'Default unit name', 'recipe-manager-core' ) : __( 'Unit', 'recipe-manager-core' ), $row[ $type . '_unit' ], $units );
						?>
					</div>
				</fieldset>
			<?php endforeach; ?>
		</div>
		<div class="rm-ingredient-actions">
			<button type="button" class="button" data-ingredient-action="up" aria-label="<?php esc_attr_e( 'Move up', 'recipe-manager-core' ); ?>" title="<?php esc_attr_e( 'Move up', 'recipe-manager-core' ); ?>"><span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span></button>
			<button type="button" class="button" data-ingredient-action="down" aria-label="<?php esc_attr_e( 'Move down', 'recipe-manager-core' ); ?>" title="<?php esc_attr_e( 'Move down', 'recipe-manager-core' ); ?>"><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
			<button type="button" class="button" data-ingredient-action="remove" aria-label="<?php esc_attr_e( 'Remove ingredient', 'recipe-manager-core' ); ?>" title="<?php esc_attr_e( 'Remove ingredient', 'recipe-manager-core' ); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
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
		<datalist id="rm-default-unit-suggestions">
			<?php foreach ( get_default_unit_suggestions() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" label="<?php echo esc_attr( $label ); ?>"></option>
			<?php endforeach; ?>
		</datalist>
		<details class="rm-ingredient-help">
			<summary><?php esc_html_e( 'Ingredient entry help', 'recipe-manager-core' ); ?></summary>
		<p><?php esc_html_e( 'Each ingredient needs a name and at least one complete measurement: mass, volume, or a Default unit you can choose or type. For example, enter Garlic as the ingredient, 2 as the Default unit quantity, and cloves as the Default unit. Leave the Default unit name blank to count the ingredient itself, for example 2 onions. Custom unit names are saved as suggestions for all recipes. You may provide multiple equivalent measurements. Use positive decimals such as 0.5 or 0.25; quantities must be greater than zero. Spoon and cup measures: teaspoon 5 ml, tablespoon 15 ml, cup 250 ml.', 'recipe-manager-core' ); ?></p>
		<p id="rm-ingredient-group-help"><?php esc_html_e( 'Grouping is optional. Enter any group name, such as Chicken ingredients or Sauce ingredients, and use the same name for related ingredients. Leave it blank for ungrouped ingredients. Move related rows together to show them under one heading.', 'recipe-manager-core' ); ?></p>
		</details>
		<noscript><p><?php esc_html_e( 'Enable JavaScript to add, remove, or reorder ingredients. Existing rows can still be edited and saved.', 'recipe-manager-core' ); ?></p></noscript>
		<div data-ingredient-rows>
			<?php foreach ( $rows as $index => $row ) { render_ingredient_row( $index, $row ); } ?>
		</div>
		<button type="button" class="button" data-ingredient-action="add"><?php esc_html_e( 'Add ingredient', 'recipe-manager-core' ); ?></button>
		<template id="rm-ingredient-template"><?php render_ingredient_row( '__INDEX__' ); ?></template>
		<p class="screen-reader-text" aria-live="polite" data-ingredient-status></p>
		<span hidden data-measurement-message><?php esc_html_e( 'Provide at least one complete mass, volume, or optional unit measurement.', 'recipe-manager-core' ); ?></span>
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
	wp_enqueue_script( 'rm-recipe-validation-notice', plugins_url( 'assets/validation-notice.js', $plugin_file ), array(), (string) filemtime( dirname( __DIR__, 2 ) . '/assets/validation-notice.js' ), true );
	foreach ( array( 'js', 'css' ) as $extension ) {
		$path = dirname( __DIR__, 2 ) . '/assets/admin.' . $extension;
		$url  = plugins_url( 'assets/admin.' . $extension, $plugin_file );
		if ( 'js' === $extension ) {
			wp_enqueue_script( 'rm-recipe-admin', $url, array( 'rm-recipe-validation-notice' ), (string) filemtime( $path ), true );
		} else {
			wp_enqueue_style( 'rm-recipe-admin', $url, array(), (string) filemtime( $path ) );
		}
	}
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_ingredient_assets' );
