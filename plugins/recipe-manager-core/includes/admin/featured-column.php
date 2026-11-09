<?php
/** Inline featured controls for the recipe list. */
namespace RecipeManager\Core;

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'manage_rm_recipe_posts_columns', function ( $columns ) {
	$result = array();
	foreach ( $columns as $key => $label ) {
		$result[ $key ] = $label;
		if ( 'title' === $key ) {
			$result['rm_featured'] = __( 'Featured', 'recipe-manager-core' );
		}
	}
	return $result;
} );

add_action( 'manage_rm_recipe_posts_custom_column', function ( $column, $post_id ) {
	if ( 'rm_featured' !== $column ) { return; }
	$featured = '1' === get_post_meta( $post_id, 'rm_featured', true );
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		echo esc_html( $featured ? __( 'Yes', 'recipe-manager-core' ) : __( 'No', 'recipe-manager-core' ) );
		return;
	}
	printf(
		'<input type="checkbox" class="rm-featured-toggle" data-recipe-id="%1$d" data-nonce="%2$s" aria-label="%3$s" %4$s disabled><span class="rm-featured-status" role="status" aria-live="polite"></span><noscript>%5$s</noscript>',
		(int) $post_id,
		esc_attr( wp_create_nonce( 'rm_featured_' . $post_id ) ),
		esc_attr( sprintf( __( 'Feature %s', 'recipe-manager-core' ), get_the_title( $post_id ) ) ),
		checked( $featured, true, false ),
		esc_html__( ' Edit the recipe to change featured status.', 'recipe-manager-core' )
	);
}, 10, 2 );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$screen = get_current_screen();
	if ( 'edit.php' !== $hook || ! $screen || 'rm_recipe' !== $screen->post_type ) { return; }
	$path = dirname( __DIR__, 2 ) . '/assets/featured.js';
	wp_enqueue_script( 'rm-featured', plugins_url( 'assets/featured.js', dirname( __DIR__, 2 ) . '/recipe-manager-core.php' ), array(), filemtime( $path ), true );
	wp_localize_script( 'rm-featured', 'rmFeatured', array(
		'url' => admin_url( 'admin-ajax.php' ),
		'saving' => __( ' Saving…', 'recipe-manager-core' ),
		'saved' => __( ' Saved.', 'recipe-manager-core' ),
		'error' => __( ' Could not save. Reload and try again.', 'recipe-manager-core' ),
	) );
} );

add_action( 'wp_ajax_rm_set_featured', function () {
	$post_id = isset( $_POST['recipe_id'] ) && is_scalar( $_POST['recipe_id'] ) ? absint( $_POST['recipe_id'] ) : 0;
	check_ajax_referer( 'rm_featured_' . $post_id );
	if ( 'rm_recipe' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( null, 403 );
	}
	$value = $_POST['featured'] ?? null;
	if ( ! in_array( $value, array( '0', '1' ), true ) ) {
		wp_send_json_error( null, 400 );
	}
	update_post_meta( $post_id, 'rm_featured', $value );
	if ( $value !== get_post_meta( $post_id, 'rm_featured', true ) ) {
		wp_send_json_error( null, 500 );
	}
	wp_send_json_success();
} );
