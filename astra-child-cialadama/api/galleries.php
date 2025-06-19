<?php

require_once(get_stylesheet_directory() . '/pretty/vendor/autoload.php');

use Wa72\HtmlPrettymin\PrettyMin;
use FileBird\Model\Folder as FolderModel;

function cialadama_test (WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try {     

    $res->set_data('test');

  } catch (Exception $e) {
    
    $res->set_status($e->getCode());
    $res->set_data($e->getMessage());
  }

  return $res;
}

function cialadama_testsimilartext (WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try { 
    
    $checktext = 'Formación Regular Barcelona 24-25 (Olga Segura)';
    $testtext = $req->get_param('testtext');
    $similarity = similar_text($checktext, $testtext);
    $levenshtein = levenshtein($checktext, $testtext);

    $res->set_data([
      'similarity' => $similarity,
      'perc' => $perc,
      'levenshtein' => $levenshtein
    ]);

  } catch (Exception $e) {
    
    $res->set_status($e->getCode());
    $res->set_data($e->getMessage());
  }

  return $res;
}

function cialadama_testcontentdom (WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try {   

    $pages = get_pages();  
    $shortcodes = []; 
    $uniquetitles = []; 
    $repeatedtitles = [];
    foreach($pages as $page) {

      $content = $page->post_content;
      $matches = null;
      preg_match_all( 
        '/' . get_shortcode_regex(['gallery']) . '/', 
        $content, 
        $matches, 
        PREG_SET_ORDER
      );

      if(count($matches) > 0) {
        
        $testcontent = $page->post_content;

        $contenthtml = '<html><head><meta 
        charset="UTF-8"><meta 
        http-equiv="Content-Type" content="text/html; charset=utf-8">
        </head><body>' .
          $testcontent .
        '</body></html>';
      
        $remove = ["\r\n", "\n", "\r", "\t", "\s"];
        $cleancontent = str_replace($remove, '', $contenthtml);

        $contentdom = new DOMDocument('1.0');
        $contentdom->substituteEntities = false;
        libxml_use_internal_errors(true);
        $contentdom->loadHTML($cleancontent);
        libxml_use_internal_errors(false);
        $contentdomx = new DOMXPath($contentdom);
      
        foreach ($contentdomx->query('//comment()') as $comment) {

          $comment->parentNode->removeChild($comment);
        }
      
        foreach ($contentdomx->query('//text()') as $text) {

          $texttext = $text->textContent;

          if(str_starts_with($texttext, '[gallery')) {

            $title = $text->previousSibling->textContent;

            if(in_array($title, $uniquetitles)) {

              $repeatedtitles[] = $title;

            } else {

              $uniquetitles[] = $title;
            }

            $shortcodes[] = [
              'shortcode' => $text->textContent,
              'titlename' =>  $text->previousSibling->nodeName,
              'title' =>  $text->previousSibling->textContent
            ];
          }
        }
      }
    }

    // $pm = new PrettyMin();
    // $pm->load($contentdom)->indent();
    // unset($pm);

    // $result = $contentdom->saveHTML();
    
    $res->set_data([
      'count' => count($shortcodes),
      'shortcodes' => $shortcodes,
      'uniquetitles' => $uniquetitles,
      'repeatedtitles' => $repeatedtitles
    ]);

  } catch (Exception $e) {
    
    $res->set_status($e->getCode());
    $res->set_data($e->getMessage());
  }

  return $res;
}

function cialadama_pagesgalleries (WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try {   

    $pages = get_pages(); 
    $pageswithgalleries = [];

    foreach($pages as $page) {

      $content = $page->post_content;

      $matches = null;
      preg_match_all( 
        '/' . get_shortcode_regex() . '/',
        $content, 
        $matches, 
        PREG_SET_ORDER
      );

      if(count($matches) > 0) {
        
        $contenthtml = '<html><head><meta 
        charset="UTF-8"><meta 
        http-equiv="Content-Type" content="text/html; charset=utf-8">
        </head><body>' .
          $content .
        '</body></html>';
      
        $remove = ["\r\n", "\n", "\r", "\t", "\s"];
        $cleancontent = str_replace($remove, '', $contenthtml);

        $contentdom = new DOMDocument('1.0');
        $contentdom->substituteEntities = false;
        libxml_use_internal_errors(true);
        $contentdom->loadHTML($cleancontent);
        libxml_use_internal_errors(false);
        $contentdomx = new DOMXPath($contentdom);

        $galleries = [];
      
        foreach ($contentdomx->query('//comment()') as $comment) {

          $comment->parentNode->removeChild($comment);
        }
      
        foreach ($contentdomx->query('//text()') as $text) {

          $texttext = $text->textContent;

          if(str_starts_with($texttext, '[gallery')) {

            $title = $text->previousSibling->textContent;

            $galleries[] = $title;
          }
        }

        if(count($galleries)) {

          $pageswithgalleries[] = [
            'title' => $page->post_title,
            'url' => 'https://cialadama.com/' . $page->post_name,
            'galleries' => $galleries
          ];
        }
      }
    }   

    $saved = file_put_contents(
      __DIR__ . '/pageswithgalleries.json',
      json_encode($pageswithgalleries, JSON_PRETTY_PRINT)
    );
    
    $res->set_data($pageswithgalleries);

  } catch (Exception $e) {
    
    $res->set_status($e->getCode());
    $res->set_data($e->getMessage());
  }

  return $res;
}

function cialadama_createshortcodelist (WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try {     

    $pages = get_pages();
    $pageswithgallery = [];
    $galleries = 0;
    foreach($pages as $page) {

      $content = $page->post_content;
      $matches = null;
      preg_match_all( 
        '/' . get_shortcode_regex() . '/', 
        $content, 
        $matches, 
        PREG_SET_ORDER
      );
      $shortcodes = [];

      if(count($matches) > 0) {
        
        $galleries += count($matches);

        foreach( $matches as $shortcode ) {

          $shortcodes[] = $shortcode[0];
        }

        $pageswithgallery[] = [
          'title' => $page->post_title,
          'galleriescount' => count($shortcodes),
          'galleries' => $shortcodes
        ];
      }
    }

    $res->set_data([
      'count' => count($pageswithgallery),
      'galleries' => $galleries,
      'pages' => $pageswithgallery
    ]);

  } catch (Exception $e) {
    
    $res->set_status($e->getCode());
    $res->set_data($e->getMessage());
  }

  return $res;
}

function cialadama_googlefolders_list() {
  
  $uploaddir = wp_upload_dir();
  $basedir = $uploaddir['basedir'];
  $strpos = strpos($basedir, '/sites');
  $googlephotosalbumsdir = substr($basedir, 0, $strpos) . '/google-photos/albums/';
  $albumsfolders = scandir($googlephotosalbumsdir);
  $albumsdirs = [];
  foreach($albumsfolders as $foldername) {

    $folder = $googlephotosalbumsdir . $foldername;

    if(
      $foldername !== '.'
      &&
      $foldername !== '..'
      &&
      is_dir($folder)
    ) {

      $metadatos = file_get_contents($folder . '/metadatos.json');
      $datos = json_decode($metadatos);
      $title = $datos->title;
      $files = scandir($folder);
      $images = [];
      foreach($files as $filename) {

        if(
          $filename !== '.'
          &&
          $filename !== '..'
          &&
          !str_contains($filename, 'supplemental-metadata.json')
          &&
          $filename !== 'metadatos.json'
        ) {

          $images[] = $filename;

        }
      }

      $albumsdirs[] = [
        'folder' => $folder,
        'foldername' => $foldername,
        'albumtitle' => $title,
        'images' => $images
      ];
    }
  }

  return $albumsdirs;
}

function cialadama_createfolderslist (WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try { 
    
    $jsonfile = __DIR__ . '/googlefolderslist.json';

    $albumsdirs = cialadama_googlefolders_list();

    $saved = file_put_contents(
      $jsonfile,
      json_encode($albumsdirs, JSON_PRETTY_PRINT)
    );

    $res->set_data($albumsdirs);

  } catch (Exception $e) {
    
    $res->set_status($e->getCode());
    $res->set_data($e->getMessage());
  }

  return $res;
}

function cialadama_googlefolders(WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try { 
    
    $googlefolderslist = cialadama_googlefolders_list();
    $googlefolders = [];
    foreach($googlefolderslist as $googlefolder) {

      if($googlefolder['albumtitle']) {

        $googlefolders[] = $googlefolder['albumtitle'];
      }
    }    

    $saved = file_put_contents(
      __DIR__ . '/googlefolderslist.json',
      json_encode($googlefolders, JSON_PRETTY_PRINT)
    );

    $res->set_data([
      'count' => count($googlefolders),
      'albums' => $googlefolders
    ]);

  } catch (Exception $e) {
    
    $res->set_status($e->getCode());
    $res->set_data($e->getMessage());
  }

  return $res;
}

function cialadama_creategalleries (WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try {     

    $GPname = 'Google Photos';
    $GP_folder = FolderModel::newOrGet($GPname, 0);

    $albumsdirs = cialadama_googlefolders_list();  
    $testdir = $albumsdirs[0];
    $folder = $testdir['folder'];
    $albumtitle = $testdir['albumtitle'];
    $images = $testdir['images'];
    
    $albumfolder = FolderModel::newOrGet($albumtitle, $GP_folder['id']);

    $testimages = [];

    foreach($images as $imagename) {

      $srcfile = $folder . '/' . $imagename;
      $wp_filetype = wp_check_filetype( $srcfile, null );
      $attachment = [
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($imagename),
        'post_content' => '',
        'post_status' => 'inherit'
      ];

      $attach_id = wp_insert_attachment( $attachment, $srcfile );
      require_once( ABSPATH . 'wp-admin/includes/image.php' );
      $attach_data = wp_generate_attachment_metadata( $attach_id, $srcfile );
      wp_update_attachment_metadata( $attach_id, $attach_data );

      $testimages[] = [
        'file' => $srcfile,
        'attachment' => $attachment
      ];
    }

    $res->set_data($testimages);

  } catch (Exception $e) {
    
    $res->set_status($e->getCode());
    $res->set_data($e->getMessage());
  }

  return $res;
}

add_action(
  'rest_api_init',
  function () {

    register_rest_route(
      'cialadama',
      'test',
      array(
        array(
          'methods'  => 'GET',
          'callback' => 'cialadama_test',
          'permission_callback' => '__return_true'
        )
      )
    );

    register_rest_route(
      'cialadama',
      'testsimilartext',
      array(
        array(
          'methods'  => 'POST',
          'callback' => 'cialadama_testsimilartext',
          'permission_callback' => '__return_true'
        )
      )
    );

    register_rest_route(
      'cialadama',
      'testcontentdom',
      array(
        array(
          'methods'  => 'GET',
          'callback' => 'cialadama_testcontentdom',
          'permission_callback' => '__return_true'
        )
      )
    );

    register_rest_route(
      'cialadama',
      'createshortcodelist',
      array(
        array(
          'methods'  => 'GET',
          'callback' => 'cialadama_createshortcodelist',
          'permission_callback' => '__return_true'
        )
      )
    );

    register_rest_route(
      'cialadama',
      'createfolderslist',
      array(
        array(
          'methods'  => 'GET',
          'callback' => 'cialadama_createfolderslist',
          'permission_callback' => '__return_true'
        )
      )
    );

    register_rest_route(
      'cialadama',
      'pagesgalleries',
      array(
        array(
          'methods'  => 'GET',
          'callback' => 'cialadama_pagesgalleries',
          'permission_callback' => '__return_true'
        )
      )
    );

    register_rest_route(
      'cialadama',
      'googlefolders',
      array(
        array(
          'methods'  => 'GET',
          'callback' => 'cialadama_googlefolders',
          'permission_callback' => '__return_true'
        )
      )
    );

    register_rest_route(
      'cialadama',
      'creategalleries',
      array(
        array(
          'methods'  => 'GET',
          'callback' => 'cialadama_creategalleries',
          'permission_callback' => '__return_true'
        )
      )
    );
  }
);