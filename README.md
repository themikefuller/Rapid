# Rapid
Rapid is a PHP web framework for HTTP request and response routing.

Rapid provides a powerfully simple interface for routing methods and requests to resource controllers. A command line interface is included to test and perform requests without the use of a web server.

Please take note that the current version differs heavily from earlier commits. Branches will be established to separate out more official version releases soon. The documentation for this repository is **still** a work in progress.

## BASIC EXAMPLE

    $host = 'example.com';
    $routes = [
      array('GET','/',function($app){
        echo 'Home Page';
      }),
    ];
    require_once '/path/to/rapid.php';
    $app = new Rapid($host,$routes);
    $app->Run();
    
## SETUP

Require rapid.php and create a new instance of the Rapid class.

The $host and $routes parameters used in the previous example are optional. Routes can be added after the $app object is created. Routes are required for the class to function properly. Without them, this robot has no purpose.

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

The $app object the $routes property, the request property and a number of methods for manipulating those properties.

The two main methods are AddRoutes() and Run().

### AddRoutes($routes)

This method accepts an array containing a single route, or an array containing multiple route arrays.

Single Route

    // Add a single route
    $home = array('GET','/',function($app) {
        echo 'Home Page';
    });
    $app->AddRoutes($home);

Multiple Routes

    // Add multiple routes at once
    $routes = [
            
        ['GET','/',function($app){
            echo 'Home Page';
        }],
        
        ['GET','/users',function($app){
            echo 'List of Users';
        }],
        
        ['GET','/users/:username',function($app){
            echo "Profile for " . $app->request['params']['username'];
        }],    

    ];
    $app->AddRoutes($routes);

Routes can be added after the $app object has been created. See the **ROUTING** section for advanced routing techniques.

### $app->Run();

The $app->Run() method routes the request to the appropriate route and executes it. Run thisa fter all routes and middleware have been added to the object.

    $app->Run();

By default this method does not return any value. The route can be the controller or the jumping off point to a controller or view. However, if the route ends with a return the value can be expressed with the $app->Run() method.

This is perfectly acceptable.

    $app = new Rapid;
    $routes = [
        'GET','/', function($app) {
            return 'Home Page';
        }
    ];
    $app->AddRoutes($routes);
    $output = $app->Run();
    echo $output;
    
## THE REQUEST

Coming Soon.

## ROUTING

Coming Soon.

## WILDCARD ROUTES

Coming Soon.

## THE RESPONSE

Coming Soon.

## COMMAND LINE INTERFACE

Rapid can be run from the command line for testing and debugging purposes. The parameters are enter after the filename that is being executed. The command line interface switches are modeled after cURL.

##CLI Examples

GET request on /users

    php app.php /users
    
POST request on /login with urlencoded body

    php app.php /login -x POST -d "username=admin&password=secret"

POST request on /login with JSON body

    php app.php /login -x POST -d '{"username":"admin","password":"secret"}'

GET request on /users with Authorization header

    php app.php /users -h "Authorization: Bearer abcd1234"
    
GET request on /users with multiple query string parameters.

    php app.php '/users?name=admin&profile=true'
    
Notice that the resource is placed within quotes. The & symbol in command line arguments has a habit of attempting to execute multiple commands, as is its nature. Putting quotes around the argument will provide the intended result.

## Additional CLI information

There should be one header defined per -h argument. Use multiple -h switches for multiple headers. Headers are entered as they would be in cURL. The key proceeds the value and the two are separated by a colon and space.

    php app.php -h "Key: Value" /resource

Data can be sent as an URLencoded string or as JSON. If the data is JSON, the entire JSON object should be enclosed within a pair of single quotes.

    php app.php -d "key=value" /resource
    php app.php -d '{"key":"value"}' /resource

The resource can be placed at the beginning or the end of a command line request. If the resource contains multiple query parameters, the entire resource should be enclosed within quotes or double quotes.

    php app.php /resource -h "Authorization: 1234"
    php app.php -h "Authorization: 1234" /resource
    php app.php "/resource?ok=yep&key=value" -h "Authorization: 1234"

If the -x switch is not used, the method will default to GET.

    php app.php /

File uploads can not be tested from command line.


## USING CORS

Cross Origin Resource Sharing can be enabled for specific domains. Here is an array containing a list of acceptable origins. 

    $allowed = [
        'https://www.example.com',
        'https://example.com',
        'https://api.example.com'
    ];
    
Pass the array into the AllowCORS() method.

    $app->AllowCORS($allowed); 

During an Ajax request, the user's web browser will typical require a response from an OPTIONS method on the destination server and check that the originating site (origin) is allowed to make the request. This route will answer all OPTIONS requests with a "NO CONTENT" response and the appropriate origin in the header.

    $app->addRoutes(['OPTIONS','*',function($app){
    http_response_code('204');
    }]);
    
## ADDITIONAL INFORMATION

Currently, file uploads in Rapid are only fully supported by the HTTP POST method.
