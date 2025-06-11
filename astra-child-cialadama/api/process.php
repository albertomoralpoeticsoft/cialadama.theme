<?php

require_once(get_stylesheet_directory() . '/pretty/vendor/autoload.php');

use FileBird\Model\Folder as FolderModel;

function cialadama_creategallery (WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try { 
    
    $albumtitle = $req->get_param('albumtitle');

    $GPname = 'Google Photos';
    $GP_folder = FolderModel::newOrGet($GPname, 0);
    $albumfolder = FolderModel::newOrGet($albumtitle, $GP_folder['id']);

    $uploaddir = wp_upload_dir();
    $basedir = $uploaddir['basedir'];

    $strpos = strpos($basedir, '/sites');
    $uploadbasedir = substr($basedir, 0, $strpos);    
    $googlephotosalbumsdir = $uploadbasedir . '/google-photos/albums/';
    $folder = $googlephotosalbumsdir . $albumtitle;  
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

    // $filebirdbasedir = $basedir . '/google-filebird/';
    // $destfolder = $filebirdbasedir . $albumtitle;  
    // if(!is_dir($destfolder)) {

    //   mkdir($destfolder, 0755, true);
    // }

    // $imageids = [];
    // foreach($images as $imagename) {

    //   $srcfile = $folder . '/' . $imagename;
    //   $destfile = $destfolder . '/' . $imagename;

    //   copy($srcfile, $destfile);

    //   $wp_filetype = wp_check_filetype($destfile, null );
    //   $attachment = [
    //     'post_mime_type' => $wp_filetype['type'],
    //     'post_title' => sanitize_file_name($imagename),
    //     'post_content' => '',
    //     'post_status' => 'inherit'
    //   ];

    //   $attach_id = wp_insert_attachment($attachment, $destfile);
    //   require_once(ABSPATH . 'wp-admin/includes/image.php');
    //   $attach_data = wp_generate_attachment_metadata($attach_id, $destfile);
    //   wp_update_attachment_metadata($attach_id, $attach_data);

    //   $imageids[] = $attach_id;
    // }    

    // FolderModel::setFoldersForPosts($imageids, $albumfolder['id']);

    $res->set_data(is_dir($images));

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