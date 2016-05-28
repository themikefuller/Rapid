<?php

// This test is a template for building a Rapid web application.

// Host Address
$host = 'example.com';

// Require Rapid Web Framework
require_once '../src/rapid.php';

// Assign Routes
$routes = [


  // GET on /users/:username sets $app->request['params']['username'] to :username
  array('GET','/users/:username',function($app){
    http_response_code('200');
    $message = $app->request;
    $json = json_encode([$message], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo $json;
  }),

  // POST on ANY resource returns the body of the request
  array('POST','*',function($app){
    http_response_code('200');
    $message = $app->request['body'];
    $json = json_encode([$message], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo $json;
  }),


  // ANY METHOD on ANY OTHER resource returns the full request
  array('*','*',function($app){
    http_response_code('200');
    $message = $app->request;
    $json = json_encode([$message], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo $json;
  }),

];

// Create new $app object
$app = new Rapid($host,$routes);

// Add middleware to properties of the $app object
$app->settings = ['custom'=>'This is a custom settings','app_title'=>'My App'];

// Route the request and generate a response
$app->Run();
