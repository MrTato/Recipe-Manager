<?php
/**
 * Recipe post type registration.
 */

namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register recipes independently of the active theme.
 */
function register_recipe_post_type() {
	register_post_type(
		'rm_recipe',
		array(
			'labels'       => array(
				'name'               => __( 'Recipes', 'recipe-manager-core' ),
				'singular_name'      => __( 'Recipe', 'recipe-manager-core' ),
				'add_new_item'       => __( 'Add New Recipe', 'recipe-manager-core' ),
				'edit_item'          => __( 'Edit Recipe', 'recipe-manager-core' ),
				'new_item'           => __( 'New Recipe', 'recipe-manager-core' ),
				'view_item'          => __( 'View Recipe', 'recipe-manager-core' ),
				'view_items'         => __( 'View Recipes', 'recipe-manager-core' ),
				'search_items'       => __( 'Search Recipes', 'recipe-manager-core' ),
				'not_found'          => __( 'No recipes found.', 'recipe-manager-core' ),
				'not_found_in_trash' => __( 'No recipes found in Trash.', 'recipe-manager-core' ),
				'all_items'          => __( 'All Recipes', 'recipe-manager-core' ),
				'archives'           => __( 'Recipe Archives', 'recipe-manager-core' ),
			),
			'public'       => true,
			'has_archive'  => 'recipes',
			'rewrite'      => array(
				'slug'       => 'recipes',
				'with_front' => false,
			),
			'hierarchical' => false,
			'show_in_rest' => true,
			'supports'     => array( 'title', 'thumbnail', 'revisions' ),
		)
	);
}

add_action( 'init', __NAMESPACE__ . '\\register_recipe_post_type' );
