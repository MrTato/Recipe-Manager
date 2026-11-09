<?php
/**
 * Plugin Name: Recipe Manager Core
 * Description: Recipe content structure and reusable functionality for Recipe Manager.
 * Version: 0.1.0
 * Text Domain: recipe-manager-core
 */

namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/post-types.php';
require_once __DIR__ . '/includes/taxonomies.php';
require_once __DIR__ . '/includes/acf-fields.php';
require_once __DIR__ . '/includes/units.php';
require_once __DIR__ . '/includes/recipe-meta.php';

if ( is_admin() ) {
	require_once __DIR__ . '/includes/admin/category-image.php';
	require_once __DIR__ . '/includes/admin/featured-column.php';
	require_once __DIR__ . '/includes/admin/ingredient-box.php';
	require_once __DIR__ . '/includes/admin/instruction-box.php';
	require_once __DIR__ . '/includes/admin/save-recipe.php';
}

/**
 * Register routes before refreshing them when the content model is implemented.
 */
function activate() {
	if ( function_exists( __NAMESPACE__ . '\\register_recipe_post_type' ) ) {
		register_recipe_post_type();
	}

	if ( function_exists( __NAMESPACE__ . '\\register_recipe_taxonomies' ) ) {
		register_recipe_taxonomies();
	}

	flush_rewrite_rules();
}

/**
 * Remove recipe routes from the refreshed rewrite rules, retaining all content.
 */
function deactivate() {
	if ( taxonomy_exists( 'rm_recipe_category' ) ) {
		unregister_taxonomy( 'rm_recipe_category' );
	}

	if ( taxonomy_exists( 'rm_recipe_tag' ) ) {
		unregister_taxonomy( 'rm_recipe_tag' );
	}

	if ( post_type_exists( 'rm_recipe' ) ) {
		unregister_post_type( 'rm_recipe' );
	}

	flush_rewrite_rules();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\deactivate' );

/**
 * Explain the missing optional-at-runtime dependency without blocking activation.
 */
function show_acf_notice() {
	if ( ! current_user_can( 'activate_plugins' ) || function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__(
			'Recipe Manager Core requires the free Advanced Custom Fields (ACF) plugin for its basic recipe fields. Please install and activate ACF. Existing recipe data is preserved.',
			'recipe-manager-core'
		)
	);
}

add_action( 'admin_notices', __NAMESPACE__ . '\\show_acf_notice' );
