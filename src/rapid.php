<?php

class Rapid {

    public $routes;
    public $request;

    public function __construct($host = 'localhost',$baseurl='/',$routes=[]) {
        $this->request = $this->TheRequest($host,$baseurl);
        $this->routes = [];
        $this->AddRoutes($routes);
    }

    function TheRequest($host=null,$baseurl='/') {
        // Blank, Null, Empty and defacto request values
        $protocol = 'http';
        $cookies = [];
        $headers = [];
        $body = [];
        $querystring = '';
        $query = [];
        $method = 'GET';    
        $resource = '/';
        $uri = [];
        $requesturi = '';
        $files = [];

        // Command Line Interface Arguments
        global $argv;
        if (isset($argv[0])) {  
            if (isset($argv[1])) {
                $resource = explode('?',$argv[1]);
                if (isset($resource[1])) {
                    $querystring = $resource[1];
                    parse_str($resource[1],$query);
                    $requesturi = $resource[0] . '?' . $resource[1];
                    $resource = $resource[0];
                } else {
                    $resource = $resource[0];
                }
            }

            if (isset($argv[2])) {
                $method = strtoupper($argv[2]);
            }

            if (isset($argv[3])) {
                if (strpos($argv[3],'{') === 0) {
                    $body = json_decode($argv[3],true);
                } else {
                    parse_str($argv[3],$body);
                }
            }
            if (isset($argv[4])) {
                parse_str($argv[4],$headers);
                $headers = array_change_key_case($headers,CASE_LOWER);
            }
        }

        // Web Server Specific Variables
        if (is_null($host) and isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }

        if (is_null($host)) {
            $host = 'localhost';
        } else {
            $host = rtrim($host,'/');
        }

        if ($baseurl == '/') {
            $baseurl = '';
        }

        if (isset($_SERVER['HTTPS'])) {
            $protocol = 'https';
        }

        if (isset($_SERVER['REQUEST_URI']) and !isset($argv[1])) {
            $resource = explode('?',$_SERVER['REQUEST_URI']);
            if (isset($resource[1])) {
                $querystring = $resource[1];
                parse_str($resource[1],$query);
                $requesturi = $resource[0] . '?' . $resource[1];
                $resource = $resource[0];
            } else {
                $resource = $resource[0];
            }
        }

        if (substr($resource, 0, strlen($baseurl)) == $baseurl) {
            $resource = substr($resource, strlen($baseurl));
        }
              
        if ($resource != '/') {
            $resource = rtrim($resource,'/');
            $uri = explode('/',$resource);
            array_shift($uri);
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $requesturi = $_SERVER['REQUEST_URI'];
        }
        if (!isset($requesturi) or empty($requesturi)) {
            $requesturi = $resource;
        }

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }

        if (isset($_COOKIE) and !empty($_COOKIE)) {
            $cookies = $_COOKIE;
        }

        if (empty($cookies) and isset($headers['cookies'])) {
            parse_str($headers['cookies'],$cookies);
        }

        if (isset($_GET) and !empty($_GET)) {
            $query = $_GET;
        }

        if (isset($_POST) and !empty($_POST)) {
            $body = $_POST;
        }

        if (isset($_FILES)) {
            $files = $_FILES;
        }  

        $input = file_get_contents("php://input");
        if (strpos($input,'{') === 0) {
            $body = json_decode($input,true);
        }

        if (strpos($input,'{') !== 0 and $body == []) {
            parse_str($input,$body);
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        // Request Array
        $request = [
          'hosturl'=>$protocol . '://' . $host . $baseurl,
          'resourceurl'=>$protocol . '://' . $host . $baseurl . $resource,
          'currenturl'=>$protocol . '://' . $host . $baseurl .  rtrim($requesturi,'/'),
          'protocol'=>$protocol,
          'host'=>$host . $baseurl,
          'method'=>$method,
          'resource'=>$resource,
          'uri'=>$uri,
          'requesturi'=>$requesturi,
          'querystring'=>$querystring,
          'query'=>$query,
          'body'=>$body,
          'cookies'=>array_change_key_case($cookies,CASE_LOWER),
          'files'=>$files,
          'headers'=>array_change_key_case($headers,CASE_LOWER),
        ];
        return $request;
    }

    public function AddRoutes($routes) {
        if (isset($routes[0]) and !is_array($routes[0])) {
            $routes = [$routes];
        }
        foreach ($routes as $route) {
            $new['method']   = strtoupper($route[0]);
            $new['resource'] = $route[1];
            $new['action']   = $route[2];
            if ($new['resource'] == '*' and $new['method'] == '*') {
                $this->routes[] = $new;
            } else {
                array_unshift($this->routes,$new);
            }
        }
    }

    public function GetRoutes() {
        return $this->routes;
    }

    public function Action($method,$resource) {
        $action = null;
        $params = [];
        $routes = $this->routes;
        $resource = trim($resource, '/');
        foreach ($routes as $route) {
            if ($route['method'] == $method or $route['method'] == '*') {
                $test = $route['resource'];
                $test = trim($test,'/');
                if ($test == $resource or $test == '*') {
                    $action = $route['action'];
                    break;
                }
                $resources = explode('/',$resource);
                $test = explode('/',$test);
                if (sizeof($resources) == sizeof($test)) {
                    $count = 0;
                    while ($count <= (sizeof($resources) - 1 )) {
                        if ($resources[$count] == $test[$count] or (isset($test[$count][0]) and $test[$count][0] == ':')) {
                            if ($test[$count][0] == ':') {
                                $len = strlen($test[$count]);
                                $keyname = substr($test[$count],1,$len);
                                $paramval = explode('?',$resources[$count]);
                                $params[$keyname] = $paramval[0];
                            }
                            $ok = true;
                        } else {
                            $ok = false;
                            break;
                        }
                        $count++;
                    }
                    if ($ok) {
                        $action = $route['action'];
                        break;
                    }
                }
            }
        }
        $response['action'] = $action;
        $response['params'] = $params;
        return $response;
    }

    public function AllowCORS($allowed) {
        $methods = 'GET, PUT, POST, DELETE, OPTIONS, PATCH';
        if (!is_array($allowed)) {
            $temp = $allowed;
            unset($allowed);
            $allowed[] = $temp;
        }
        header("Allow: $methods");
        header("Access-Control-Allow-Headers:Origin, Authorization, Content-Type, Accept");
        if (isset($_SERVER['HTTP_ORIGIN']) and in_array($_SERVER['HTTP_ORIGIN'],$allowed)) {
            $origin = $_SERVER['HTTP_ORIGIN'];
            header("Access-Control-Allow-Origin: $origin");
        }
        header("Access-Control-Allow-Methods: $methods");
    }

    public function Run() {
        $app = $this;
        $response = null;
        $action_res = $this->Action($app->request['method'],$app->request['resource']);
        $action = $action_res['action'];
        $app->request['params'] = $action_res['params'];
        if (isset($action)) {
            if (is_array($action)) {
                if (is_callable($action)) {
                    $class = new $action[0]($app);
                    $function = [$class,$action[1]];
                }
            } else {
                if (is_callable($action)) {
                    $function = $action;
                }
            }
            if (isset($function)) {
                $response = call_user_func($function,$app);
            }
        }
        return $response;
    }

}
