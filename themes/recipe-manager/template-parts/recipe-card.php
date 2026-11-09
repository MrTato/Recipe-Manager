<?php
/** Shared recipe preview for discovery sections. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$description = rm_theme_text( get_the_ID(), 'rm_description' );
$minutes = function_exists( 'rm_get_recipe_total_minutes' ) ? rm_get_recipe_total_minutes( get_the_ID() ) : 0;
?>
<article <?php post_class( 'home-recipe-card' ); ?>>
	<a class="home-card-image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) :
			the_post_thumbnail( 'medium_large', array( 'alt' => '', 'loading' => 'lazy' ) );
		else : ?><span class="home-card-placeholder"><?php esc_html_e( 'From the kitchen', 'recipe-manager' ); ?></span><?php endif; ?>
	</a>
	<div class="home-card-body">
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( $description ) : ?><p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $description ), 22 ) ); ?></p><?php endif; ?>
		<?php if ( $minutes > 0 ) : ?><p class="home-card-time"><?php printf( esc_html__( '%s min total', 'recipe-manager' ), esc_html( number_format_i18n( $minutes ) ) ); ?></p><?php endif; ?>
	</div>
</article>
