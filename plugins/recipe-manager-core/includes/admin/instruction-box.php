<?php
/**
 * Ordered instruction editor with optional attachment images.
 */

namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function register_instruction_box() {
	add_meta_box( 'rm-instructions', __( 'Instructions', 'recipe-manager-core' ), __NAMESPACE__ . '\\render_instruction_box', 'rm_recipe', 'normal', 'default' );
}
add_action( 'add_meta_boxes_rm_recipe', __NAMESPACE__ . '\\register_instruction_box' );

function render_instruction_row( $index, $step = array() ) {
	$step = wp_parse_args( $step, array( 'text' => '', 'image_id' => 0, 'group' => '' ) );
	$image_id = absint( $step['image_id'] );
	$image = $image_id ? wp_get_attachment_image_src( $image_id, 'medium' ) : false;
	?>
	<div class="rm-step-row">
		<h3 class="rm-step-group-heading" data-step-group-heading hidden></h3>
		<h4 class="rm-step-badge"><?php esc_html_e( 'Step', 'recipe-manager-core' ); ?> <span data-step-number><?php echo is_int( $index ) ? esc_html( $index + 1 ) : ''; ?></span></h4>
		<div class="rm-step-content">
			<label class="rm-step-text-field">
				<span class="rm-step-section-title"><?php esc_html_e( 'Instruction', 'recipe-manager-core' ); ?></span>
				<textarea rows="3" name="rm_steps[<?php echo esc_attr( $index ); ?>][text]" data-step-field="text"><?php echo esc_textarea( $step['text'] ); ?></textarea>
			</label>
			<p class="rm-step-error" data-step-error role="alert" hidden><?php esc_html_e( 'Enter instruction text for this step, or clear its image and group.', 'recipe-manager-core' ); ?></p>
			<label class="rm-step-group-field">
				<span><?php esc_html_e( 'Group (optional)', 'recipe-manager-core' ); ?></span>
				<input type="text" name="rm_steps[<?php echo esc_attr( $index ); ?>][group]" data-step-field="group" value="<?php echo esc_attr( $step['group'] ); ?>" autocomplete="off" aria-describedby="rm-step-group-help" />
			</label>
		</div>
		<input type="hidden" name="rm_steps[<?php echo esc_attr( $index ); ?>][image_id]" data-step-field="image_id" value="<?php echo esc_attr( $image_id ); ?>" />
		<div class="rm-step-image">
			<div class="rm-step-section-title"><span class="dashicons dashicons-format-image" aria-hidden="true"></span><?php esc_html_e( 'Image', 'recipe-manager-core' ); ?><small><?php esc_html_e( '(Optional)', 'recipe-manager-core' ); ?></small></div>
			<div class="rm-step-image-placeholder" aria-hidden="true"><span class="dashicons dashicons-format-image"></span></div>
			<img data-step-preview alt="<?php esc_attr_e( 'Step image preview', 'recipe-manager-core' ); ?>" <?php if ( $image ) : ?>src="<?php echo esc_url( $image[0] ); ?>"<?php else : ?>hidden<?php endif; ?> />
			<button type="button" class="button" data-step-action="image"><?php esc_html_e( 'Choose or replace image', 'recipe-manager-core' ); ?></button>
			<button type="button" class="button-link-delete" data-step-action="clear-image" <?php if ( ! $image_id ) : ?>hidden<?php endif; ?>><?php esc_html_e( 'Remove image', 'recipe-manager-core' ); ?></button>
		</div>
		<div class="rm-step-actions">
			<button type="button" class="button" data-step-action="up" aria-label="<?php esc_attr_e( 'Move up', 'recipe-manager-core' ); ?>" title="<?php esc_attr_e( 'Move up', 'recipe-manager-core' ); ?>"><span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span></button>
			<button type="button" class="button" data-step-action="down" aria-label="<?php esc_attr_e( 'Move down', 'recipe-manager-core' ); ?>" title="<?php esc_attr_e( 'Move down', 'recipe-manager-core' ); ?>"><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
			<button type="button" class="button" data-step-action="remove" aria-label="<?php esc_attr_e( 'Remove step', 'recipe-manager-core' ); ?>" title="<?php esc_attr_e( 'Remove step', 'recipe-manager-core' ); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
		</div>
	</div>
	<?php
}

function render_instruction_box( $post ) {
	$steps = get_post_meta( $post->ID, '_rm_steps', true );
	$steps = is_array( $steps ) ? array_values( $steps ) : array();
	wp_nonce_field( 'rm_save_steps', 'rm_steps_nonce' );
	?>
	<div id="rm-instruction-editor" data-media-title="<?php esc_attr_e( 'Choose a step image', 'recipe-manager-core' ); ?>" data-media-button="<?php esc_attr_e( 'Use image', 'recipe-manager-core' ); ?>">
		<details class="rm-instruction-help">
			<summary><?php esc_html_e( 'Instruction entry help', 'recipe-manager-core' ); ?></summary>
		<p><?php esc_html_e( 'Add instructions in cooking order. Images are optional. Step numbers update automatically when you reorder steps.', 'recipe-manager-core' ); ?></p>
		<p id="rm-step-group-help"><?php esc_html_e( 'Grouping is optional. Use any group name, such as Prepare chicken or Make sauce. Reuse a name for related steps and move them together to show one subtitle. Leave it blank for ungrouped steps. Step numbers continue across all groups.', 'recipe-manager-core' ); ?></p>
		</details>
		<noscript><p><?php esc_html_e( 'Enable JavaScript to add, remove, or reorder steps and choose images. Existing instruction text can still be edited and saved.', 'recipe-manager-core' ); ?></p></noscript>
		<div data-step-rows>
			<?php foreach ( $steps as $index => $step ) { render_instruction_row( $index, $step ); } ?>
		</div>
		<button type="button" class="button" data-step-action="add"><?php esc_html_e( 'Add step', 'recipe-manager-core' ); ?></button>
		<template id="rm-step-template"><?php render_instruction_row( '__INDEX__' ); ?></template>
		<p class="screen-reader-text" aria-live="polite" data-step-status></p>
		<span hidden data-step-ungrouped-label><?php esc_html_e( 'Ungrouped steps', 'recipe-manager-core' ); ?></span>
		<span hidden data-step-order-message><?php esc_html_e( 'Step order updated.', 'recipe-manager-core' ); ?></span>
	</div>
	<?php
}

function enqueue_instruction_assets( $hook ) {
	$screen = get_current_screen();
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! $screen || 'rm_recipe' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	$root = dirname( __DIR__, 2 );
	$plugin_file = $root . '/recipe-manager-core.php';
	wp_enqueue_script( 'rm-instructions', plugins_url( 'assets/instructions.js', $plugin_file ), array( 'media-editor' ), (string) filemtime( $root . '/assets/instructions.js' ), true );
	wp_enqueue_style( 'rm-instructions', plugins_url( 'assets/instructions.css', $plugin_file ), array(), (string) filemtime( $root . '/assets/instructions.css' ) );
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_instruction_assets' );
