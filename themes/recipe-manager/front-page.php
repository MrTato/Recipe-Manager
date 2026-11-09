<?php
/** Public recipe discovery homepage. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$recipes_available = post_type_exists( 'rm_recipe' );
$archive_url = $recipes_available ? get_post_type_archive_link( 'rm_recipe' ) : false;
$featured_ids = array();
get_header(); ?>
<main id="main" class="site-main homepage">
	<section class="home-intro" aria-labelledby="home-title">
		<?php if ( is_page() && has_post_thumbnail( get_queried_object_id() ) ) : ?>
			<div class="home-intro-image" aria-hidden="true">
				<?php echo get_the_post_thumbnail( get_queried_object_id(), 'full', array( 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 1280px) 100vw, 1216px' ) ); ?>
			</div>
		<?php endif; ?>
		<div class="home-intro-content">
		<?php if ( is_page() && have_posts() ) : the_post(); ?>
			<h1 id="home-title"><?php the_title(); ?></h1>
			<div class="home-intro-copy"><?php the_content(); ?></div>
		<?php else : ?>
			<h1 id="home-title"><?php esc_html_e( 'Find your next favorite recipe', 'recipe-manager' ); ?></h1>
			<p><?php esc_html_e( 'Explore recipes from my kitchen, find something delicious, and make it your own.', 'recipe-manager' ); ?></p>
		<?php endif; ?>
		<?php if ( $archive_url ) : ?>
			<a class="home-button" href="<?php echo esc_url( $archive_url ); ?>">
				<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 11h18l-3 9H6l-3-9ZM7 3c-3 3 3 3 0 6m5-7c-3 3 3 3 0 6m5-5c-3 3 3 3 0 6"/></svg>
				<span><?php esc_html_e( 'Browse all recipes', 'recipe-manager' ); ?></span>
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 12h16m-6-6 6 6-6 6"/></svg>
			</a>
		<?php endif; ?>
		</div>
	</section>
	<?php if ( $recipes_available ) : ?>
	<section class="home-search" aria-label="<?php esc_attr_e( 'Recipe search', 'recipe-manager' ); ?>">
		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="home-recipe-search"><?php esc_html_e( 'Search recipes', 'recipe-manager' ); ?></label>
			<div class="home-search-controls">
				<svg class="home-search-icon" viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="10" cy="10" r="7"/><path d="m15 15 6 6"/></svg>
				<input id="home-recipe-search" type="search" name="s" placeholder="<?php esc_attr_e( 'Search recipes…', 'recipe-manager' ); ?>" required>
				<input type="hidden" name="post_type" value="rm_recipe">
				<svg class="home-search-flourish" viewBox="0 0 130 40" width="130" height="40" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 32C28 16 55 44 82 27 98 17 104 5 97 5c-5 0-5 8-5 8s-3-10-8-8c-9 4 7 31 24 27 7-1 12-2 19-5"/></svg>
				<button type="submit" class="button-primary"><?php esc_html_e( 'Search', 'recipe-manager' ); ?></button>
			</div>
		</form>
	</section>
	<?php
	get_template_part( 'template-parts/category-cards' );
	$featured = new WP_Query( array(
		'post_type' => 'rm_recipe', 'post_status' => 'publish', 'posts_per_page' => 3,
		'no_found_rows' => true, 'ignore_sticky_posts' => true,
		'meta_key' => 'rm_featured', 'meta_value' => '1',
		'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' ),
	) );
	if ( $featured->have_posts() ) : ?>
	<section class="home-section home-featured-section" aria-label="<?php esc_attr_e( 'Featured recipes', 'recipe-manager' ); ?>">
		<div class="home-recipe-grid">
		<?php while ( $featured->have_posts() ) : $featured->the_post();
			$featured_ids[] = get_the_ID();
			get_template_part( 'template-parts/recipe-card', null, array( 'featured' => true ) );
		endwhile; ?>
		</div>
	</section>
	<?php endif;
	wp_reset_postdata();
	$recent = new WP_Query( array(
		'post_type' => 'rm_recipe', 'post_status' => 'publish', 'posts_per_page' => 5,
		'no_found_rows' => true, 'ignore_sticky_posts' => true,
		'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' ),
	) );
	if ( $recent->have_posts() ) : ?>
	<section class="home-section home-all-recipes" aria-labelledby="home-all-title">
		<header class="home-all-heading">
			<svg class="home-all-leaves" viewBox="0 0 64 48" width="64" height="48" fill="none" aria-hidden="true" focusable="false"><path d="M31 39C9 40 5 22 4 9c17 0 28 8 27 30Zm4-2C30 17 42 5 60 4c-2 18-9 31-25 33Z" fill="currentColor"/><path d="m14 19 18 22M49 15 32 41" stroke="var(--paper)" stroke-width="1.5"/></svg>
			<h2 id="home-all-title"><?php esc_html_e( 'All Recipes', 'recipe-manager' ); ?></h2>
		</header>
		<div class="home-recipe-grid">
		<?php while ( $recent->have_posts() ) : $recent->the_post();
			get_template_part( 'template-parts/recipe-card', null, array( 'compact' => true ) );
		endwhile; ?>
		<?php if ( $archive_url ) : ?>
		<a class="home-all-link" href="<?php echo esc_url( $archive_url ); ?>">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/see-all-recipes.webp' ); ?>" alt="<?php esc_attr_e( 'See All Recipes', 'recipe-manager' ); ?>" width="900" height="985" loading="lazy">
		</a>
		<?php endif; ?>
		</div>
	</section>
	<?php elseif ( ! $featured_ids ) : ?>
		<p class="home-empty"><?php esc_html_e( 'Recipes are coming soon. Come back for something delicious.', 'recipe-manager' ); ?></p>
	<?php endif;
	wp_reset_postdata();
	else : ?>
		<p class="home-empty"><?php esc_html_e( 'The recipe collection will be available soon.', 'recipe-manager' ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
