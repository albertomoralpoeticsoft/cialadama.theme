<?php

function cialadama_readgsheet_table(WP_REST_Request $req) {
  
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
    $table = [];
    $page = '';
    
    foreach($values as $value) {

      if($value[0] != '') {

        $page = $value[0];
      };

      if($value[0] == '') {

        $clave = $value[1] ?? null;
        if ($clave) {
          
          $table[$clave][] = [$value[2], $page];
        }
      }
    }      

    $saved = file_put_contents(
      __DIR__ . '/tablepagesgalleries.json',
      json_encode($table, JSON_PRETTY_PRINT)
    );

    $res->set_data([
      'count' => count($table),
      'table' => $table
    ]);

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
      'readgsheet/table',
      array(
        array(
          'methods'  => 'GET',
          'callback' => 'cialadama_readgsheet_table',
          'permission_callback' => '__return_true'
        )
      )
    );
  }
);