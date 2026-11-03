<?php
/**
 * Basic recipe fields supplied by ACF Free.
 */

namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
	'key' => 'group_6ae96f5e58d60',
	'title' => __( 'Recipe Details', 'recipe-manager-core' ),
	'fields' => array(
		array(
			'key' => 'field_6ae96f5f628d0',
			'label' => __( 'Description', 'recipe-manager-core' ),
			'name' => 'rm_description',
			'aria-label' => '',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'maxlength' => '',
			'allow_in_bindings' => 0,
			'rows' => '',
			'placeholder' => '',
			'new_lines' => '',
		),
		array(
			'key' => 'field_6ae96fd437f1b',
			'label' => __( 'Default servings', 'recipe-manager-core' ),
			'name' => 'rm_servings',
			'aria-label' => '',
			'type' => 'number',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'min' => 1,
			'max' => '',
			'allow_in_bindings' => 0,
			'placeholder' => '',
			'step' => 1,
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_6ae9700f02b9a',
			'label' => __( 'Preparation time', 'recipe-manager-core' ),
			'name' => 'rm_prep_minutes',
			'aria-label' => '',
			'type' => 'number',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'min' => 0,
			'max' => '',
			'allow_in_bindings' => 0,
			'placeholder' => '',
			'step' => 1,
			'prepend' => '',
			'append' => __( 'minutes', 'recipe-manager-core' ),
		),
		array(
			'key' => 'field_6ae9703302b9b',
			'label' => __( 'Cooking time', 'recipe-manager-core' ),
			'name' => 'rm_cook_minutes',
			'aria-label' => '',
			'type' => 'number',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'min' => 0,
			'max' => '',
			'allow_in_bindings' => 0,
			'placeholder' => '',
			'step' => 1,
			'prepend' => '',
			'append' => __( 'minutes', 'recipe-manager-core' ),
		),
		array(
			'key' => 'field_6ae9705c6a0e8',
			'label' => __( 'Featured recipe', 'recipe-manager-core' ),
			'name' => 'rm_featured',
			'aria-label' => '',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'default_value' => 0,
			'allow_in_bindings' => 0,
			'ui' => 0,
			'ui_on_text' => '',
			'ui_off_text' => '',
		),
		array(
			'key' => 'field_6ae970894a35b',
			'label' => __( 'Notes and tips', 'recipe-manager-core' ),
			'name' => 'rm_notes',
			'aria-label' => '',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'maxlength' => '',
			'allow_in_bindings' => 0,
			'rows' => '',
			'placeholder' => '',
			'new_lines' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'rm_recipe',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 0,
	'display_title' => '',
	'allow_ai_access' => false,
	'ai_description' => '',
) );
} );

