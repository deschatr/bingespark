<?php

define("API_ROOT_PATH", __DIR__ );
require API_ROOT_PATH . "/include/bootstrap.php";
require API_ROOT_PATH . "/controllers/moviecontroller.php";

$controller = new MovieController;
$controller->processRequest();

?>