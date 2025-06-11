<?php
/**
 * Astra Child Cia La Dama Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child Cia La Dama
 * @since 1.0.0
 */

/* Log */

function core_log($display) { 

  $text = is_string($display) ? $display : json_encode($display, JSON_PRETTY_PRINT);

  file_put_contents(
    WP_CONTENT_DIR . '/core_log.txt',
    $text . PHP_EOL,
    FILE_APPEND
  );
}

/* Admin */

add_filter( 'login_display_language_dropdown', '__return_false' );

add_action(
  'init', 
  function () { 
    
    /* Disable patterns option */ 

    unregister_block_pattern('gutenslider/pattern-testimonial-slider');
    unregister_block_pattern_category('gutenslider');

    remove_theme_support( 'core-block-patterns' );
  }
);

// Scripts

add_action(
  'admin_enqueue_scripts',
  function () {
    
    wp_enqueue_style(
      'tpyts_admin_styles', 
      get_stylesheet_directory_uri() . '/admin.css',
      array(),
      filemtime(get_stylesheet_directory() . '/admin.css')
    );
  }
);

/* Enqueue styles */

add_action( 
	'wp_enqueue_scripts', 
	function () {

    wp_enqueue_script(
      'astra-child-cialadama-theme-js', 
      get_stylesheet_directory_uri() . '/main.js',
      array('jquery'), 
      filemtime(get_stylesheet_directory() . '/main.js'),
      true
    );

		wp_enqueue_style( 
			'astra-child-cialadama-theme-css',
			get_stylesheet_directory_uri() . '/main.css', 
			array('astra-theme-css'), 
			filemtime(get_stylesheet_directory() . '/main.css'),
			'all' 
		);
	}, 
	15 
);

/* Theme setup */

/* Show reusable blocks */

add_action( 
  'admin_menu', 
  function () {
    add_menu_page( 
      'Reusable Blocks', 
      'Reusable Blocks', 
      'edit_posts', 
      'edit.php?post_type=wp_block', 
      '', 
      'dashicons-editor-table', 
      22 
    );
  }
);

// Hide admin bar & patterns!

add_action(
  'after_setup_theme',
  function () {

    add_post_type_support('page', 'excerpt');

    remove_theme_support( 'core-block-patterns' );

    show_admin_bar(false);
  }
);

add_filter(
  'intermediate_image_sizes_advanced', 
  function($sizes) {

    return array_intersect_key(
      $sizes, 
      array_flip(['thumbnail', 'medium', 'large'])
    );
  }
);

// Head clean!

require_once(dirname(__FILE__) . '/cleanhead.php');

// Api!

require_once(dirname(__FILE__) . '/api/galleries.php');
require_once(dirname(__FILE__) . '/api/process.php');
require_once(dirname(__FILE__) . '/api/gsheets.php');

// Gallery hacks!

require_once(dirname(__FILE__) . '/shortcode.php');
