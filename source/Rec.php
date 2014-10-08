<?php

include "RecComponent.php";
include "RecController.php";
include "RecPDO.php";
include "RecModel.php";
include "RecWidget.php";

class Rec
{
    public static $debug = true;
    public static $applicationName = 'Web Application'; // Default Application name

    public static $url = null;      // /rec/
    public static $urlFull = null;  // test.loc/rec/
    public static $urlPart = null;
    public static $urlDomain = null;

    public static $path = null;     // D:/server/domains/test.loc/rec/
    public static $pathApp = null;  // D:/server/domains/test.loc/rec/app/

    private $recUrls = array();

    public $request = null;
    private static $prefix = '';
    public static $controller = null;
    public static $action = null;
    public static $params = null;

    public function __construct($appPath = null, $debug = true)
    {
        self::$debug = $debug;
        self::$urlPart = substr($_SERVER['PHP_SELF'], 0, -9);
        self::$urlDomain = $_SERVER['HTTP_HOST'];
        self::$urlFull = self::$urlDomain . self::$urlPart;
        self::$url = self::$urlPart;

        self::$path = substr($_SERVER['SCRIPT_FILENAME'], 0, -9);
        self::$pathApp = ($appPath == null) ? self::$path : self::$path . $appPath.'/';
        $this->request = rtrim(str_replace(self::$urlPart, '', $_SERVER['REQUEST_URI']), '/');
    }

    public function setApplicationName($name=null)
    {
        if($name != null)
            self::$applicationName = $name;
    }

    public function urlDefault($class)
    {
        $this->recUrls['recUrlDefault'] = $this->checkClass($class);
    }

    public function urlNotFound($class)
    {
        $this->recUrls['recUrlNotFound'] = $this->checkClass($class);
    }

    public $currentRequest = null;

    public function urlAdd($class, $url)
    {
        $param = null;
        if ($paramPos = strpos($url, '{')) {
            $_url = $url;
            $url = substr($url, 0, $paramPos - 1);
            $param = substr($_url, $paramPos);
        }

        $paramValues = array(' '=>'', '/'=>'\/*', '{n}'=>'(\d*)', '{w}'=>'([a-z_]*)', '{p}'=>'(\w*)',
                             '{!n}'=>'(\d+)', '{!w}'=>'([a-z_]+)', '{!p}'=>'(\w+)');

        $this->recUrls[$url] = array(
            'class' => $this->checkClass($class),
            'param' => $param,
            'regexp' => '|^('.strtr($url,$paramValues).')\/*'.strtr($param,$paramValues).'$|',
        );
    }


    public function determineRunParams()
    {
        /* Determine params to run applicetion */
        $params = array();
        $classMethods = explode('/',$this->recUrls['recUrlNotFound']);

        if($this->request === '' || $this->request === '/')
          $classMethods = explode('/',$this->recUrls['recUrlDefault']);

        if($this->request == 'error404' || $this->request ==' 404')
          $classMethods = explode('/',$this->recUrls['recUrlNotFound']);

        //$pregMatchCount = 0;
        //$pregMatchResults = array();

        foreach (array_keys($this->recUrls) as $kr) 
        {
          if($kr=='recUrlDefault' || $kr=='recUrlNotFound') continue;

            if (stripos($this->request, $kr) === 0) {
                if(preg_match($this->recUrls[$kr]['regexp'], $this->request, $result)) {    
                    $classMethods = explode('/',$this->recUrls[$kr]['class']);
                    if(count($result)>2) {
                        array_shift($result);
                        array_shift($result);
                        $params = (isset($result)) ? $result : array();
                    }
                //$pregMatchCount ++;
                //$pregMatchResults[] = $kr;
                }
            }
        }
        
        $controllerPath = self::$pathApp.'/Controllers/'.$classMethods[0].'.php';

        if(!file_exists($controllerPath)) {
          if(self::$debug)
            self::ExceptionError('File not exists!',$controllerPath.'<br>  Call: '.$classMethods[0].'::'.$classMethods[1].'()<br>  Request URL: '.$this->request.' ', true);

          $classMethods[0] = 'Controller';
          $classMethods[1] = 'error404';
          $controllerPath = 'RecController.php';
        }

        self::$controller = $classMethods[0];
        self::$action = $classMethods[1];
        self::$params = $params;

        return $controllerPath;
    }



    public function run()
    {
      // start autoload classes
      $this->autoloadClasses();
      $definedControllerPath = $this->determineRunParams();

      require_once ($definedControllerPath);

      if(class_exists( self::$controller ))
      {
          $controllerObj = new self::$controller;
          if (method_exists($controllerObj, self::$action))
          {
              if(self::$action=='error404')
                header("HTTP/1.0 404 Not Found");

              if (empty(self::$params)) {
                  call_user_func(array($controllerObj, self::$action));
                  exit;
              } else {

                  call_user_func_array(array((object)$controllerObj, self::$action), self::$params );
                  exit;
              }

          } else {
              Request::redirect(self::$url.'/error404',false,'404');
          }

      } else {
        if(self::$debug)
          self::ExceptionError('Class not exists','Class path: '.$definedControllerPath.'<br>Class name: '.self::$controller);
        Request::redirect(self::$url.'/error404',0,'404');
      }

    }


    
    /**
     * Авто загрузка классов с директорий системы и приложения.
     */
    private function autoloadClasses()
    {
        //spl_autoload_register(array($this, '__'));
        spl_autoload_register(array($this, 'autoloadModelsClasses'));
        spl_autoload_register(array($this, 'autoloadComponentsClasses'));
        spl_autoload_register(array($this, 'autoloadWidgetsClasses'));
    }
    /**
     * Авто загрузка моделей приложения.
     */
    private function autoloadModelsClasses($className)
    {
        $file = self::$pathApp . 'Models/' . $className . '.php';
        if (is_file($file))
            include_once($file);
    }
    private function autoloadComponentsClasses($className)
    {
        $file = self::$pathApp . 'Components/' . $className . '.php';
        if (is_file($file))
            include_once($file);
    }
    private function autoloadWidgetsClasses($className)
    {
        $file = self::$pathApp . 'Widgets/' . $className . '.php';
        if (is_file($file))
            include_once($file);
    }
    

    /* check second params in class url */
    public function checkClass($class)
    {
        if (strpos($class, '/') === false)
            return $class . '/index';
        else
            return $class;
    }


    public function clearUrl($string)
    {
        return $string;
    }



    /**
     * Метод вызова ошибки исключения
     *
     * @param string $errorMsg Сообщения о ошибке
     * @param null $fileName Конкретные данные, например имя файла
     * @param bool $die
     */
    public static function ExceptionError($errorMsg = 'File not exists', $fileName = null, $die = true)
    {
        try {
            throw new Exception("TRUE.");
        } catch (Exception $e) {
            echo "<!doctype html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Rec: " . $errorMsg . "</title>
    <style>
        body,html{
            margin:0;padding:0;background-color: #00002C;
            font-family: 'Ubuntu Condensed', 'Ubuntu', sans-serif;
        }
        .box{
            min-height: 600px;
            padding: 10px;
            font-size: 11px;
            color:#FFF;
            background: #0033FF;
        }
        .description{display: block; padding: 10px; color:#828282;}
    </style>
</head>
<body>
    <div  class='box'>

        <h2 style='font-size: 14px; color:#FF9900;'>Warning! throw Exception. </h2>

        <h2>Message: " . $errorMsg . " </h2>";

            if ($fileName != null):
                echo "<code style='display: block; padding: 10px; font-size: 12px; font-weight: bold; font-family: Consolas, Courier New, monospace; color:#CBFEFF; background: #000066'>"
                    . $fileName .
                    "</code>";
            endif;

            echo "<div class='description'>
            Function setup: " . $e->getFile() . "
            <br>
            Line: " . $e->getLine() . "
        </div>

        <h3>Trace As String: </h3>
        <code style='display: block; padding: 10px; font-size: 12px; font-weight: bold; font-family: Consolas, Courier New, monospace; color:#CBFEFF; background: #000066'>
            " . str_replace('#', '<br> ', $e->getTraceAsString()) . "<br>
        </code>

        <h3>Code: </h3>
        <code style='display: block; padding: 10px; font-size: 12px; font-weight: bold; font-family: Consolas, Courier New, monospace; color:#CBFEFF;  background: #000066'>
            " . $e->getCode() . "
        </code>

    </div>
</body>
</html>";
        if ($die) die();
        }

    } // END ExceptionError


} // END Class Rec




/**
 * Class Request
 */
class Request
{

    public static function post($name=null, $clear=false)
    {
        if(!empty($_POST[$name]))
        {
            if($clear)
                return self::clear($_POST[$name]);
            else
                return trim($_POST[$name]);
        }
        else
        {
            return null;
        }
    }


    public static function get($name=null, $clear=false)
    {
        if(!empty($_GET[$name]))
        {
            if($clear)
                return self::clear($_GET[$name]);
            else
                return trim($_GET[$name]);
        }
        else
        {
            return null;
        }
    }


    public static function value($name, $clear=false)
    {
        if(!empty($_POST[$name]))
        {
            return self::post($name, $clear);
        }

        if(!empty($_GET[$name]))
        {
            return self::get($name, $clear);
        }
    }


    public static function clear($dataClear)
    {
        if(is_array($dataClear)){
            foreach ($dataClear as $_dataClear)
                self::clear($_dataClear);

        }elseif(is_string($dataClear)){
            return trim( strip_tags( html_entity_decode( $dataClear ) ) );
        }
    }

    public static function session($name=null, $setValue=null, $clear=false)
    {
        if(!isset($_SESSION))
            session_start();

        if($setValue===null)
        {
            if(!empty($_SESSION[$name]))
            {
                if($clear)
                    return self::getSession($_SESSION[$name], $clear);
                else
                    return trim($_SESSION[$name]);
            }
        }
        else
        {
            self::getSession($name, $clear);
            if(isset($_SESSION[$name]))
                return true;
            else
                return false;
        }

        return null;
    }

    public static function setSession($name, $setValue=null, $clear=false)
    {
        if(!isset($_SESSION))
            session_start();

        if($clear)
            $setValue = self::clear($setValue);

        if(is_array($setValue))
            $_SESSION = $setValue;
        else
            $_SESSION[$name] = $setValue;
    }

    public static function getSession($name=null, $clear=false)
    {
        if(!isset($_SESSION))
            session_start();

        if(!empty($_SESSION[$name]))
        {
            if($clear)
                return self::clear($_SESSION[$name]);
            else
                return trim($_SESSION[$name]);
        }
        else if($name === null)
        {
            if($clear)
                return self::clear($_SESSION);
            else
                return $_SESSION;
        }
    }


    protected static $expireTime = 3600;
    public static $cookieDecode  = false;

    public static function cookie($key, $value=false, $expire = null, $domain = null, $path = null)
    {
        if($value === false)
            return self::getCookie($key);
        else
            return self::setCookie($key, $value, $expire, $domain, $path);
    }


    public static function setCookie($key, $value, $expire = null, $domain = null, $path = null) {

        if ($expire === null)
            $expire = time() + self::$expireTime;

        if ($domain === null)
            $domain = $_SERVER['HTTP_HOST'];

        if ($path === null);
            $path = str_replace(basename($_SERVER['SCRIPT_FILENAME']), '', $_SERVER['PHP_SELF']);



        if(self::$cookieDecode)
            $value = base64_encode($value);

        return setcookie($key, $value, $expire, $path, $domain);
    }

    public static function getCookie($key)
    {
        if (!empty($_COOKIE[$key]))
        {
            if(self::$cookieDecode)
                return base64_decode($_COOKIE[$key]);
            else
                return $_COOKIE[$key];
        } else {
            return null;
        }
    }


    public static function deleteCookie($key, $domain = null, $path = null) {

        if ($domain === null)
            $domain = $_SERVER['HTTP_HOST'];

        if ($path === null)
            $path = str_replace(basename($_SERVER['SCRIPT_FILENAME']), '', $_SERVER['PHP_SELF']);

        return setcookie($key, false, time() - 3600, $path, $domain);
    }


    /**
     * Check if request is an ajax request
     * @since  3.3.0
     * @return bool true if ajax
     */
    public static function isAjax()
    {
       return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_GET['ajax']);
    }


    public static function redirect($url=null, $delayForce = 0, $code = 302)
    {

        if(self::isAjax()){
            header('HTTP/1.1 401 Unauthorized', true, 401);
            header('WWW-Authenticate: FormBased');
            die();
        }

        if( !(strpos($url,'http://') > -1) )
            $url = Rec::$url.$url;

        if($delayForce===true){
            if (!headers_sent()) {
                header('Location: ' . $url);
            } else {
                echo "<html><head><title>REDIRECT</title></head><body>";
                echo '<script type="text/javascript">';
                echo 'window.location.href="' . $url . '";';
                echo '</script>';
                echo '<noscript>';
                echo '<meta http-equiv="refresh" content="0; url=' . $url . '" />';
                echo '</noscript>';
                echo "</body></html>";
            }
            echo "<!--Headers already!\n-->";
            echo "</body></html>";
            exit;
        }

        if (!headers_sent($file, $line)) {
            if ($delayForce)
                header('Refresh: ' . $delayForce . '; url=' . $url, true);
            else
                header('Location: ' . $url, true, $code);
        } else {
            return true;
        }
    }


    protected static $headerCodes = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );


    public static function sendHeaderCode($code=200)
    {
        $message = self::$headerCodes[$code];
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol . ' ' . $code . ' ' . $message);
    }

}

