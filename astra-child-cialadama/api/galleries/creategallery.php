<?php

use FileBird\Model\Folder as FolderModel;

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