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
            require CONFIG_PATH .'dbconnect.php';

            self::$instance = new $className( $mysql_serveur , $mysql_user, $mysql_pass, $mysql_database);
        }
        return self::$instance;
    }

    public function initialized() {
        return isset( self::$instance );
    }

    private function __construct() {
        $args = func_get_args();
        // this doesn't work ... need use ReflectionClass ... BEURK ! ReflectionClass is not documented ... unstable
        // self::$pdo_obj = call_user_func_array('Database::__construct', $args);
        $r = new \ReflectionClass('\includes\Database');
        self::$pdo_obj = $r->newInstanceArgs($args);
    }

    // singleton pattern, code from php.net
    public function __clone() { error_handler('Clone is not allowed.', E_USER_ERROR); }

    // singleton pattern, code from php.net
    public function __wakeup() { error_handler('Unserializing is not allowed.', E_USER_ERROR); }

    // for call staticly dynamic fx (doesn't use instance vars and doesn't use singleton ;-) )
    public static function __callStatic($name, $args) {
        self::singleton();
        if (method_exists(self::$instance, $name))
            return call_user_func_array(array(self::$instance, $name), $args);
        elseif (method_exists(self::$pdo_obj, $name))
            return call_user_func_array(array(self::$pdo_obj, $name), $args);
        else
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
    private static $hist = array();

    public function __construct ( $host='localhost', $username='root', $passwd ='',$dbname = 'db_conges')
    {
        parent::__construct (  $host , $username , $passwd , $dbname );
        $this->query('SET NAMES \'utf8\';');
        $this->query("SET @@SESSION.sql_mode='';");
    }

    public function getQuerys() {
        return self::$hist;
    }

    public function query( $query , $resultmode = MYSQLI_STORE_RESULT )
    {
        $nb = count(self::$hist);
        $backtraces = debug_backtrace();
        $f = '';

        foreach ( $backtraces as $k => $b ) {
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

        if ($this->errno != 0) {
            $dump_name = DUMP_PATH . 'sql_' . date('c') . '.dump';
            $fh = fopen( $dump_name , 'a+');
            if($fh ==! false) {
                fputs ($fh, "\n".'##################################################');
                fputs ($fh, "\n".'Date : '. date('Y-m-d H:i:s (T)') );
                fputs ($fh, "\n".'**************************************************');
                fputs ($fh, "\n".'--------------------------------------------------');
                fputs ($fh, "\n".'=> Last erreur log');
                fputs ($fh, "\n".var_export(error_get_last(), true));
                fputs ($fh, "\n".'**************************************************');
                fputs ($fh, "\n".'--------------------------------------------------');
                fputs ($fh, "\n".'=> Debug Backtrace');
                fputs ($fh, "\n".var_export($backtraces, true));
                fputs ($fh, "\n".'**************************************************');
                fputs ($fh, "\n".'--------------------------------------------------');
                fputs ($fh, "\n".'=> Var dump $_REQUEST');
                fputs ($fh, "\n".var_export($_REQUEST, true));
                fputs ($fh, "\n".'--------------------------------------------------');
                fputs ($fh, "\n".'=> Var dump $_SESSION');
                fputs ($fh, "\n".var_export((isset($_SESSION) ? $_SESSION : array() ), true));
                fputs ($fh, "\n".'--------------------------------------------------');
                fputs ($fh, "\n".'=> Var dump $_SERVER');
                fputs ($fh, "\n".var_export($_SERVER, true));
                fputs ($fh, "\n".'**************************************************');
                fclose ($fh);
            }

            @ob_clean();
            // DONT USE GETTEXT ... KEEP THIS CODE WITHOUT REF ... THIS NEED TO BE A SAFE CODE !!!!
            echo '<div style="margin: auto; width: 80%;">
                    <h1>Une erreur est survenue ...</h1>
                    <p>Pour aider la résolution de ce problème, veuillez fournir les informations suivantes :</p>
                    <div>
                        <div style="float:left;width: 230px;"><img src="'. IMG_PATH .'oops.png" style="width: 98%" /></div>
                        <textarea style="width: 70%" rows="14">'.
                        'login : ' .@$_SESSION['userlogin']."\n".
                        'uri   : ' .preg_replace('/session=phpconges[a-z0-9]{32}&?/','',@$_SERVER['REQUEST_URI'])."\n".
                        'dump  : ' .$dump_name. "\n"."\n".
                        'file  : ' .$f['file']. "\n".
                        'line  : ' .$f['line']. "\n".
                        'fx    : $SQL->query'. "\n".
                        'error : ' .$this->error. "\n".
                        'sql   : ' .$query. "\n".
                        '</textarea>
                    </div>
                </div>';
            exit();
        }
        return $result;
    }

    public function quote( $escapestr )
    {
        return $this->escape_string( $escapestr );
    }
}


class Database_MySQLi_Result extends \MySQLi_Result
{
    public function fetch_all()
    {
        $rows = array();
        while($row = $this->fetch_assoc())
        {
            $rows[] = $row;
        }

        return $rows;
    }
}
