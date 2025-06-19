<?php

require_once(get_stylesheet_directory() . '/pretty/vendor/autoload.php');

use FileBird\Model\Folder as FolderModel;

function cialadama_getfolderimages($folder) {

  $files = scandir($folder);
  $images = [];
  foreach($files as $filename) {

    if(
      $filename !== '.'
      &&
      $filename !== '..'
      &&
      !str_contains($filename, '.json')
    ) {

      $images[] = $filename;
    }
  }

  return $files;
}

function cialadama_googlefolders(WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try { 
    
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
            !str_contains($filename, '.json')
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
    
    $saved = file_put_contents(
      __DIR__ . '/googlefolderslist.json',
      json_encode($albumsdirs, JSON_PRETTY_PRINT)
    );

    $res->set_data($albumsdirs);

  } catch (Exception $e) {
    
    $res->set_status($e->getCode());
    $res->set_data($e->getMessage());
  }

  return $res;
}

function cialadama_creategallery (WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try { 

    $params = $req->get_params();

    // {
    //   "folder": "/home/fqnztkn/www/wp-content/uploads/google-photos/albums/A GALOPE (Marc Valls)",
    //   "foldername": "A GALOPE (Marc Valls)",
    //   "albumtitle": "A GALOPE (Marc Valls)",
    //   "images": [...],
    //   "innerHTML": ""
    // }
    
    $albumtitle = $params['albumtitle'];

    $GPname = 'Google Photos';
    $GP_folder = FolderModel::newOrGet($GPname, 0);
    $albumfolder = FolderModel::newOrGet($albumtitle, $GP_folder['id']);

    $folder = $params['folder']; 
    $images = $params['images'];

    $uploaddir = wp_upload_dir();
    $basedir = $uploaddir['basedir'];
    $filebirdbasedir = $basedir . '/google-filebird/';
    $destfolder = $filebirdbasedir . $albumtitle;  
    if(is_dir($destfolder)) {

      $album = cialadama_getfolderimages($folder);
      $dest = cialadama_getfolderimages($destfolder);

      $res->set_data([
        'album' => count($album),
        'dest' => count($dest)
      ]);

      return $res;

    } else {

      mkdir($destfolder, 0755, true);
    }

    $imageids = [];
    foreach($images as $imagename) {

      $srcfile = $folder . '/' . $imagename;
      $destfile = $destfolder . '/' . $imagename;

      copy($srcfile, $destfile);

      $wp_filetype = wp_check_filetype($destfile, null );
      $attachment = [
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($imagename),
        'post_content' => '',
        'post_status' => 'inherit'
      ];

      $attach_id = wp_insert_attachment($attachment, $destfile);
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      $attach_data = wp_generate_attachment_metadata($attach_id, $destfile);
      wp_update_attachment_metadata($attach_id, $attach_data);

      $imageids[] = $attach_id;
    }    

    FolderModel::setFoldersForPosts($imageids, $albumfolder['id']);

    $res->set_data($images);

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
      'creategallery',
      array(
        array(
          'methods'  => 'POST',
          'callback' => 'cialadama_creategallery',
          'permission_callback' => '__return_true'
        )
      )
    );
  }
);