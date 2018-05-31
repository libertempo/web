<?php
namespace includes;

// class SQL, interface with mysqli, it's a singleton, non-static method can be call staticly
// Build for PHP 5.3
class SQL
{
    // singleton
    private static $instance;

    // warper obj
    private static $pdo_obj;

    //=====================
    // singleton
    //=====================

    // singleton pattern, code from php.net
    // fucking parameters ... I don't find a way to use $args and call construtor with it ...
    public static function singleton() {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            require CONFIG_PATH . 'dbconnect.php';

            self::$instance = new $className( $mysql_serveur , $mysql_user, $mysql_pass, $mysql_database);
        }
        return self::$instance;
    }

    public static function singletonWithoutDb()
    {
        require CONFIG_PATH . 'dbconnect.php';
        return new self($mysql_serveur, $mysql_user, $mysql_pass, '');
    }

    public function existsDatabase(string $name) : bool
    {
        require CONFIG_PATH . 'dbconnect.php';
        $instance = new self($mysql_serveur, $mysql_user, $mysql_pass, '');

        $res = $instance->query('SHOW DATABASES');
        foreach ($res->fetch_all() as $database) {
            if ($name === $database['Database']) {
                return true;
            }
        }

        return in_array($name, $res->fetch_all(), true);
    }

    public function initialized() {
        return isset( self::$instance );
    }

    private function __construct(string $server, string $user, string $host, string $database) {
        self::$pdo_obj = new \includes\Database($server, $user, $host, $database);
    }

    public function __clone() { error_handler('Clone is not allowed.', E_USER_ERROR); }

    public function __wakeup() { error_handler('Unserializing is not allowed.', E_USER_ERROR); }

    /**
     * for call staticly dynamic fx (doesn't use instance vars and doesn't use singleton ;-) )
     * @deprecated
     */
    public static function __callStatic($name, $args) {
        self::singleton();
        if (method_exists(self::$instance, $name)) {
            return call_user_func_array([self::$instance, $name], $args);
        }
        if (method_exists(self::$pdo_obj, $name)) {
            return call_user_func_array([self::$pdo_obj, $name], $args);
        }
        throw new \Exception(sprintf('The required method "%s" does not exist for %s', $name, get_class(self::$instance)));
    }

    //=====================
    // warper
    //=====================

    // isset on the warped obj
    public function __isset($name) {
        return isset(self::$pdo_obj->$name);
    }

    // get on the warped obj
    public function __get($name) {
        return self::$pdo_obj->$name;
    }

    // isset on the warped obj
    public function __set($name, $value) {
        self::$pdo_obj->$name = $value;
    }

    // unset on the warped obj
    public function __unset($name) {
        unset(self::$pdo_obj->$name);
    }

    // call on the warped obj
    public function __call($name, $args) {
        return call_user_func_array(array(self::$pdo_obj, $name), $args);
    }

    // call on the warped obj
    public static function getVar($name) {
        return self::$pdo_obj->$name;
    }

    /**
     * Retourne l'objet DB
     *
     * @return \includes\Database
     */
    public function getPdoObj()
    {
        return self::$pdo_obj;
    }
}


class Database extends \mysqli
{
    private static $hist = [];

    public function __construct($host, $username, $passwd, $dbname)
    {
        /* activate reporting */
        $driver = new \mysqli_driver();
        // @TODO: mettre ALL quand on voudra travailler dessus;
        $driver->report_mode = MYSQLI_REPORT_ALL & ~MYSQLI_REPORT_INDEX;;
        parent::__construct ($host, $username, $passwd, $dbname);
        $this->query('SET NAMES \'utf8\';');
        $this->query("SET @@SESSION.sql_mode='';");
    }

    public function getQuerys() {
        return self::$hist;
    }

    /*public function isDbEmpty() : bool
    {
        return empty($this->query('SHOW TABLES')->fetch_all());
    }*/

    public function query($query, $resultmode = MYSQLI_STORE_RESULT) : Database_MySQLi_Result
    {
        $nb = count(self::$hist);
        $backtraces = debug_backtrace();
        $f = '';

        foreach ( $backtraces as $b ) {
            if (isset($b['file']) && basename($b['file']) != 'sql.class.php') {
                $f = $b;
            break;
            }
        }

        if ($f !='') {
            self::$hist[$nb]['back'] = $f;
        }

        self::$hist[$nb]['query'] = $query;
        self::$hist[$nb]['t1'] = microtime(true);

        $this->real_query($query);
        $result = new Database_MySQLi_Result($this);

        self::$hist[$nb]['t2'] = microtime(true);
        self::$hist[$nb]['results'] = $result;

        return $result;
    }

    public function quote($escapestr)
    {
        return $this->escape_string( $escapestr );
    }
}


class Database_MySQLi_Result extends \MySQLi_Result
{
    public function fetch_all($result_type = NULL)
    {
        $rows = array();
        while($row = $this->fetch_assoc())
        {
            $rows[] = $row;
        }

        return $rows;
    }
}
