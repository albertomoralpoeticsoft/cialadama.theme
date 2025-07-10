<?php

use FileBird\Model\Folder as FolderModel;

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
        if(!$metadatos) { continue; }
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

function cialadama_processalbum(WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try { 

    $params = $req->get_params();
    $albumtitle = $params['albumtitle'];    

    $GPname = 'Google Photos';
    $GP_folder = FolderModel::newOrGet($GPname, 0);
    $albumfolder = FolderModel::newOrGet($albumtitle, $GP_folder['id']);

    $res->set_data($albumfolder);

  } catch (Exception $e) {
    
    $res->set_status($e->getCode());
    $res->set_data($e->getMessage());
  }

  return $res;
}

function cialadama_processimage(WP_REST_Request $req) {

  /*
  {
    "folder": "/home/fqnztkn/www/wp-content/uploads/google-photos/albums/A GALOPE (Marc Valls)",
    "foldername": "A GALOPE (Marc Valls)",
    "albumtitle": "A GALOPE (Marc Valls)",
    "images": [
        "DSC04362.jpg",
        ...
    ],
    "image": "DSC04362.jpg",
    "fbfolder": 187
  }
  */
  
  $res = new WP_REST_Response();

  try { 

    $params = $req->get_params();
    $srcimage = $params['folder'] . '/' . $params['image'];
    $destfolder = $params['albumtitle'];

    $uploaddir = wp_upload_dir();
    $custom_upload_base_path = $uploaddir['basedir'] . '/google-filebird/' . $destfolder;
    $custom_upload_base_url  = $uploaddir['baseurl'] . '/google-filebird/' . $destfolder;

    if (!is_dir($custom_upload_base_path)) {
        
      mkdir( $custom_upload_base_path, 0755, true );
    }

    $destination_path = $custom_upload_base_path . '/' . $params['image'];
    $attachment_url   = $custom_upload_base_url . '/' . $params['image'];

    if(file_exists($destination_path)) {

      throw new Exception('File still processed.', 200);
    }

    if (!copy( $srcimage, $destination_path)) {

      throw new Exception('Failed to copy the file to the custom folder.', 500);
    }
    $file_type = wp_check_filetype(basename( $destination_path ), null);
    $attachment = array(
      'guid'           => $attachment_url,
      'post_mime_type' => $file_type['type'],
      'post_title'     => preg_replace( '/\.[^.]+$/', '', basename($destination_path)),
      'post_content'   => '',
      'post_status'    => 'inherit',
    );
    $attach_id = wp_insert_attachment($attachment, $destination_path);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attach_id, $destination_path);
    wp_update_attachment_metadata($attach_id, $attach_data);

    FolderModel::setFoldersForPosts([$attach_id], $params['fbfolder']);

    $res->set_data($destination_path);

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
      'processalbum',
      array(
        array(
          'methods'  => 'post',
          'callback' => 'cialadama_processalbum',
          'permission_callback' => '__return_true'
        )
      )
    );  

    register_rest_route(
      'cialadama',
      'processimage',
      array(
        array(
          'methods'  => 'POST',
          'callback' => 'cialadama_processimage',
          'permission_callback' => '__return_true'
        )
      )
    );  
  }
);