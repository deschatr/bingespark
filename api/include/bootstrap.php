<?php

// include 
require_once API_ROOT_PATH . "/include/utilities.php";

// include main configuration file
require_once API_ROOT_PATH . "/include/config.php";
 
//  Throw mysqli_sql_exception for errors instead of warnings 
mysqli_report(MYSQLI_REPORT_STRICT);

define('DEFAULT_PAGE_SIZE',20);

?>