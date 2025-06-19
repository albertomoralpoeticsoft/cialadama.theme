<?php

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
  }
);