<?php

use FileBird\Model\Folder as FolderModel;

function cialadama_pagesgalleries (WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try {   

    $clienttoken = cialadama_gsheets_getclienttoken('https://www.googleapis.com/auth/spreadsheets.readonly');
    $client = $clienttoken['client'];
    $token = $clienttoken['token'];
    $spreadsheetId = '1CTgAiUkt_9fYDdIEvCfG4m_dyw7YHJyS8THtQzI6C_Y';
    $range = 'Galerías!A1:C1000';
    $response = $client->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$range}", [
      'headers' => [
        'Authorization' => "Bearer $token"
      ]
    ]);

    $data = json_decode($response->getBody(), true);
    $values = $data ['values'];
    $tablepagesgalleries = [];
    $page = '';
    
    foreach($values as $value) {

      if($value[0] != '') {

        $page = $value[0];
      };

      if($value[0] == '') {

        $clave = $value[1] ?? null;
        if ($clave) {
          
          $tablepagesgalleries[$clave][] = [$value[2], $page];
        }
      }
    }           

    $saved = file_put_contents(
      __DIR__ . '/tablepagesgalleries.json',
      json_encode($tablepagesgalleries, JSON_PRETTY_PRINT)
    );

    /* *************************************************************** */

    $GPname = 'Google Photos';
    $GP_folder = FolderModel::newOrGet($GPname, 0);

    $pageswithgalleries = [];

    // $pagetestids = [8842]; 
    $pages = get_posts([
      'post_type' => 'page',
      // 'post__in' => $pagetestids,
      'orderby' => 'post__in',
      'numberposts' => -1 
    ]);

    foreach($pages as $page) {

      $content = $page->post_content;

      $matches = null;
      preg_match_all( 
        '/' . get_shortcode_regex(['gallery']) . '/',
        $content, 
        $matches, 
        PREG_SET_ORDER
      );

      if(count($matches) == 0) { continue; }  
      
      $remove = ["\r\n", "\n", "\r", "\t", "\s"];
      $content = str_replace($remove, '', $content);
      
      $contenthtml = '<html><head><meta 
      charset="UTF-8"><meta 
      http-equiv="Content-Type" content="text/html; charset=utf-8">
      </head><body>' .
        $content .
      '</body></html>';
    
      $contentdom = new DOMDocument('1.0');
      $contentdom->substituteEntities = false;
      libxml_use_internal_errors(true);
      $contentdom->loadHTML($contenthtml);
      libxml_use_internal_errors(false);
      $contentdomx = new DOMXPath($contentdom);

      $galleries = [];
    
      foreach ($contentdomx->query('//text()') as $text) {

        $texttext = $text->textContent;

        if(str_contains($texttext, '[gallery')) {

          // LOCALIZACION DEL TITULO
          
          $title = $text->previousSibling->previousSibling->previousSibling->textContent;  
          
          // Todo para esto
          
          $mediafolder = $tablepagesgalleries[$title][0][0];
          $textupdated = '[gallery folder="' . $mediafolder . '"]';

          $text->textContent = $textupdated;

          $galleries[] = [
            'title' => $title,
            'FBFolder' => $mediafolder,
            'texttext' => $texttext,
            'textupdated' => $textupdated
          ];
        }
      }

      if(count($galleries)) {

        $pageswithgalleries[] = [
          'title' => $page->post_title,
          'url' => 'https://cialadama.com/' . $page->post_name,
          'galleries' => $galleries
        ];

        $body = $contentdom->getElementsByTagName('body')->item(0);
        $transformedcontent = '';
        foreach ($body->childNodes as $child) {

          $transformedcontent .= $contentdom->saveHTML($child);
        }

        wp_update_post([
          'ID' => $page->ID,
          'post_content' => $transformedcontent,
        ]);
      }
    }
    
    $res->set_data($pageswithgalleries);

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
      'pagesgalleries',
      array(
        array(
          'methods'  => 'GET',
          'callback' => 'cialadama_pagesgalleries',
          'permission_callback' => '__return_true'
        )
      )
    );
  }
);