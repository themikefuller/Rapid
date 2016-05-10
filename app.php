<?php


// Create $app - Load the web framework and create the $app object to carry throughout the app.
require_once 'rapid.php';

$app = new Rapid();

// Routes - Route methods and resources to functions, class methods, or closure functions.
$routes = array(

    array('GET','/',function($app) {
        $response = ['message'=>'App Home Page'];
        $json = json_encode($response,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        http_response_code('200');
        echo $json;
    }),

    array('*','*',function($app) {
        $response = ['message'=>'Page Not Found'];
        $json = json_encode($response,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        http_response_code('404');
        echo $json;
    }),

);

$app->AddRoutes($routes);

// - Middleware Hook - Add functionality to $app before the request is routed and the response is generated.

// Settings - Add settings as a property of $app
$app->settings = ['app_title'=>'My App','custom'=>'custom settings'];

// Run The App - The request is routed to a response.
$app->Run();
