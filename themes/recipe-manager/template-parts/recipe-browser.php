<?php
/** Shared archive, category, tag, and recipe search presentation. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
$archive = get_post_type_archive_link( 'rm_recipe' );
$state = function_exists( '\RecipeManager\Core\browse_state' ) ? \RecipeManager\Core\browse_state() : array( 'categories' => array(), 'min' => '', 'max' => '', 'sort' => 'newest' );
$categories = taxonomy_exists( 'rm_recipe_category' ) ? get_terms( array( 'taxonomy' => 'rm_recipe_category', 'hide_empty' => true, 'orderby' => 'name' ) ) : array();
if ( is_wp_error( $categories ) ) { $categories = array(); }
global $wp_query;
?>
<main id="main" class="recipe-browser">
	<header class="browse-hero">
		<div><h1><?php esc_html_e( 'Recipes', 'recipe-manager' ); ?></h1>
		<p><?php esc_html_e( 'Explore a collection of comforting and flavorful recipes, from everyday meals to special favorites.', 'recipe-manager' ); ?></p></div>
	</header>
	<div class="browse-container">
	<form class="browse-layout" method="get" action="<?php echo esc_url( $archive ?: home_url( '/' ) ); ?>">
		<aside class="browse-sidebar" aria-label="<?php esc_attr_e( 'Recipe filters', 'recipe-manager' ); ?>">
		<details class="browse-filters" open>
			<summary><?php esc_html_e( 'Filter Recipes', 'recipe-manager' ); ?></summary>
			<fieldset><legend><?php esc_html_e( 'Category', 'recipe-manager' ); ?></legend>
			<?php foreach ( $categories as $category ) : ?>
				<label class="browse-category"><input type="checkbox" name="rm_category[]" value="<?php echo esc_attr( $category->term_id ); ?>" <?php checked( in_array( $category->term_id, $state['categories'], true ) ); ?>>
				<span><?php echo esc_html( $category->name ); ?> <small>(<?php echo esc_html( number_format_i18n( $category->count ) ); ?>)</small></span></label>
			<?php endforeach; ?>
			<?php if ( ! $categories ) : ?><p><?php esc_html_e( 'Categories will appear as recipes are added.', 'recipe-manager' ); ?></p><?php endif; ?>
			</fieldset>
			<fieldset class="browse-time"><legend><?php esc_html_e( 'Total Time', 'recipe-manager' ); ?></legend>
			<p><?php esc_html_e( 'Preparation + cooking, in minutes', 'recipe-manager' ); ?></p>
			<div><label><?php esc_html_e( 'Minimum', 'recipe-manager' ); ?><input type="number" name="rm_min" min="0" max="100000" step="1" value="<?php echo esc_attr( $state['min'] ); ?>" placeholder="0"></label>
			<label><?php esc_html_e( 'Maximum', 'recipe-manager' ); ?><input type="number" name="rm_max" min="0" max="100000" step="1" value="<?php echo esc_attr( $state['max'] ); ?>" placeholder="<?php esc_attr_e( 'Any', 'recipe-manager' ); ?>"></label></div>
			</fieldset>
			<a class="browse-clear" href="<?php echo esc_url( $archive ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'Clear filters', 'recipe-manager' ); ?></a>
		</details>
		</aside>
		<div class="browse-results-controls">
			<?php get_template_part( 'template-parts/recipe-search', null, array( 'embedded' => true, 'value' => get_search_query( false ) ) ); ?>
			<div class="browse-toolbar">
				<p><?php printf( esc_html( _n( '%s recipe found', '%s recipes found', $wp_query->found_posts, 'recipe-manager' ) ), esc_html( number_format_i18n( $wp_query->found_posts ) ) ); ?></p>
				<label><?php esc_html_e( 'Sort by:', 'recipe-manager' ); ?> <select name="rm_sort">
				<?php foreach ( array( 'newest' => __( 'Newest', 'recipe-manager' ), 'oldest' => __( 'Oldest', 'recipe-manager' ), 'title' => __( 'Title A–Z', 'recipe-manager' ) ) as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $state['sort'], $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?></select></label>
			</div>
			<?php if ( $state['categories'] || '' !== $state['min'] || '' !== $state['max'] || get_search_query() || is_tax( 'rm_recipe_tag' ) ) : ?>
			<div class="browse-active" aria-label="<?php esc_attr_e( 'Active filters', 'recipe-manager' ); ?>">
				<?php foreach ( $categories as $category ) : if ( ! in_array( $category->term_id, $state['categories'], true ) ) { continue; } ?><span><?php echo esc_html( $category->name ); ?></span><?php endforeach; ?>
				<?php if ( get_search_query() ) : ?><span><?php printf( esc_html__( 'Search: %s', 'recipe-manager' ), esc_html( get_search_query( false ) ) ); ?></span><?php endif; ?>
				<?php if ( is_tax( 'rm_recipe_tag' ) ) : ?><span><?php single_term_title(); ?></span><?php endif; ?>
				<?php if ( '' !== $state['min'] ) : ?><span><?php printf( esc_html__( 'At least %s min', 'recipe-manager' ), esc_html( $state['min'] ) ); ?></span><?php endif; ?>
				<?php if ( '' !== $state['max'] ) : ?><span><?php printf( esc_html__( 'Up to %s min', 'recipe-manager' ), esc_html( $state['max'] ) ); ?></span><?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</form>
	<div class="browse-results">
		<?php if ( have_posts() ) : ?>
		<div class="home-recipe-grid">
		<?php while ( have_posts() ) : the_post();
			get_template_part( 'template-parts/recipe-card', null, array( 'compact' => true, 'featured' => '1' === get_post_meta( get_the_ID(), 'rm_featured', true ) ) );
		endwhile; ?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
		<?php else : ?>
		<div class="browse-empty"><h2><?php esc_html_e( 'No recipes found', 'recipe-manager' ); ?></h2><p><?php esc_html_e( 'Try another search or clear your filters to explore the collection.', 'recipe-manager' ); ?></p><a href="<?php echo esc_url( $archive ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'View all recipes', 'recipe-manager' ); ?></a></div>
		<?php endif; ?>
	</div>
	</div>
</main>
<?php get_footer(); ?>
