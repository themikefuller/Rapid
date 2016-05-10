# Rapid
Rapid is a PHP web framework for HTTP request and response routing.

Rapid provides a puwerfully simple interface for routing methods and requests to resource controllers. A command line interface is included to test and perform requests without the use of a web server.

The documentation for this repository is a work in progress.

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

Require rapid.php and create a new instance of the Rapid class.

The $host, $baseurl, and $routes parameters used in the previous example are optional. Routes can be added after the $app object is created. Routes are required for the class to function properly. Without them, this robot has no purpose.

    require_once 'rapid.php';
    $app = new Rapid;

Add Routes to the $app object

    $routes = [
        array('GET','/',function($app) {
            echo 'Home Page';
        }),
        array('GET','/users',function($app) {
          echo 'User List';
        }),
        array('GET','/users/:username',function($app) {
          echo 'Profile for ' . $app->request['params']['username'];
        }),
    ];
    $app->AddRoutes($routes);

Run the app

    $app->Run();


## THE $app OBJECT

## THE REQUEST

## ROUTING

## THE RESPONSE

## COMMAND LINE INTERFACE
