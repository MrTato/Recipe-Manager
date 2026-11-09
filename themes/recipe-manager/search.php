<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( 'rm_recipe' === get_query_var( 'post_type' ) ) {
	get_template_part( 'template-parts/recipe-browser' );
} else {
	get_template_part( 'index' );
}
