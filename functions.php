<?php
/**
 * Author: Ole Fredrik Lie
 * URL: http://olefredrik.com
 *
 * FoundationPress functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 1.0.0
 */

/** Various clean up functions */
require_once( 'library/cleanup.php' );

/** Required for Foundation to work properly */
require_once( 'library/foundation.php' );

/** Register all navigation menus */
require_once( 'library/navigation.php' );

/** Add menu walkers for top-bar and off-canvas */
require_once( 'library/menu-walkers.php' );

/** Create widget areas in sidebar and footer */
require_once( 'library/widget-areas.php' );

/** Return entry meta information for posts */
require_once( 'library/entry-meta.php' );

/** Enqueue scripts */
require_once( 'library/enqueue-scripts.php' );

/** Add theme support */
require_once( 'library/theme-support.php' );

/** Add Nav Options to Customer */
require_once( 'library/custom-nav.php' );

/** Change WP's sticky post class */
require_once( 'library/sticky-posts.php' );

/** If your site requires protocol relative url's for theme assets, uncomment the line below */
// require_once( 'library/protocol-relative-theme-assets.php' );

// Custom filter function to modify default gallery shortcode output
// Courtesy of http://robido.com/wordpress/wordpress-gallery-filter-to-modify-the-html-output-of-the-default-gallery-shortcode-and-style/
function my_post_gallery( $output, $attr ) {
 
	// Initialize
	global $post, $wp_locale;
 
	// Gallery instance counter
	static $instance = 0;
	$instance++;
 
	// Validate the author's orderby attribute
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( ! $attr['orderby'] ) unset( $attr['orderby'] );
	}
 
	// Get attributes from shortcode
	extract( shortcode_atts( array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'itemtag'    => 'dl',
		'icontag'    => 'dt',
		'captiontag' => 'dd',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => ''
	), $attr ) );
 
	// Initialize
	$id = intval( $id );
	$attachments = array();
	if ( $order == 'RAND' ) $orderby = 'none';
 
	if ( ! empty( $include ) ) {
 
		// Include attribute is present
		$include = preg_replace( '/[^0-9,]+/', '', $include );
		$_attachments = get_posts( array( 'include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
 
		// Setup attachments array
		foreach ( $_attachments as $key => $val ) {
			$attachments[ $val->ID ] = $_attachments[ $key ];
		}
 
	} else if ( ! empty( $exclude ) ) {
 
		// Exclude attribute is present 
		$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
 
		// Setup attachments array
		$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
	} else {
		// Setup attachments array
		$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
	}
 
	if ( empty( $attachments ) ) return '';
 
	// Filter gallery differently for feeds
	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment ) $output .= wp_get_attachment_link( $att_id, $size, true ) . "\n";
		return $output;
	}
 
	// Filter tags and attributes
	$itemtag = tag_escape( $itemtag );
	$captiontag = tag_escape( $captiontag );
	$columns = intval( $columns );
	$itemwidth = $columns > 0 ? floor( 100 / $columns ) : 100;
	$float = is_rtl() ? 'right' : 'left';
	$selector = "gallery-{$instance}";
 
	// Filter gallery CSS
	$output = apply_filters( 'gallery_style', "
		<style type='text/css'>
			#{$selector} {
				margin: auto;
			}
			#{$selector} .gallery-item {
				float: {$float};
				margin-top: 10px;
				text-align: center;
				width: {$itemwidth}%;
			}
			#{$selector} img {
				border: 2px solid #cfcfcf;
			}
			#{$selector} .gallery-caption {
				margin-left: 0;
			}
		</style>
		<!-- see gallery_shortcode() in wp-includes/media.php -->
		<div id='$selector' class='gallery galleryid-{$id}'>"
	);
 
	// Iterate through the attachments in this gallery instance
	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
 
		// Attachment link
		$link = isset( $attr['link'] ) && 'file' == $attr['link'] ? wp_get_attachment_link( $id, $size, false, false ) : wp_get_attachment_link( $id, $size, true, false ); 
 
		// Start itemtag
		$output .= "<{$itemtag} class='gallery-item large-4 columns'>";
 
		// icontag
		$output .= "
		<{$icontag} class='gallery-icon'>
			$link
		</{$icontag}>";

 		$title = trim($attachment->post_title);

 		if ( $title ) {
 
			// title
			$output .= "
			<{$captiontag} class='gallery-title'>
				<h2>" . $title . "</h2>
			</{$captiontag}>";
 
		}

		if ( $captiontag && trim( $attachment->post_excerpt ) ) {
 
			// captiontag
			$output .= "
			<{$captiontag} class='gallery-caption'>
				" . wptexturize($attachment->post_excerpt) . "
			</{$captiontag}>";
 
		}
 
		// End itemtag
		$output .= "</{$itemtag}>";
 
		// Line breaks by columns set
		if($columns > 0 && ++$i % $columns == 0) $output .= '<br style="clear: both">';
 
	}
 
	// End gallery output
	$output .= "
		<br style='clear: both;'>
	</div>\n";
 
	return $output;
 
}
 
// Apply filter to default gallery shortcode
add_filter( 'post_gallery', 'my_post_gallery', 10, 2 );

?>