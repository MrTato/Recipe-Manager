<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'recipe-manager' ); ?></a>
<header class="site-header">
	<a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( "Bayardo's Kitchen", 'recipe-manager' ); ?></a>
	<nav aria-label="<?php esc_attr_e( 'Primary navigation', 'recipe-manager' ); ?>">
	<?php if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'depth' => 1 ) );
	} elseif ( post_type_exists( 'rm_recipe' ) ) { ?>
		<ul>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"<?php if ( is_front_page() ) { echo ' aria-current="page"'; } ?>><?php esc_html_e( 'Home', 'recipe-manager' ); ?></a></li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'rm_recipe' ) ); ?>"><?php esc_html_e( 'Recipes', 'recipe-manager' ); ?></a></li>
		</ul>
	<?php } ?>
	</nav>
</header>
