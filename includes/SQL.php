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

    public static function existsDatabase(string $name) : bool
    {
        $instance = self::singletonWithoutDb();

        $res = $instance->query('SHOW DATABASES');
        foreach ($res->fetch_all() as $database) {
            if ($name === $database['Database']) {
                $instance->select_db($name);
                return true;
            }
        }

        return in_array($name, $res->fetch_all(), true);
    }

    public static function singletonWithoutDb()
    {
        require CONFIG_PATH . 'dbconnect.php';
        return new self($mysql_serveur, $mysql_user, $mysql_pass, '');
    }

    public function initialized() {
        return isset( self::$instance );
    }

    private function __construct(string $server, string $user, string $host, string $database) {
        self::$pdo_obj = new Database($server, $user, $host, $database);
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
    public function __construct($host, $username, $passwd, $dbname)
    {
        /* activate reporting */
        $driver = new \mysqli_driver();
        // @TODO: mettre ALL quand on voudra travailler dessus;
        $driver->report_mode = MYSQLI_REPORT_ALL & ~MYSQLI_REPORT_INDEX;
        parent::__construct ($host, $username, $passwd, $dbname);
        $this->query('SET NAMES \'utf8\';');
        $this->query("SET @@SESSION.sql_mode='';");
    }

    public function query($query, $resultmode = MYSQLI_STORE_RESULT) : Database_MySQLi_Result
    {
        unset($resultmode);
        $this->real_query($query);

        return new Database_MySQLi_Result($this);
    }

    public function quote($escapestr)
    {
        return $this->escape_string( $escapestr );
    }
}


class Database_MySQLi_Result extends \mysqli_result
{
    public function fetch_all($result_type = NULL)
    {
        $rows = array();
        foreach ($this->fetch_assoc() as $k => $v) {
            $rows[] = [$k => $v];
        }

        return $rows;
    }
}
