<?php

// This test is a template for building a Rapid web application.

// Host Address
$host = 'example.com';

// Require Rapid Web Framework
require_once '../src/rapid.php';

// Assign Routes
$routes = [
 
  // GET on / returns an HTML text response. By default the response status code is 200.
  array('GET','/',function($app) {
    $app->Send('home page');
  }),

  // GET on a resource returns the resource as a parameter within a JSON document. 
  array('GET','/:resource',function($app){
    $message = $app->request['params'];
    $app->SendJSON($message);
  }),

  // GET on a subresource returns the resource and subresource as paramaters within a JSON document.
  array('GET','/:resource/:subresource',function($app){
    $message = $app->request['params'];
    $app->SendJSON($message);
  }),

  // POST on ANY resource returns the body of the request within a JSON document and a 201 status code.
  array('POST','*',function($app){
    http_response_code('201');
    $message = $app->request['body'];
    $app->SendJSON($message);
  }),

  // ANY METHOD on ANY OTHER resource returns the full request as a JSON document and a 404 staus code.
  array('*','*',function($app){
    http_response_code('405');
    $message = $app->request;
    $app->SendJSON($message);
  }),

];

// Create new $app object
$app = new Rapid($host,$routes);

// Add middleware to properties of the $app object
$app->settings = ['custom'=>'This is a custom settings','app_title'=>'My App'];

// Route the request and generate a response
$app->Run();
