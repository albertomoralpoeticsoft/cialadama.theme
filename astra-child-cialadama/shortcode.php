<?php
use FileBird\Classes\Tree;
use FileBird\Classes\Helpers as Helpers;

function poeticsoft_filebird_findfolder_bytitle($title, $folders = NULL) {

  if(!$folders) {

    $folders = Tree::getFolders(null);
  }   

  foreach ($folders as $folder) {

    if (
      isset($folder['text']) 
      && 
      $folder['text'] === $title
    ) {
      
      return $folder;
    }

    if (!empty($folder['children'])) {

      $found = poeticsoft_filebird_findfolder_bytitle($title, $folder['children']);
      
      if ($found) {

        return $found;
      }
    }
  }
}

// add_shortcode(
//   'gallery', 
//   function($atts) {

//     return '<div class="susbstituteshortcode">Galería en construcción</div>';
//   }
// );

add_shortcode(
  'gallery', 
  function($atts) {

    $foldertitle = $atts['folder'];
    $folder = poeticsoft_filebird_findfolder_bytitle($foldertitle);

    $folderid = $folder['id'];
    $imageids = Helpers::getAttachmentIdsByFolderId($folderid);
    $imageslist = array_map(
      function($id) {

        $urlthumb = wp_get_attachment_image_src($id, 'thumbnail');
        $urlview = wp_get_attachment_image_src($id, 'large');

        return '<div
          class="carousel-cell"
          data-view="' . $urlview[0] . '"
        >
          <img data-flickity-lazyload="' . $urlthumb[0] . '" />
        </div>';
      },
      $imageids
    );
    $imageshtml = implode('', $imageslist);

    return '<div 
      class="shortcode-poeticsoft-gallery"
    >
      <div class="galleryview"></div>
      <div class="carousel flickitygallery">' . 
        $imageshtml .
      '</div>' .
    '</div>';
  }
);