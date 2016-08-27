<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 1.0.0
 */

 get_header(); ?>

 <?php get_template_part( 'parts/featured-image' ); ?>

 <div id="page" role="main">

 <?php do_action( 'foundationpress_before_content' ); ?>
 
 <!-- Get Featured Photos -->
  <div class="featured-photos">
    <?php /* The loop */
      while ( have_posts() ) : the_post();
          if ( get_post_gallery() ) :
              echo get_post_gallery();
          endif; 
      endwhile; 
      ?>
    <?php wp_reset_postdata();?>
    
  </div>
 <?php do_action( 'foundationpress_after_content' ); ?>

 </div>

 <?php get_footer(); ?>