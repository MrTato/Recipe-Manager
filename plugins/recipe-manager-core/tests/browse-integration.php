<?php
/** Run with wp eval-file; creates and removes isolated recipe fixtures. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$ids = array();
$term = wp_insert_term( 'Browse test ' . wp_generate_uuid4(), 'rm_recipe_category' );
if ( is_wp_error( $term ) ) { throw new RuntimeException( $term->get_error_message() ); }
$term_id = $term['term_id'];
$original_get = $_GET;
$original_main = $GLOBALS['wp_the_query'];
$original_query = $GLOBALS['wp_query'];
$check = static function ( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
};
$run = static function ( $args, $filters = array() ) use ( $term_id ) {
	$_GET = array_merge( array( 'rm_category' => array( (string) $term_id ) ), $filters );
	$q = new WP_Query();
	$GLOBALS['wp_the_query'] = $q;
	$GLOBALS['wp_query'] = $q;
	$q->query( array_merge( array( 'post_type' => 'rm_recipe' ), $args ) );
	return $q;
};
try {
	for ( $i = 0; $i < 11; $i++ ) {
		$id = wp_insert_post( array( 'post_type' => 'rm_recipe', 'post_status' => 'publish', 'post_title' => sprintf( 'Browse fixture %02d', $i ) ), true );
		if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
		$ids[] = $id;
		wp_set_object_terms( $id, array( $term_id ), 'rm_recipe_category' );
		update_post_meta( $id, 'rm_prep_minutes', 10 );
		update_post_meta( $id, 'rm_cook_minutes', $i * 5 );
	}
	update_post_meta( $ids[0], 'rm_description', 'uniquebrowsedescription' );
	update_post_meta( $ids[1], '_rm_ingredients', array( array( 'name' => 'uniquebrowseingredient' ) ) );
	$q = $run( array() );
	$check( 11 === (int) $q->found_posts && 9 === $q->post_count && 2 === (int) $q->max_num_pages, 'Archive category filter or pagination failed' );
	$q = $run( array( 'paged' => 2 ) );
	$check( 2 === $q->post_count, 'Second page failed' );
	$q = $run( array(), array( 'rm_min' => '20', 'rm_max' => '30', 'rm_sort' => 'title' ) );
	$check( 3 === (int) $q->found_posts && $ids[2] === $q->posts[0]->ID, 'Combined time filter or title sorting failed' );
	$q = $run( array( 's' => 'uniquebrowsedescription' ) );
	$check( 1 === (int) $q->found_posts && $ids[0] === $q->posts[0]->ID, 'Description search failed' );
	$q = $run( array( 's' => 'uniquebrowseingredient' ) );
	$check( 1 === (int) $q->found_posts && $ids[1] === $q->posts[0]->ID, 'Ingredient search failed' );
	$q = $run( array( 's' => 'uniquebrowsenomatch' ) );
	$check( 0 === (int) $q->found_posts, 'Empty search failed' );
	$category = get_term( $term_id );
	$q = $run( array( 'rm_recipe_category' => $category->slug ) );
	$check( in_array( $term_id, \RecipeManager\Core\browse_state()['categories'], true ), 'Native category selection failed' );
	ob_start();
	get_template_part( 'template-parts/recipe-browser' );
	$html = ob_get_clean();
	$check( false !== strpos( $html, 'browse-sidebar' ) && false !== strpos( $html, 'home-recipe-card' ) && 1 === substr_count( $html, '<form ' ), 'Shared template rendering failed' );
	echo "Browse integration checks passed: categories, time, sorting, search, pagination, empty results, and shared rendering.\n";
} finally {
	foreach ( $ids as $id ) { wp_delete_post( $id, true ); }
	wp_delete_term( $term_id, 'rm_recipe_category' );
	$_GET = $original_get;
	$GLOBALS['wp_the_query'] = $original_main;
	$GLOBALS['wp_query'] = $original_query;
}
