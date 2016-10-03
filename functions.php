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

?>