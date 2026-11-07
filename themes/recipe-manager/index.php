<?php
/** Basic fallback for posts, pages, and archives. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>
<main id="main" class="site-main">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
	<article <?php post_class( 'entry' ); ?>>
		<?php if ( is_singular() ) : ?>
			<h1><?php the_title(); ?></h1>
			<?php the_content(); ?>
		<?php else : ?>
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php the_excerpt(); ?>
		<?php endif; ?>
	</article>
<?php endwhile; the_posts_pagination(); else : ?>
	<p><?php esc_html_e( 'No content found.', 'recipe-manager' ); ?></p>
<?php endif; ?>
</main>
<?php get_footer(); ?>
