# Rapid
Rapid is a PHP web framework for HTTP request and response routing.

Rapid provides a puwerfully simple interface for routing methods and requests to resource controllers. A command line interface is included to test and perform requests without the use of a web server.

The documentation for this repository is in the works.

## BASIC EXAMPLE

    $host = 'example.com';
    $baseurl = '/';
    $routes = [
      array('GET','/',function($app){
        echo 'Home Page';
      }),
    ];
    require_once '/path/to/rapid.php';
    $app = new Rapid($host,$baseurl,$routes);
    $app->Run();
    
## SETUP

## THE $app OBJECT

## THE REQUEST

## ROUTING

## THE RESPONSE
