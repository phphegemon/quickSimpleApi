<?php
/*
* simpleQuickAPI one script simple api template
* author: phphegemon@gmail.com
*/

// debug
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
//echo 'Hello world! Working on php version ' . phpversion();

/**
 * Class DB_CONNECTION
 */
class DB_CONNECTION
{
    // some DB
    const MYSQL_HOST = "localhost";
    const MYSQL_USER = "localhost";
    const MYSQL_PASS = "password";
    const MYSQL_DB   = "localhost";

    private static $instance; // stores the MySQLi instance

    private function __construct() { } // block directly instantiating

    private function __clone() { } // block cloning of the object

    public static function call()
    {
        // create the instance if it does not exist
        if(!isset(self::$instance))
        {
            // the MYSQL_* constants should be set to or
            //  replaced with your db connection details
            self::$instance = new MySQLi(self::MYSQL_HOST, self::MYSQL_USER, self::MYSQL_PASS, self::MYSQL_DB);
            if(self::$instance->connect_error)
            {
                throw new Exception('MySQL SOU connection failed: ' . self::$instance->connect_error);
            }
        }
        // return the instance
        return self::$instance;
    }
}

/**
 * phpSimpleApi
 */
class phpSimpleApi
{
    private $db;
    private $method;
    private $dateNow;
    private $response;
    private $error;

    const API_SALT = 'XSW@CDE#xsw2cde3';
    const API_METHODS = ['apiTest'];

    public function getDb()
    {
        return $this->db;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getDateNow()
    {
        return $this->dateNow;
    }

    public function getresponse()
    {
        return $this->response;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setDb()
    {
        $this->db = DB_CONNECTION::call();
        return $this;
    }

    public function setMethod($method)
    {   
        $this->method = $method;
        return $this;
    }

    public function setDateNow()
    {
        $d = new DateTime();
        $this->dateNow = $d->format('Y-m-d');
        return $this;
    }

    public function setResponse($response)
    {
        $this->response = json_encode($response);
        return $this;
    }

    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    public function __construct()
    {
        $this->setDateNow();
        $this->setDb();
    }

    public function verifyRequest($request)
    {
        $validKey = $this->checkApiKey($request['apiKey']);
        if ($validKey === false) {
            $this->setError('Invalid api key.')->setResponse(['response'=>$this->getError(), 'error'=>true]);
            return $this;
        }
        $validMethod = $this->checkMethod($request['apiMethod']);
        if ($validMethod === false) {
            $this->setError('Invalid method.')->setResponse(['response'=>$this->getError(), 'error'=>true]);
            return $this;
        }
        $this->executeMethod($request['apiMethod'], $request['data']);
        return $this;
    }

    public function executeMethod($method, $data = false)
    {
        switch ($method) {
            case 'apiTest':
                $this->setResponse(['response'=>'Test api method', 'error'=>false]);
                break;
            /*
                implement wanted methods
            */
            default:
                $this->setError('Method definition not found')->$this->setResponse(['response'=>$this->getError(), 'error'=>true]);
        }
        return $this;
    }

    public function checkApiKey($key)
    {
        $hash = $this->getApiKey();
        if($key === $hash) {
            return true;
        }
        return false;
    }

    public function getApiKey()
    {
        return hash('sha256', self::API_SALT . $this->getDateNow());
    }

    public function checkMethod($method)
    {
        if (in_array($method, self::API_METHODS)) {
            return true;
        }
        return false;
    }
}

if (empty($_REQUEST)) {
    $_REQUEST = json_decode(file_get_contents('php://input'), true);
}

if (!empty($_REQUEST) && !empty($_REQUEST['apiKey']) && !empty($_REQUEST['apiMethod'])) {
    try {
        $db = DB_CONNECTION::call();
        $api = new phpSimpleApi();
        echo $api->verifyRequest($_REQUEST)->getResponse();
    } catch (Exception $e) {
        echo json_encode(array("response"=>"Db connection error.", "error"=>true));
        die();
    }
} else {
    echo 'Error. Invalid request!';
}
die();
