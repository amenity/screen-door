<?php
function screendoor_enqueue_styles() {

    $parent_style = 'twentysixteen-style'; 

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'screendoor_enqueue_styles' );

// Custom favicon Function to Include
function customfavicon_link() {
    echo '<link rel="apple-touch-icon" sizes="180x180" href="' . get_stylesheet_directory_uri() . '/apple-touch-icon.png"/>' . "\n";
    echo '<link rel="icon" type="image/png" href="' . get_stylesheet_directory_uri() . '/favicon-32x32.png" sizes="32x32"/>' . "\n";
    echo '<link rel="icon" type="image/png" href="' . get_stylesheet_directory_uri() . '/favicon-16x16.png" sizes="16x16"/>' . "\n";
    echo '<link rel="manifest" href="' . get_stylesheet_directory_uri() . '/manifest.json">' . "\n";
    echo '<link rel="mask-icon" href="' . get_stylesheet_directory_uri() . '/safari-pinned-tab.svg" color="#5bbad5">' . "\n";
    echo '<meta name="theme-color" content="#ffffff">' . "\n";
}
add_action( 'wp_head', 'customfavicon_link' );

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