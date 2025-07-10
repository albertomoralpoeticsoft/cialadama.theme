<?php  

// https://chatgpt.com/c/68398ed7-7cbc-8013-afad-80841a34d716

require 'guzzle/vendor/autoload.php';
use GuzzleHttp\Client;
require 'firebase/vendor/autoload.php';
use Firebase\JWT\JWT;

function cialadama_gsheets_getclienttoken($scope) { 

  $creds = json_decode(file_get_contents(__DIR__ . '/cia-la-dama-c48947eed8c4.json'), true);
  $now = time();
  $jwt_payload = [
    'iss' => $creds['client_email'],
    'scope' => $scope,
    'aud' => $creds['token_uri'],
    'exp' => $now + 3600,
    'iat' => $now,
  ];
  $jwt = JWT::encode($jwt_payload, $creds['private_key'], 'RS256');
  $client = new Client();
  $response = $client->post($creds['token_uri'], [
    'form_params' => [
      'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
      'assertion' => $jwt
    ]
  ]);
  $token = json_decode($response->getBody(), true)['access_token']; 

  return [
    'client' => $client,
    'token' => $token  
  ];
}

function cialadama_sheetid(WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try { 

    $clienttoken = cialadama_gsheets_getclienttoken('https://www.googleapis.com/auth/spreadsheets.readonly');
    $client = $clienttoken['client'];
    $token = $clienttoken['token'];
    $spreadsheetId = '1CTgAiUkt_9fYDdIEvCfG4m_dyw7YHJyS8THtQzI6C_Y';
    $response = $client->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}", [
      'headers' => [
        'Authorization' => "Bearer $token"
      ]
    ]);

    $data = json_decode($response->getBody(), true);

    $res->set_data($data);

  } catch (Exception $e) {
    
    $res->set_status($e->getCode());
    $res->set_data($e->getMessage());
  }

  return $res;
}

function cialadama_readgsheet(WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try {    

    $clienttoken = cialadama_gsheets_getclienttoken('https://www.googleapis.com/auth/spreadsheets.readonly');
    $client = $clienttoken['client'];
    $token = $clienttoken['token'];
    $spreadsheetId = '1CTgAiUkt_9fYDdIEvCfG4m_dyw7YHJyS8THtQzI6C_Y';
    $range = 'Galerías!C1:C5';
    $response = $client->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$range}", [
      'headers' => [
        'Authorization' => "Bearer $token"
      ]
    ]);

    $data = json_decode($response->getBody(), true);

    $res->set_data($data);

  } catch (Exception $e) {
    
    $res->set_status($e->getCode());
    $res->set_data($e->getMessage());
  }

  return $res;
}

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

function cialadama_writegsheet(WP_REST_Request $req) {
  
  $res = new WP_REST_Response();

  try {    

    $clienttoken = cialadama_gsheets_getclienttoken();
    $client = $clienttoken['client'];
    $token = $clienttoken['token'];
    $spreadsheetId = '1CTgAiUkt_9fYDdIEvCfG4m_dyw7YHJyS8THtQzI6C_Y';
    
    // Google folders list
    
    $googlefolderslist = json_decode(file_get_contents(__DIR__ . '/googlefolderslist.json'), true);
    $albumscount = count($googlefolderslist);
    $range = "Albums!A1:A$albumscount";
    $values = array_map(
      function($row) {
        
        return [$row];
      },
      $googlefolderslist
    );
    $body = [
      'range' => $range,
      'majorDimension' => 'ROWS',
      'values' => $values
    ];
    // $response = $client->put(
    //   "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/$range?valueInputOption=RAW",
    //   [
    //     'headers' => [
    //       'Authorization' => "Bearer $token",
    //       'Content-Type'  => 'application/json'
    //     ],
    //     'body' => json_encode($body)
    //   ]
    // );
    // $resultfolders = json_decode($response->getBody(), true); 
    
    // Pages galleries
    
    $pagesgalleries = json_decode(file_get_contents(__DIR__ . '/pageswithgalleries.json'), true);
    $data = [];
    $galleryindexes = [];
    $galleryindex = 0;
    foreach($pagesgalleries as $page) {

      $galleryindex++;

      $data[] = [
        $page['title'],
        $page['url']
      ];

      foreach($page['galleries'] as $gallery) {

        $galleryindex++;

        $galleryindexes[] = $galleryindex;

        $data[] = [
          '',
          $gallery
        ];
      }
    }
    $datacount = count($data);
    
    $range = "Galerías!A1:B$datacount";
    $body = [
      'range' => $range,
      'majorDimension' => 'ROWS',
      'values' => $data
    ];
    // $response = $client->put(
    //   "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/$range?valueInputOption=RAW",
    //   [
    //     'headers' => [
    //       'Authorization' => "Bearer $token",
    //       'Content-Type'  => 'application/json'
    //     ],
    //     'body' => json_encode($body)
    //   ]
    // );
    // $resultgalleries = json_decode($response->getBody(), true);  

    // Selectors 

    $requests = [];

    foreach($galleryindexes as $index) {

      $requests[] = [
        [
          'setDataValidation' => [
            'range' => [
              'sheetId' => 0,
              'startRowIndex' => $index -1,
              'endRowIndex' => $index,
              'startColumnIndex' => 2,
              'endColumnIndex' => 3
            ],
            'rule' => [
              'condition' => [
                'type' => 'ONE_OF_RANGE',
                'values' => [
                  ['userEnteredValue' => '=Albums!A1:A' . $albumscount]
                ]
              ],
              'showCustomUi' => true,
              'strict' => true
            ]
          ]
        ]
      ]; 

      $requests[] = [
        [
          'repeatCell' => [
            'range' => [
              'sheetId' => 0,
              'startRowIndex' => $index -1,
              'endRowIndex' => $index,
              'startColumnIndex' => 1,
              'endColumnIndex' => 3
            ],
            'cell' => [
                'userEnteredFormat' => [
                  'backgroundColor' => [
                    'red'   => 0.9,
                    'green' => 1,
                    'blue'  => 0.9,
                    'alpha' => 1
                  ]
                ]
            ],
            'fields' => 'userEnteredFormat.backgroundColor'
          ]
        ]
      ];
    }    

    $responseselectors = $client->post(
      "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId:batchUpdate",
      [
        'headers' => [
          'Authorization' => "Bearer $token",
          'Content-Type'  => 'application/json'
        ],
        'body' => json_encode(['requests' => $requests])
      ]
    );

    $res->set_data([
      // 'albums' => $resultfolders,
      // 'galleries' => $resultgalleries,
      'selectors' => $responseselectors
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
      'sheetid',
      array(
        array(
          'methods'  => 'GET',
          'callback' => 'cialadama_sheetid',
          'permission_callback' => '__return_true'
        )
      )
    );

    register_rest_route(
      'cialadama',
      'readgsheet',
      array(
        array(
          'methods'  => 'GET',
          'callback' => 'cialadama_readgsheet',
          'permission_callback' => '__return_true'
        )
      )
    );

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

    register_rest_route(
      'cialadama',
      'writegsheet',
      array(
        array(
          'methods'  => 'GET',
          'callback' => 'cialadama_writegsheet',
          'permission_callback' => '__return_true'
        )
      )
    );
  }
);