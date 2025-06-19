<?php

require 'guzzle/vendor/autoload.php';
use GuzzleHttp\Client;
require 'firebase/vendor/autoload.php';
use Firebase\JWT\JWT;

function cialadama_gsheets_getclienttoken($scope) { 

  $creds = json_decode(file_get_contents(__DIR__ . '/cia-la-dama-fab3d868c375.json'), true);
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

require_once(dirname(__FILE__) . '/creategallery.php');
require_once(dirname(__FILE__) . '/googlefolders.php');
require_once(dirname(__FILE__) . '/gsheets.php');
require_once(dirname(__FILE__) . '/pagesgalleries.php');