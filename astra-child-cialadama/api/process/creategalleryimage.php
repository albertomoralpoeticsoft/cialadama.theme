<?php

use FileBird\Model\Folder as FolderModel;

function cialadama_creategalleryimage (WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try { 

    $params = $req->get_params();

    // {
    //   "folder": "/home/fqnztkn/www/wp-content/uploads/google-photos/albums/A GALOPE (Marc Valls)",
    //   "foldername": "A GALOPE (Marc Valls)",
    //   "albumtitle": "A GALOPE (Marc Valls)",
    //   "imagename": "imagename"
    // }
    
    $albumtitle = $params['albumtitle'];

    $GPname = 'Google Photos';
    $GP_folder = FolderModel::newOrGet($GPname, 0);
    $albumfolder = FolderModel::newOrGet($albumtitle, $GP_folder['id']);

    $folder = $params['folder'];

    $uploaddir = wp_upload_dir();
    $basedir = $uploaddir['basedir'];
    $filebirdbasedir = $basedir . '/google-filebird/';
    $destfolder = $filebirdbasedir . $albumtitle;
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

    FolderModel::setFoldersForPosts([$attach_id], $albumfolder['id']);

    $res->set_data($imagename);

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
      'creategalleryimage',
      array(
        array(
          'methods'  => 'POST',
          'callback' => 'cialadama_creategalleryimage',
          'permission_callback' => '__return_true'
        )
      )
    );
  }
);