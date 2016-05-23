<?php

// Host Address
$host = 'example.com';

// Base URL of app
$baseurl = '/';

// Create Routes
$routes = [

  // GET Request on /
  array('GET','/',function($app){
    http_response_code('200');
    $message = $app->request;
    $json = json_encode([$message], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo $json;
  }),

  // GET Request on /users
  array('GET','/users',function($app){
    http_response_code('200');
    $title = 'User List';
    $message = 'This would be a list of users.';
    $json = json_encode([$title,$message], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo $json;
  }),
  
    // GET Request on /users/:username
  array('GET','/users/:username',function($app){
    http_response_code('200');
    $title = 'Profile Page';
    $message = 'This would be a profile for ' . $app->request['params']['username'];
    $json = json_encode([$title,$message], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo $json;
  }),

  // Catchall for any other method or resource request
  array('*','*',function($app){
    http_response_code('404');
    $title = 'Page Not Found';
    $message = 'Page Not Found';
    $json = json_encode([$title,$message], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo $json;
  }),

];

// Require Rapid Web Framework
require_once '../src/rapid.php';

// Create a new instance of the Rapid framework
$app = new Rapid($host,$baseurl,$routes);

// Add middleware to properties of the $app object
$app->settings = ['custom'=>'This is a custom settings','app_title'=>'My App'];

// Route the request and generate a response
$app->Run();
