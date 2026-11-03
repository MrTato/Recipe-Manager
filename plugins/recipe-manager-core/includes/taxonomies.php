<?php
/**
 * Recipe taxonomy registration.
 */

namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register categories and tags specifically for recipes.
 */
function register_recipe_taxonomies() {
	register_taxonomy(
		'rm_recipe_category',
		array( 'rm_recipe' ),
		array(
			'labels'            => array(
				'name'              => __( 'Recipe Categories', 'recipe-manager-core' ),
				'singular_name'     => __( 'Recipe Category', 'recipe-manager-core' ),
				'search_items'      => __( 'Search Recipe Categories', 'recipe-manager-core' ),
				'all_items'         => __( 'All Recipe Categories', 'recipe-manager-core' ),
				'parent_item'       => __( 'Parent Recipe Category', 'recipe-manager-core' ),
				'parent_item_colon' => __( 'Parent Recipe Category:', 'recipe-manager-core' ),
				'edit_item'         => __( 'Edit Recipe Category', 'recipe-manager-core' ),
				'update_item'       => __( 'Update Recipe Category', 'recipe-manager-core' ),
				'add_new_item'      => __( 'Add New Recipe Category', 'recipe-manager-core' ),
				'new_item_name'     => __( 'New Recipe Category Name', 'recipe-manager-core' ),
				'not_found'         => __( 'No recipe categories found.', 'recipe-manager-core' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'recipe-category',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'rm_recipe_tag',
		array( 'rm_recipe' ),
		array(
			'labels'            => array(
				'name'                       => __( 'Recipe Tags', 'recipe-manager-core' ),
				'singular_name'              => __( 'Recipe Tag', 'recipe-manager-core' ),
				'search_items'               => __( 'Search Recipe Tags', 'recipe-manager-core' ),
				'popular_items'              => __( 'Popular Recipe Tags', 'recipe-manager-core' ),
				'all_items'                  => __( 'All Recipe Tags', 'recipe-manager-core' ),
				'edit_item'                  => __( 'Edit Recipe Tag', 'recipe-manager-core' ),
				'update_item'                => __( 'Update Recipe Tag', 'recipe-manager-core' ),
				'add_new_item'               => __( 'Add New Recipe Tag', 'recipe-manager-core' ),
				'new_item_name'              => __( 'New Recipe Tag Name', 'recipe-manager-core' ),
				'separate_items_with_commas' => __( 'Separate recipe tags with commas', 'recipe-manager-core' ),
				'add_or_remove_items'        => __( 'Add or remove recipe tags', 'recipe-manager-core' ),
				'choose_from_most_used'      => __( 'Choose from the most used recipe tags', 'recipe-manager-core' ),
				'not_found'                  => __( 'No recipe tags found.', 'recipe-manager-core' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'recipe-tag',
				'with_front' => false,
			),
		)
	);
}

add_action( 'init', __NAMESPACE__ . '\\register_recipe_taxonomies' );
