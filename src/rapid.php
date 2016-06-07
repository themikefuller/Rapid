<?php

class Rapid {

    public $routes;
    public $request;

    public function __construct($host = 'localhost',$routes=[]) {
        $this->request = $this->TheRequest($host);
        $this->routes = [];
        $this->AddRoutes($routes);
    }

    private function TheRequest($host='localhost') {
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

        global $argv;
        if (isset($argv[0])) {
            $cmd = $this->CommandLine($host,$argv);
        } else {
            $cmd = $this->WebServer($host);
        }
 
        $host = $cmd['host'];
        $headers = array_change_key_case($cmd['headers'],CASE_LOWER);
        $body = $cmd['body'];
        $method = $cmd['method'];
        $requesturi = $cmd['requesturi'];
        $protocol = $cmd['protocol'];
        $cookies = $cmd['cookies'];

        if (isset($cmd['files'])) {
            $files = $cmd['files'];
        }

        $querystring = explode('?',$requesturi);
        $resource = $querystring[0];
        array_shift($querystring);
        if (!empty($querystring)) {
            $querystring = implode($querystring,'?');
            parse_str($querystring,$query);
            $querystring = '?' . $querystring;
        } else {
            $querystring = '';
            }

        if ($resource != '/') {
            $resource = rtrim($resource,'/');
            $resource = '/' . $resource;
            $uri = explode('/',$resource);
            array_shift($uri);
        }

        if (empty($cookies) and isset($headers['cookies'])) {
            parse_str($headers['cookies'],$cookies);
        }

        if ($resource != '/') {
            $resource = '/' . ltrim($resource,'/');
            $uri = explode('/',ltrim($resource,'/'));
        }

        $request = [
          'hosturl'=>$protocol . '://' . $host,
          'resourceurl'=>$protocol . '://' . $host . $resource,
          'currenturl'=>$protocol . '://' . $host .  rtrim($requesturi,'/'),
          'protocol'=>$protocol,
          'host'=>$host,
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
        $slash = false;
        foreach ($routes as $route) {
            $new['method']   = strtoupper($route[0]);
            $new['resource'] = $route[1];
            $new['action']   = $route[2];
            if ($new['resource'] == '*' and $new['method'] == '*' and $new['resource'] != '/') {
                $this->routes[] = $new;
            } else {
                if ($new['resource'] != '/') {
                    array_unshift($this->routes,$new);
                } else {
                    $slash = $new;
                }
            }
        }
        if ($slash) {
            array_unshift($this->routes,$slash);
        }
    }

    public function GetRoutes() {
        return $this->routes;
    }

    private function Action($method,$resource) {
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

    public function SendJSON($object) {
        header('Content-Type: application/json');
        echo json_encode($object, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        die();
    }

    public function Send($object) {
        header('Content-Type: text/html');
        echo $object;
        die();
    }

  private function WebServer($host) {

        if ($host == 'localhost') {
            $host = $_SERVER['HTTP_HOST'];
        }


        if (isset($_SERVER['HTTPS'])) {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }

        $requesturi = $_SERVER['REQUEST_URI'];

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }

        if (isset($_COOKIE) and !empty($_COOKIE)) {
            $cookies = $_COOKIE;
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

        $method = $_SERVER['REQUEST_METHOD'];

        $web['host'] = $host;
        $web['resource'] = $resource;
        $web['method'] = $method;
        $web['headers'] = $headers;
        $web['body'] = $body;
        $web['requesturi'] = $requesturi;
        $web['protocol'] = 'http';
        $web['cookies'] = $cookies;
        $web['files'] = $files;

        return $web;

    }



    private function CommandLine($host,$argv) {
        $dswitch = false;
        $hswitch = false;
        $xswitch = false;
        $trip = false;
        $headers = [];
        $data = [];
        $method = 'GET';
        $requesturi = '/';
        $params = [];

        if (isset($argv[1])) {
            $params = $argv;
            array_shift($params);
    
            foreach ($params as $param) {
                if ($dswitch) {
                    $dswitch = false;
                    $data = $param;
                    if ($data[0] == '{') {
                        $data = json_decode($data,true);
                    } else {
                        parse_str($data,$data);
                    }
                    $trip = true;
                }

                if ($hswitch) {
                    $hswitch = false;
                    $hsplit = explode(': ',$param);
                    $hkey = $hsplit[0];
                    $hvalue = null;
                    if (isset($hsplit[1])) {
                        $hvalue = $hsplit[1];
                    $headers[$hkey] = $hvalue;
                    }
                    $trip = true;
                }

                if ($xswitch) {
                    $xswitch = false;
                    $method = strtoupper($param);
                    $trip = true;
                }

                if ($param[0] == '-') {
                    if (isset($param[1]) and strtolower($param[1]) == 'd') {
                        $dswitch = true;
                    }
                    if (isset($param[1]) and strtolower($param[1]) == 'h') {
                        $hswitch = true;
                    }
                    if (isset($param[1]) and strtolower($param[1]) == 'x') {
                        $xswitch = true;
                    }
                } else {
                    if (!$trip) {
                        $requesturi = $param;
                    }
                    $trip = false;
                }
            }
        }

        $cli['host'] = $host;
        $cli['method'] = $method;
        $cli['headers'] = $headers;
        $cli['body'] = $data;
        $cli['requesturi'] = $requesturi;
        $cli['protocol'] = 'http';
        $cli['cookies'] = [];
        return $cli;
    }

}
