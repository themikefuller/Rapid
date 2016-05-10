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
    $title = 'Home Page';
    $message = 'This is a homepage';
    $json = json_encode([$title,$message], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo $json;
  }),

  // POST Request on /
  array('POST','/:pages',function($app){
    http_response_code('201');
    $title = 'Home Page';
    $message = $app->settings['app_title'] . ': Response to a POST on /' . $app->request['params']['pages'];
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

// Require Web Framework
require_once 'rapid.php';

// Create a new instance of the Rapid framework
$app = new Rapid($host,$baseurl,$routes);
$app->settings = ['custom'=>'This is a custom settings','app_title'=>'My App'];

// Route the request and generate a response
$app->Run();
