<?php
/** Structured recipe, readable with or without JavaScript. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>
<main id="main" class="site-main">
<?php while ( have_posts() ) : the_post(); ?>
<?php if ( post_password_required() ) : ?>
	<h1><?php the_title(); ?></h1>
	<?php echo get_the_password_form(); continue; ?>
<?php endif;
if ( ! function_exists( 'rm_get_recipe_ingredients' ) ) : ?>
	<h1><?php the_title(); ?></h1><?php the_content(); continue; ?>
<?php endif;
$id = get_the_ID();
$ingredients = rm_get_recipe_ingredients( $id );
$steps = rm_get_recipe_steps( $id );
$servings = rm_get_recipe_servings( $id );
$description = rm_theme_text( $id, 'rm_description' );
$notes = rm_theme_text( $id, 'rm_notes' );
$units = \RecipeManager\Core\get_units();
?>
<article <?php post_class( 'recipe' ); ?> data-recipe data-servings="<?php echo esc_attr( $servings ); ?>">
	<header class="recipe-hero <?php echo has_post_thumbnail() ? '' : 'recipe-hero--text'; ?>">
		<?php if ( has_post_thumbnail() ) : ?>
		<div class="recipe-photo"><?php the_post_thumbnail( 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></div>
		<?php endif; ?>
		<div class="recipe-intro">
			<div class="recipe-categories"><?php rm_theme_terms( $id, 'rm_recipe_category' ); ?></div>
			<h1><?php the_title(); ?></h1>
			<?php if ( '' !== trim( $description ) ) : ?><div class="recipe-description"><?php echo nl2br( esc_html( $description ) ); ?></div><?php endif; ?>
			<dl class="recipe-facts">
				<div><dt><label for="recipe-servings"><?php esc_html_e( 'Servings', 'recipe-manager' ); ?></label></dt><dd>
					<span data-servings-display><?php echo esc_html( $servings ); ?></span>
					<span class="servings-control" data-servings-control hidden>
						<button type="button" data-servings-decrease aria-label="<?php esc_attr_e( 'Fewer servings', 'recipe-manager' ); ?>">−</button>
						<input id="recipe-servings" type="number" min="1" max="9007199254740991" step="1" value="<?php echo esc_attr( $servings ); ?>">
						<button type="button" data-servings-increase aria-label="<?php esc_attr_e( 'More servings', 'recipe-manager' ); ?>">+</button>
					</span>
				</dd></div>
				<?php foreach ( array( 'rm_prep_minutes' => __( 'Prep', 'recipe-manager' ), 'rm_cook_minutes' => __( 'Cook', 'recipe-manager' ), 'total' => __( 'Total', 'recipe-manager' ) ) as $key => $label ) :
					$minutes = 'total' === $key ? rm_get_recipe_total_minutes( $id ) : rm_recipe_meta_integer( get_post_meta( $id, $key, true ) ); ?>
				<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( sprintf( __( '%s min', 'recipe-manager' ), number_format_i18n( $minutes ) ) ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		</div>
	</header>
	<p class="screen-reader-text" role="status" aria-live="polite" data-status></p>
	<div class="recipe-body">
		<section class="recipe-ingredients" aria-labelledby="ingredients-heading">
			<h2 id="ingredients-heading"><?php esc_html_e( 'Ingredients', 'recipe-manager' ); ?></h2>
			<div class="ingredient-actions" data-controls hidden>
			<button type="button" class="measurement-cycle" data-measurement-cycle aria-describedby="measurement-help" hidden><?php esc_html_e( 'Measurements:', 'recipe-manager' ); ?> <span data-measurement-label><?php esc_html_e( 'Default', 'recipe-manager' ); ?></span></button>
				<button type="button" class="button-primary" data-print><?php esc_html_e( 'Print recipe', 'recipe-manager' ); ?></button>
				<button type="button" class="button-reset" data-reset aria-label="<?php esc_attr_e( 'Reset servings and measurements', 'recipe-manager' ); ?>" title="<?php esc_attr_e( 'Reset servings and measurements', 'recipe-manager' ); ?>"><svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7v5h-5" /><path d="M20 12a8 8 0 1 0-2.34 5.66" /></svg></button>
			</div>
			<p id="measurement-help" class="measurement-help" data-measurement-help hidden><?php esc_html_e( 'Click to cycle: Default → Mass → Volume → Optional unit. Unavailable measurements keep their default amount.', 'recipe-manager' ); ?></p>
			<div class="ingredient-deck"><div class="ingredient-card" data-ingredient-card>
			<?php $previous_group = null; foreach ( $ingredients as $row ) :
				if ( $row['group'] !== $previous_group ) {
					if ( null !== $previous_group ) { echo '</ul>'; }
					if ( '' !== $row['group'] ) { echo '<h3 class="group-heading">' . esc_html( $row['group'] ) . '</h3>'; }
					echo '<ul class="ingredient-list">';
					$previous_group = $row['group'];
				}
				$type = null !== $row['mass_quantity'] ? 'mass' : ( null !== $row['volume_quantity'] ? 'volume' : 'optional' ); ?>
				<li data-ingredient="<?php echo esc_attr( wp_json_encode( $row ) ); ?>"><span class="ingredient-copy"><span class="ingredient-amount"><?php echo esc_html( rm_theme_quantity( $row[ $type . '_quantity' ] ) . ' ' . $row[ $type . '_unit' ] ); ?></span> <span><?php echo esc_html( $row['name'] ); ?></span><?php if ( $row['note'] ) : ?><small><?php echo esc_html( $row['note'] ); ?></small><?php endif; ?></span></li>
			<?php endforeach; if ( null !== $previous_group ) { echo '</ul>'; } ?>
			</div></div>
		</section>
		<section class="recipe-instructions" aria-labelledby="instructions-heading">
			<h2 id="instructions-heading"><?php esc_html_e( 'Instructions', 'recipe-manager' ); ?></h2>
			<?php $previous_group = null; foreach ( $steps as $index => $step ) :
				if ( $step['group'] !== $previous_group ) {
					if ( null !== $previous_group ) { echo '</ol>'; }
					if ( '' !== $step['group'] ) { echo '<h3 class="group-heading">' . esc_html( $step['group'] ) . '</h3>'; }
					echo '<ol role="list" class="step-list" start="' . esc_attr( $index + 1 ) . '">';
					$previous_group = $step['group'];
				} ?>
				<li class="recipe-step <?php echo $step['image_id'] ? 'has-image' : ''; ?>">
					<span class="step-number" aria-hidden="true"><?php echo esc_html( $index + 1 ); ?></span>
					<?php if ( $step['image_id'] ) : ?><div class="step-photo"><?php echo wp_get_attachment_image( $step['image_id'], 'medium', false, array( 'loading' => 'lazy' ) ); ?></div><?php endif; ?>
					<div class="step-text"><?php echo nl2br( esc_html( $step['text'] ) ); ?></div>
				</li>
			<?php endforeach; if ( null !== $previous_group ) { echo '</ol>'; } ?>
		</section>
	</div>
	<?php if ( '' !== trim( $notes ) ) : ?>
	<section class="recipe-notes" aria-labelledby="notes-heading"><h2 id="notes-heading"><?php esc_html_e( 'Notes & tips', 'recipe-manager' ); ?></h2><div><?php echo nl2br( esc_html( $notes ) ); ?></div></section>
	<?php endif; ?>
	<div class="recipe-tags"><?php rm_theme_terms( $id, 'rm_recipe_tag' ); ?></div>
</article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
