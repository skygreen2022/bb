<?php

require_once("../../../init.php");

// Outputs all the result of shellcommand "ls", and returns
// the last output line into $last_line. Stores the return value
// of the shell command in $retval.
//$last_line = system('ls', $retval);

//exec('mysqldump -uroot -p betterlife>bb20161113.bak', $result);
//echo $result;

$dest_db_config = array
(
    "host"     => ConfigDb::$host,
    "port"     => ConfigDb::$port,
    "user"     => ConfigDb::$username,
    "password" => ConfigDb::$password,
    "dbname"   => "bb",
    "script_filename" => Gc::$nav_root_path . "install" . DS . "data" . DS . "mysql" . DS . "db_betterlife.sql",
);

DbInfoMysqli::run_script($dest_db_config);
