<?php

require_once("../../../init.php");

$tableList = ManagerDb::newInstance()->dbinfo()->tableList();
foreach ($tableList as $tablename) {
    echo "DROP TABLE $tablename;<br/>";
}
